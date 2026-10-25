<?php

use App\Enum\EnrollmentStatus;
use App\Enum\UserRole;
use App\Filament\Resources\AcademicClasses\Pages\EditAcademicClass;
use App\Filament\Resources\AcademicClasses\Pages\ListAcademicClasses;
use App\Filament\Resources\AcademicClasses\RelationManagers\SubjectAssignmentsRelationManager;
use App\Filament\Resources\AcademicSessions\Pages\CreateAcademicSession;
use App\Filament\Resources\Students\Pages\CreateStudent;
use App\Filament\Resources\Students\Pages\ListStudents;
use App\Filament\Resources\Students\Pages\ViewStudent;
use App\Models\AcademicClass;
use App\Models\AcademicSession;
use App\Models\Enrollment;
use App\Models\ExaminationResult;
use App\Models\Section;
use App\Models\Student;
use App\Models\Subject;
use App\Models\SubjectAssignment;
use App\Support\Academics\EnrollmentException;
use App\Support\Academics\Enrollments;
use Filament\Actions\Testing\TestAction;
use Illuminate\Support\Carbon;
use Livewire\Livewire;

beforeEach(function () {
    $this->session = AcademicSession::factory()->startingIn(2026)->current()->create();
    $this->nextSession = AcademicSession::factory()->startingIn(2027)->create();
    $this->class3 = AcademicClass::factory()->create(['name' => 'Class 3', 'sort_order' => 3]);
    $this->class4 = AcademicClass::factory()->create(['name' => 'Class 4', 'sort_order' => 4]);
    $this->section3A = Section::factory()->for($this->class3)->create(['name' => 'A']);
    $this->section3B = Section::factory()->for($this->class3)->create(['name' => 'B']);
    $this->section4A = Section::factory()->for($this->class4)->create(['name' => 'A']);
    $this->enrollments = app(Enrollments::class);
});

function enrolledStudent(array $attributes = []): Student
{
    $student = Student::factory()->create(['is_active' => true] + $attributes);
    app(Enrollments::class)->enroll($student, test()->session, test()->class3, test()->section3A, '7');

    return $student->refresh();
}

describe('enrollment rules', function () {
    it('enrolls a student and keeps the current class in step', function () {
        $student = enrolledStudent();

        expect($student->currentEnrollment->placement)->toBe('Class 3 - A')
            ->and($student->currentEnrollment->roll_number)->toBe('7')
            ->and($student->academic_class_id)->toBe($this->class3->id)
            ->and($student->is_active)->toBeTrue();
    });

    it('never gives a student two active placements', function () {
        $student = enrolledStudent();

        expect(fn () => $this->enrollments->enroll($student, $this->session, $this->class4))->toThrow(EnrollmentException::class);
        expect($student->enrollments()->active()->count())->toBe(1);
    });

    it('rejects a section from a different class', function () {
        $student = Student::factory()->create();

        expect(fn () => $this->enrollments->enroll($student, $this->session, $this->class4, $this->section3A))->toThrow(EnrollmentException::class);
        expect($student->enrollments()->count())->toBe(0);
    });

    it('transfers within a session and keeps the old placement as history', function () {
        $student = enrolledStudent();

        $this->enrollments->transfer($student, $this->class3, $this->section3B, on: Carbon::parse('2026-09-01'), notes: 'Parent request');

        $history = $student->enrollments()->orderBy('id')->get();
        expect($history)->toHaveCount(2)
            ->and($history[0]->status)->toBe(EnrollmentStatus::Transferred)
            ->and($history[0]->ended_on->toDateString())->toBe('2026-09-01')
            ->and($history[0]->notes)->toContain('Parent request')
            ->and($history[1]->status)->toBe(EnrollmentStatus::Active)
            ->and($history[1]->placement)->toBe('Class 3 - B')
            ->and($history[1]->academic_session_id)->toBe($this->session->id)
            ->and($history[1]->roll_number)->toBe('7');
    });

    it('refuses a transfer to the same placement', function () {
        $student = enrolledStudent();

        expect(fn () => $this->enrollments->transfer($student, $this->class3, $this->section3A))->toThrow(EnrollmentException::class);
    });

    it('promotes into a later session without touching examination results', function () {
        $student = enrolledStudent();
        $result = ExaminationResult::create(['student_id' => $student->id, 'academic_class_id' => $this->class3->id, 'year' => '2026', 'total_marks' => 100, 'obtained_marks' => 81]);

        $this->enrollments->promote($student, $this->nextSession, $this->class4, $this->section4A);

        $student->refresh();
        expect($student->currentEnrollment->placement)->toBe('Class 4 - A')
            ->and($student->currentEnrollment->academic_session_id)->toBe($this->nextSession->id)
            ->and($student->enrollments()->where('status', EnrollmentStatus::Promoted)->value('academic_class_id'))->toBe($this->class3->id)
            ->and($student->academic_class_id)->toBe($this->class4->id)
            ->and($result->fresh()->only(['student_id', 'academic_class_id', 'obtained_marks']))->toBe(['student_id' => $student->id, 'academic_class_id' => $this->class3->id, 'obtained_marks' => 81]);
    });

    it('records a repeated year as retained', function () {
        $student = enrolledStudent();

        $this->enrollments->promote($student, $this->nextSession, $this->class3, $this->section3A);

        expect($student->enrollments()->orderBy('id')->first()->status)->toBe(EnrollmentStatus::Retained);
    });

    it('refuses to promote into the same or an earlier session', function () {
        $student = enrolledStudent();

        expect(fn () => $this->enrollments->promote($student, $this->session, $this->class4))->toThrow(EnrollmentException::class);
    });

    it('marks graduated and withdrawn students inactive and keeps their history', function (string $method, EnrollmentStatus $status) {
        $student = enrolledStudent();

        $this->enrollments->{$method}($student, Carbon::parse('2027-03-31'));

        $student->refresh();
        expect($student->is_active)->toBeFalse()
            ->and($student->currentEnrollment)->toBeNull()
            ->and($student->enrollments()->sole()->status)->toBe($status)
            ->and($student->academic_class_id)->toBe($this->class3->id);
    })->with([['graduate', EnrollmentStatus::Graduated], ['withdraw', EnrollmentStatus::Left]]);

    it('re-enrolls a student who left', function () {
        $student = enrolledStudent();
        $this->enrollments->withdraw($student);

        $this->enrollments->enroll($student, $this->nextSession, $this->class4);

        expect($student->refresh()->is_active)->toBeTrue()
            ->and($student->enrollments()->count())->toBe(2);
    });

    it('promotes a whole class to the next class, matching sections, and reports who was skipped', function () {
        $students = collect([enrolledStudent(), enrolledStudent()]);
        $top = Student::factory()->create();
        $this->enrollments->enroll($top, $this->session, $this->class4);
        $notEnrolled = Student::factory()->create();

        $result = $this->enrollments->promoteMany($students->push($top, $notEnrolled), $this->nextSession, 'next');

        expect($result['moved'])->toBe(2)
            ->and($result['skipped'])->toHaveCount(2)
            ->and(implode(' ', $result['skipped']))->toContain('final class')->toContain('not currently enrolled')
            ->and($students[0]->refresh()->currentEnrollment->placement)->toBe('Class 4 - A')
            ->and($top->refresh()->currentEnrollment->academic_session_id)->toBe($this->session->id);
    });

    it('finds the next class by position', function () {
        expect($this->class3->nextClass()->is($this->class4))->toBeTrue()
            ->and($this->class4->nextClass())->toBeNull();
    });

    it('lists the subjects for a placement: whole-class plus own section', function () {
        $english = Subject::factory()->create(['name' => 'English']);
        $computer = Subject::factory()->create(['name' => 'Computer']);
        $art = Subject::factory()->create(['name' => 'Art']);
        SubjectAssignment::create(['academic_class_id' => $this->class3->id, 'subject_id' => $english->id]);
        SubjectAssignment::create(['academic_class_id' => $this->class3->id, 'section_id' => $this->section3A->id, 'subject_id' => $computer->id]);
        SubjectAssignment::create(['academic_class_id' => $this->class3->id, 'section_id' => $this->section3B->id, 'subject_id' => $art->id]);

        $names = fn (?int $section) => SubjectAssignment::forPlacement($this->class3->id, $section)->with('subject')->get()->pluck('subject.name')->sort()->values()->all();

        expect($names($this->section3A->id))->toBe(['Computer', 'English'])
            ->and($names(null))->toBe(['English']);
    });

    it('keeps exactly one current session', function () {
        $this->nextSession->makeCurrent();

        expect(AcademicSession::where('is_current', true)->pluck('id')->all())->toBe([$this->nextSession->id]);
    });
});

describe('protecting history', function () {
    it('will not delete classes, sections or sessions that hold history', function () {
        enrolledStudent();
        $admin = userWithRole(UserRole::SuperAdmin);
        $emptyClass = AcademicClass::factory()->create();

        expect($admin->can('delete', $this->class3))->toBeFalse()
            ->and($admin->can('delete', $this->section3A))->toBeFalse()
            ->and($admin->can('delete', $this->session))->toBeFalse()
            ->and($admin->can('delete', $emptyClass))->toBeTrue()
            ->and($admin->can('delete', $this->section3B))->toBeTrue()
            ->and($admin->can('delete', $this->nextSession))->toBeTrue();
    });

    it('will not delete a class that only has examination results', function () {
        $class = AcademicClass::factory()->create();
        ExaminationResult::create(['student_id' => Student::factory()->create()->id, 'academic_class_id' => $class->id, 'total_marks' => 100, 'obtained_marks' => 50]);

        expect(userWithRole(UserRole::SuperAdmin)->can('delete', $class))->toBeFalse();
    });

    it('skips classes with history in a bulk delete', function () {
        enrolledStudent();
        $emptyClass = AcademicClass::factory()->create();

        Livewire::actingAs(userWithRole(UserRole::SuperAdmin))
            ->test(ListAcademicClasses::class)
            ->selectTableRecords([$this->class3->id, $emptyClass->id])
            ->callAction(TestAction::make('delete')->table()->bulk());

        expect(AcademicClass::find($this->class3->id))->not->toBeNull()
            ->and(AcademicClass::find($emptyClass->id))->toBeNull();
    });

    it('never lets anyone delete or hand-create enrollment history', function () {
        $student = enrolledStudent();
        $admin = userWithRole(UserRole::SuperAdmin);

        expect($admin->can('delete', $student->currentEnrollment))->toBeFalse()
            ->and($admin->can('create', Enrollment::class))->toBeFalse()
            ->and($admin->can('update', $student->currentEnrollment))->toBeTrue();
    });
});

describe('permissions', function () {
    it('lets staff manage placements and structure but not sessions or imports', function () {
        $staff = userWithRole(UserRole::Staff);

        expect($staff->can('changePlacement', Enrollment::class))->toBeTrue()
            ->and($staff->can('create', Section::class))->toBeTrue()
            ->and($staff->can('create', Subject::class))->toBeTrue()
            ->and($staff->can('viewAny', AcademicSession::class))->toBeTrue()
            ->and($staff->can('create', AcademicSession::class))->toBeFalse()
            ->and($staff->can('import-students'))->toBeFalse();

        $this->actingAs($staff)->get('/admin/academic-sessions')->assertOk();
        $this->actingAs($staff)->get('/admin/academic-sessions/create')->assertForbidden();
        $this->actingAs($staff)->get('/admin/import-students')->assertForbidden();
    });

    it('lets principals manage sessions and import students', function () {
        $principal = userWithRole(UserRole::Principal);

        expect($principal->can('create', AcademicSession::class))->toBeTrue()
            ->and($principal->can('import-students'))->toBeTrue();

        $this->actingAs($principal)->get('/admin/import-students')->assertOk();
    });

    it('gives teachers, students, parents and inactive staff no academic management', function (?UserRole $role, bool $active) {
        $user = userWithRole($role, active: $active);

        expect($user->can('changePlacement', Enrollment::class))->toBeFalse()
            ->and($user->can('create', AcademicClass::class))->toBeFalse()
            ->and($user->can('create', Subject::class))->toBeFalse()
            ->and($user->can('import-students'))->toBeFalse();
    })->with([
        [UserRole::Teacher, true], [UserRole::Student, true], [UserRole::Parent, true], [UserRole::Staff, false], [UserRole::Principal, false],
    ]);

    it('hides placement actions from users who may only view students', function () {
        // Teachers can view students but have no panel yet; check the action authorization directly
        $this->actingAs(userWithRole(UserRole::Teacher));

        expect(App\Filament\Resources\Students\Actions\PlacementActions::allowed())->toBeFalse();
    });
});

describe('admin screens', function () {
    beforeEach(function () {
        $this->actingAs(userWithRole(UserRole::Staff));
    });

    it('creates a student together with their first placement', function () {
        Livewire::test(CreateStudent::class)
            ->fillForm([
                'admission_number' => 'SR-2001',
                'first_name' => 'Sana',
                'father_name' => 'Ali',
                'academic_class_id' => $this->class3->id,
                'section_id' => $this->section3B->id,
                'roll_number' => '4',
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $student = Student::where('admission_number', 'SR-2001')->sole();
        expect($student->currentEnrollment->placement)->toBe('Class 3 - B')
            ->and($student->currentEnrollment->academic_session_id)->toBe($this->session->id)
            ->and($student->is_active)->toBeTrue();
    });

    it('rejects a duplicate admission number in the form', function () {
        enrolledStudent(['admission_number' => 'SR-1']);

        Livewire::test(CreateStudent::class)
            ->fillForm(['admission_number' => 'SR-1', 'first_name' => 'Copy'])
            ->call('create')
            ->assertHasFormErrors(['admission_number' => 'unique']);

        expect(Student::where('admission_number', 'SR-1')->count())->toBe(1);
    });

    it('searches, filters and paginates the student list', function () {
        $ali = enrolledStudent(['first_name' => 'Ali', 'father_name' => 'Karim', 'admission_number' => 'SR-77']);
        $other = Student::factory()->create(['first_name' => 'Bilal', 'is_active' => true]);
        $this->enrollments->enroll($other, $this->session, $this->class4);
        $inactive = Student::factory()->create(['first_name' => 'Gone', 'is_active' => false]);

        Livewire::test(ListStudents::class)
            ->assertCanSeeTableRecords([$ali, $other])
            ->assertCanNotSeeTableRecords([$inactive])
            ->searchTable('Karim')
            ->assertCanSeeTableRecords([$ali])
            ->assertCanNotSeeTableRecords([$other])
            ->searchTable('SR-77')
            ->assertCanSeeTableRecords([$ali])
            ->searchTable(null)
            ->filterTable('class', $this->class4->id)
            ->assertCanSeeTableRecords([$other])
            ->assertCanNotSeeTableRecords([$ali])
            ->resetTableFilters()
            ->filterTable('section', $this->section3A->id)
            ->assertCanSeeTableRecords([$ali])
            ->assertCanNotSeeTableRecords([$other])
            ->resetTableFilters()
            ->filterTable('is_active', false)
            ->assertCanSeeTableRecords([$inactive])
            ->assertCanNotSeeTableRecords([$ali]);

        Student::factory()->count(30)->create(['is_active' => true]);
        Livewire::test(ListStudents::class)->assertCountTableRecords(32)->assertSee('Next');
    });

    it('shows a student with placement history and results', function () {
        $student = enrolledStudent(['first_name' => 'Hina']);
        $this->enrollments->promote($student, $this->nextSession, $this->class4, $this->section4A);
        $result = ExaminationResult::create(['student_id' => $student->id, 'year' => '2026', 'detail' => 'Annual Examination', 'total_marks' => 100, 'obtained_marks' => 90]);

        $this->get("/admin/students/{$student->id}")
            ->assertOk()
            ->assertSee('Hina')
            ->assertSee('Class 4 - A')
            ->assertSee('Enrollment history');

        $manager = fn (string $class) => Livewire::test($class, ['ownerRecord' => $student, 'pageClass' => ViewStudent::class]);

        $manager(App\Filament\Resources\Students\RelationManagers\EnrollmentsRelationManager::class)
            ->assertCanSeeTableRecords($student->enrollments)
            ->assertSee('Promoted')
            ->assertSee('Class 3 - A');
        $manager(App\Filament\Resources\Students\RelationManagers\ExaminationResultsRelationManager::class)
            ->assertCanSeeTableRecords([$result])
            ->assertSee('90 / 100');
    });

    it('transfers a student from the student page', function () {
        $student = enrolledStudent();

        Livewire::test(ViewStudent::class, ['record' => $student->id])
            ->callAction(TestAction::make('transfer')->arguments([]), [
                'academic_class_id' => $this->class3->id,
                'section_id' => $this->section3B->id,
                'transferred_on' => '2026-10-01',
            ])
            ->assertHasNoActionErrors();

        expect($student->refresh()->currentEnrollment->placement)->toBe('Class 3 - B');
    });

    it('bulk-promotes selected students', function () {
        $students = collect([enrolledStudent(), enrolledStudent()]);

        Livewire::test(ListStudents::class)
            ->selectTableRecords($students->pluck('id')->all())
            ->callAction(TestAction::make('promote')->table()->bulk(), ['academic_session_id' => $this->nextSession->id, 'mode' => 'next'])
            ->assertHasNoActionErrors();

        expect($students->map(fn (Student $student) => $student->refresh()->currentEnrollment->placement)->all())->toBe(['Class 4 - A', 'Class 4 - A']);
    });

    it('refuses a duplicate whole-class subject', function () {
        $subject = Subject::factory()->create();
        SubjectAssignment::create(['academic_class_id' => $this->class3->id, 'subject_id' => $subject->id]);

        Livewire::test(SubjectAssignmentsRelationManager::class, ['ownerRecord' => $this->class3, 'pageClass' => EditAcademicClass::class])
            ->callAction(TestAction::make('create')->table(), ['subject_id' => $subject->id])
            ->assertHasActionErrors(['subject_id']);

        expect(SubjectAssignment::count())->toBe(1);
    });
});

it('suggests the next session and refuses overlapping dates', function () {
    $this->actingAs(userWithRole(UserRole::Principal));

    Livewire::test(CreateAcademicSession::class)
        ->assertSchemaStateSet(['name' => '2028-2029', 'starts_on' => '2028-04-01', 'ends_on' => '2029-03-31'])
        ->fillForm(['name' => 'Overlap', 'starts_on' => '2027-06-01', 'ends_on' => '2028-05-31'])
        ->call('create')
        ->assertHasFormErrors(['ends_on']);
});
