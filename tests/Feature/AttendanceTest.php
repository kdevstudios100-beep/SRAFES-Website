<?php

use App\Enum\AttendanceStatus;
use App\Enum\UserRole;
use App\Filament\Pages\AttendanceOverview;
use App\Filament\Pages\AttendanceReport;
use App\Filament\Pages\TakeAttendance;
use App\Filament\Resources\Students\Pages\ViewStudent;
use App\Filament\Resources\Students\RelationManagers\AttendanceRecordsRelationManager;
use App\Filament\Resources\TeacherAssignments\Pages\CreateTeacherAssignment;
use App\Models\AcademicClass;
use App\Models\AcademicSession;
use App\Models\AttendanceRecord;
use App\Models\Enrollment;
use App\Models\Section;
use App\Models\Student;
use App\Models\Subject;
use App\Models\TeacherAssignment;
use App\Support\Academics\Enrollments;
use App\Support\Attendance\AttendanceException;
use App\Support\Attendance\AttendanceGroup;
use App\Support\Attendance\AttendanceRegister;
use App\Support\Attendance\AttendanceScope;
use App\Support\Attendance\AttendanceSummary;
use Filament\Facades\Filament;
use Illuminate\Support\Carbon;
use Livewire\Livewire;

beforeEach(function () {
    Carbon::setTestNow('2026-10-26 09:00');

    $this->session = AcademicSession::factory()->startingIn(2026)->current()->create();
    $this->class5 = AcademicClass::factory()->create(['name' => 'Class 5', 'sort_order' => 5]);
    $this->class6 = AcademicClass::factory()->create(['name' => 'Class 6', 'sort_order' => 6]);
    $this->sectionA = Section::factory()->for($this->class5)->create(['name' => 'A']);
    $this->sectionB = Section::factory()->for($this->class5)->create(['name' => 'B']);

    $this->groupA = new AttendanceGroup($this->session, $this->class5, $this->sectionA);
    $this->groupB = new AttendanceGroup($this->session, $this->class5, $this->sectionB);
    $this->group6 = new AttendanceGroup($this->session, $this->class6);

    $place = function (AcademicClass $class, ?Section $section, string $roll, string $name) {
        $student = Student::factory()->create(['first_name' => $name, 'last_name' => '', 'is_active' => true]);

        return app(Enrollments::class)->enroll($student, $this->session, $class, $section, $roll, Carbon::parse('2026-04-01'));
    };

    $this->a1 = $place($this->class5, $this->sectionA, '2', 'Amna');
    $this->a2 = $place($this->class5, $this->sectionA, '1', 'Bilal');
    $this->b1 = $place($this->class5, $this->sectionB, '1', 'Chand');
    $this->c1 = $place($this->class6, null, '1', 'Daud');

    $this->teacher = userWithRole(UserRole::Teacher);
    TeacherAssignment::create(['user_id' => $this->teacher->id, 'academic_session_id' => $this->session->id, 'academic_class_id' => $this->class5->id, 'section_id' => $this->sectionA->id]);

    $this->scope = app(AttendanceScope::class);
    $this->register = app(AttendanceRegister::class);
});

afterEach(fn () => Carbon::setTestNow());

function marks(array $enrollments, string $status = 'present'): array
{
    return collect($enrollments)->mapWithKeys(fn (Enrollment $enrollment) => [$enrollment->id => ['status' => $status, 'remarks' => null]])->all();
}

describe('who can mark which register', function () {
    it('gives a class teacher only their own section', function () {
        $keys = $this->scope->groupsFor($this->teacher, $this->session)->map->key()->all();

        expect($keys)->toBe([$this->groupA->key()]);
    });

    it('gives no register to a subject teacher or an inactive teacher', function () {
        $subjectTeacher = userWithRole(UserRole::Teacher);
        TeacherAssignment::create(['user_id' => $subjectTeacher->id, 'academic_session_id' => $this->session->id, 'academic_class_id' => $this->class5->id, 'section_id' => $this->sectionB->id, 'subject_id' => Subject::factory()->create()->id]);
        $inactive = userWithRole(UserRole::Teacher, active: false);
        TeacherAssignment::create(['user_id' => $inactive->id, 'academic_session_id' => $this->session->id, 'academic_class_id' => $this->class6->id]);

        expect($this->scope->groupsFor($subjectTeacher, $this->session))->toBeEmpty()
            ->and($this->scope->groupsFor($inactive, $this->session))->toBeEmpty();
    });

    it('gives staff every register with students, in class order', function () {
        $labels = $this->scope->groupsFor(userWithRole(UserRole::Staff), $this->session)->map->label()->all();

        expect($labels)->toBe(['Class 5 - A', 'Class 5 - B', 'Class 6']);
    });

    it('refuses future dates, dates outside the session and old dates for teachers', function () {
        expect($this->scope->whyCannotMark($this->teacher, $this->groupA, today()))->toBeNull()
            ->and($this->scope->whyCannotMark($this->teacher, $this->groupA, today()->subDays(7)))->toBeNull()
            ->and($this->scope->whyCannotMark($this->teacher, $this->groupA, today()->addDay()))->toContain('future')
            ->and($this->scope->whyCannotMark($this->teacher, $this->groupA, today()->subDays(8)))->toContain('7 days')
            ->and($this->scope->whyCannotMark($this->teacher, $this->groupB, today()))->toContain('not the class teacher')
            ->and($this->scope->whyCannotMark(userWithRole(UserRole::Staff), $this->groupA, Carbon::parse('2026-04-02')))->toBeNull()
            ->and($this->scope->whyCannotMark(userWithRole(UserRole::Staff), $this->groupA, Carbon::parse('2026-03-31')))->toContain('outside');
    });
});

describe('the register', function () {
    it('lists students in roll order and follows transfers and leaving', function () {
        expect($this->scope->roster($this->groupA, today())->map->student->map->first_name->all())->toBe(['Bilal', 'Amna']);

        app(Enrollments::class)->transfer($this->a1->student, $this->class5, $this->sectionB, on: Carbon::parse('2026-10-20'));
        app(Enrollments::class)->withdraw($this->a2->student, Carbon::parse('2026-10-22'));

        $names = fn (AttendanceGroup $group, string $date) => $this->scope->roster($group, Carbon::parse($date))->map->student->map->first_name->all();

        expect($names($this->groupA, '2026-10-19'))->toBe(['Bilal', 'Amna'])
            // Transfer day belongs to the new section only
            ->and($names($this->groupA, '2026-10-20'))->toBe(['Bilal'])
            ->and($names($this->groupB, '2026-10-20'))->toContain('Amna')
            ->and($names($this->groupB, '2026-10-19'))->not->toContain('Amna')
            ->and($names($this->groupA, '2026-10-22'))->toBe([]);
    });

    it('saves a complete day and updates it instead of duplicating', function () {
        $this->register->save($this->teacher, $this->groupA, today(), marks([$this->a1, $this->a2]));
        $this->register->save($this->teacher, $this->groupA, today(), [
            $this->a1->id => ['status' => 'absent', 'remarks' => '  Fever  '],
            $this->a2->id => ['status' => 'present'],
        ]);

        expect(AttendanceRecord::count())->toBe(2)
            ->and(AttendanceRecord::where('enrollment_id', $this->a1->id)->sole()->only(['status', 'remarks', 'recorded_by']))
            ->toBe(['status' => AttendanceStatus::Absent, 'remarks' => 'Fever', 'recorded_by' => $this->teacher->id]);
    });

    it('refuses an incomplete day', function () {
        expect(fn () => $this->register->save($this->teacher, $this->groupA, today(), marks([$this->a1])))
            ->toThrow(AttendanceException::class, 'Bilal');
        expect(AttendanceRecord::count())->toBe(0);
    });

    it('refuses students from another register and invalid statuses', function () {
        expect(fn () => $this->register->save($this->teacher, $this->groupA, today(), marks([$this->a1, $this->a2, $this->b1])))
            ->toThrow(AttendanceException::class, 'not on this register');
        expect(fn () => $this->register->save($this->teacher, $this->groupA, today(), marks([$this->a1, $this->a2], 'holiday')))
            ->toThrow(AttendanceException::class);
        expect(AttendanceRecord::count())->toBe(0);
    });

    it('refuses a teacher marking another section', function () {
        expect(fn () => $this->register->save($this->teacher, $this->groupB, today(), marks([$this->b1])))
            ->toThrow(AttendanceException::class, 'not the class teacher');
    });

    it('keeps attendance when a student is promoted or the student is deleted with history', function () {
        $this->register->save($this->teacher, $this->groupA, today(), marks([$this->a1, $this->a2]));
        $next = AcademicSession::factory()->startingIn(2027)->create();

        app(Enrollments::class)->promote($this->a1->student, $next, $this->class6);

        expect($this->a1->student->attendanceRecords()->count())->toBe(1);

        $this->a2->student->delete();
        expect(AttendanceRecord::count())->toBe(1);
    });
});

describe('summaries', function () {
    it('counts leave as neither present nor absent and flags low attendance', function () {
        $staff = userWithRole(UserRole::Staff);
        $days = ['2026-10-19' => ['present', 'absent'], '2026-10-20' => ['late', 'absent'], '2026-10-21' => ['leave', 'absent'], '2026-10-22' => ['absent', 'present']];

        foreach ($days as $date => [$amna, $bilal]) {
            $this->register->save($staff, $this->groupA, Carbon::parse($date), [$this->a1->id => ['status' => $amna], $this->a2->id => ['status' => $bilal]]);
        }

        $rows = app(AttendanceSummary::class)->forGroup($this->groupA, Carbon::parse('2026-10-01'), today())->keyBy(fn ($row) => $row['enrollment']->student->first_name);

        // Amna: present + late = 2 of (4 - 1 leave) = 66.7%
        expect($rows['Amna']['percent'])->toBe(66.7)
            ->and($rows['Amna']['low'])->toBeTrue()
            ->and($rows['Amna']['counts'])->toBe(['present' => 1, 'absent' => 1, 'late' => 1, 'leave' => 1])
            ->and($rows['Bilal']['percent'])->toBe(25.0);

        $narrow = app(AttendanceSummary::class)->forGroup($this->groupA, Carbon::parse('2026-10-22'), Carbon::parse('2026-10-22'));
        expect($narrow->firstWhere(fn ($row) => $row['enrollment']->is($this->a2))['percent'])->toEqual(100);
    });

    it('reports which registers were taken on a day', function () {
        $staff = userWithRole(UserRole::Staff);
        $this->register->save($staff, $this->groupA, today(), [$this->a1->id => ['status' => 'present'], $this->a2->id => ['status' => 'absent']]);

        $day = app(AttendanceSummary::class)->day($staff, $this->session, today())->keyBy(fn ($row) => $row['group']->label());

        expect($day['Class 5 - A']['marked'])->toBe(2)
            ->and($day['Class 5 - A']['counts']['absent'])->toBe(1)
            ->and($day['Class 5 - B']['marked'])->toBe(0)
            ->and($day['Class 6']['on_roll'])->toBe(1);
    });
});

describe('panels and pages', function () {
    it('lets teachers into the teacher panel only', function () {
        $this->actingAs($this->teacher);

        $this->get('/teacher')->assertRedirect();
        $this->get('/teacher/attendance-overview')->assertOk()->assertSee('Class 5 - A')->assertDontSee('Class 5 - B');
        $this->get('/teacher/take-attendance')->assertOk();
        $this->get('/teacher/attendance-report')->assertOk();
        $this->get('/admin')->assertForbidden();
        $this->get('/admin/students')->assertForbidden();
    });

    it('keeps students, parents and inactive teachers out of the teacher panel', function (?UserRole $role, bool $active) {
        $this->actingAs(userWithRole($role, active: $active))->get('/teacher/take-attendance')->assertForbidden();
    })->with([[UserRole::Student, true], [UserRole::Parent, true], [UserRole::Teacher, false], [null, true]]);

    it('gives staff the attendance pages in the admin panel', function () {
        $this->actingAs(userWithRole(UserRole::Staff));

        $this->get('/admin/attendance-overview')->assertOk()->assertSee('Class 6');
        $this->get('/admin/take-attendance')->assertOk();
        $this->get('/admin/attendance-report')->assertOk();
        $this->get('/admin/teacher-assignments')->assertOk();
        $this->get('/admin/teacher-assignments/create')->assertForbidden();
    });

    it('takes attendance through the page', function () {
        Filament::setCurrentPanel(Filament::getPanel('teacher'));
        $this->actingAs($this->teacher);

        Livewire::test(TakeAttendance::class)
            ->assertSet('group', $this->groupA->key())
            ->assertSee('Bilal')
            ->assertDontSee('Chand')
            ->call('markAll', 'present')
            ->set("marks.{$this->a1->id}.status", 'absent')
            ->set("marks.{$this->a1->id}.remarks", 'Sick')
            ->call('save')
            ->assertNotified('Attendance saved for 2 students');

        expect(AttendanceRecord::where('enrollment_id', $this->a1->id)->sole()->only(['status', 'remarks']))
            ->toBe(['status' => AttendanceStatus::Absent, 'remarks' => 'Sick']);

        // Reopening the day shows the saved marks
        Livewire::test(TakeAttendance::class)->assertSet("marks.{$this->a1->id}.status", 'absent');
    });

    it('ignores a register key for a section the teacher does not teach', function () {
        Filament::setCurrentPanel(Filament::getPanel('teacher'));
        $this->actingAs($this->teacher);

        Livewire::withQueryParams(['group' => $this->groupB->key()])
            ->test(TakeAttendance::class)
            ->assertSet('group', $this->groupA->key())
            ->assertDontSee('Chand')
            ->set('group', $this->groupB->key())
            ->set('marks', [$this->b1->id => ['status' => 'absent']])
            ->call('save');

        expect(AttendanceRecord::where('enrollment_id', $this->b1->id)->exists())->toBeFalse();
    });

    it('locks dates outside the teacher edit window', function () {
        Filament::setCurrentPanel(Filament::getPanel('teacher'));
        $this->actingAs($this->teacher);

        Livewire::withQueryParams(['date' => today()->subDays(10)->toDateString()])
            ->test(TakeAttendance::class)
            ->assertSee('up to 7 days back')
            ->assertDontSee('Mark all present')
            ->set('marks', marks([$this->a1, $this->a2]))
            ->call('save')
            ->assertNotified('Attendance not saved');

        expect(AttendanceRecord::count())->toBe(0);
    });

    it('shows the report and downloads it as CSV', function () {
        $staff = userWithRole(UserRole::Staff);
        $this->register->save($staff, $this->groupA, today(), [$this->a1->id => ['status' => 'present'], $this->a2->id => ['status' => 'absent']]);
        $this->actingAs($staff);

        Livewire::withQueryParams(['group' => $this->groupA->key()])
            ->test(AttendanceReport::class)
            ->assertSee('Amna')
            ->assertSee('100%')
            ->assertSee('0%')
            ->callAction('download')
            ->assertFileDownloaded();
    });

    it('shows a student\'s attendance on their admin page', function () {
        $this->register->save(userWithRole(UserRole::Staff), $this->groupA, today(), marks([$this->a1, $this->a2]));
        $this->actingAs(userWithRole(UserRole::Staff));

        Livewire::test(AttendanceRecordsRelationManager::class, ['ownerRecord' => $this->a1->student, 'pageClass' => ViewStudent::class])
            ->assertCanSeeTableRecords(AttendanceRecord::where('enrollment_id', $this->a1->id)->get())
            ->assertSee('100%');
    });

    it('shows the daily overview to a teacher for their own class only', function () {
        Filament::setCurrentPanel(Filament::getPanel('teacher'));
        $this->actingAs($this->teacher);

        Livewire::test(AttendanceOverview::class)->assertSee('Class 5 - A')->assertSee('Not taken')->assertDontSee('Class 6');
    });
});

describe('teacher assignments', function () {
    it('allows one class teacher per section', function () {
        $this->actingAs(userWithRole(UserRole::Principal));

        Livewire::test(CreateTeacherAssignment::class)
            ->fillForm([
                'academic_session_id' => $this->session->id,
                'user_id' => userWithRole(UserRole::Teacher)->id,
                'academic_class_id' => $this->class5->id,
                'section_id' => $this->sectionA->id,
            ])
            ->call('create')
            ->assertHasFormErrors(['user_id']);

        expect(TeacherAssignment::count())->toBe(1);
    });

    it('creates a class teacher assignment that opens the register', function () {
        $this->actingAs(userWithRole(UserRole::Principal));
        $newTeacher = userWithRole(UserRole::Teacher);

        Livewire::test(CreateTeacherAssignment::class)
            ->fillForm([
                'academic_session_id' => $this->session->id,
                'user_id' => $newTeacher->id,
                'academic_class_id' => $this->class6->id,
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        expect($this->scope->groupsFor($newTeacher, $this->session)->map->label()->all())->toBe(['Class 6']);
    });

    it('offers only active users who can take attendance', function () {
        $this->actingAs(userWithRole(UserRole::Principal));
        $student = userWithRole(UserRole::Student);
        $student->update(['name' => 'Student Account']);

        Livewire::test(CreateTeacherAssignment::class)
            ->fillForm(['academic_session_id' => $this->session->id, 'user_id' => $student->id, 'academic_class_id' => $this->class6->id])
            ->call('create')
            ->assertHasFormErrors(['user_id']);
    });
});
