<?php

use App\Enum\EnrollmentStatus;
use App\Enum\ExaminationResultStatus;
use App\Enum\ExamStatus;
use App\Enum\UserRole;
use App\Filament\Pages\EnterMarks;
use App\Filament\Pages\ExamResults;
use App\Filament\Resources\Exams\Pages\EditExam;
use App\Filament\Resources\Exams\RelationManagers\PapersRelationManager;
use App\Models\AcademicClass;
use App\Models\AcademicSession;
use App\Models\Enrollment;
use App\Models\Exam;
use App\Models\ExaminationResult;
use App\Models\ExamMark;
use App\Models\ExamPaper;
use App\Models\Section;
use App\Models\Student;
use App\Models\Subject;
use App\Models\SubjectAssignment;
use App\Models\TeacherAssignment;
use App\Support\Academics\Enrollments;
use App\Support\Attendance\AttendanceGroup;
use App\Support\Exams\ExamException;
use App\Support\Exams\ExamScope;
use App\Support\Exams\GradingScale;
use App\Support\Exams\MarkSheet;
use App\Support\Exams\ResultSheet;
use Filament\Facades\Filament;
use Illuminate\Support\Carbon;
use Livewire\Livewire;

beforeEach(function () {
    Carbon::setTestNow('2026-10-26 09:00');

    $this->session = AcademicSession::factory()->startingIn(2026)->current()->create();
    $this->class5 = AcademicClass::factory()->create(['name' => 'Class 5', 'sort_order' => 5]);
    $this->sectionA = Section::factory()->for($this->class5)->create(['name' => 'A']);
    $this->sectionB = Section::factory()->for($this->class5)->create(['name' => 'B']);

    $this->english = Subject::factory()->create(['name' => 'English']);
    $this->maths = Subject::factory()->create(['name' => 'Maths']);
    $this->urdu = Subject::factory()->create(['name' => 'Urdu']);
    SubjectAssignment::create(['academic_class_id' => $this->class5->id, 'subject_id' => $this->english->id]);
    SubjectAssignment::create(['academic_class_id' => $this->class5->id, 'subject_id' => $this->maths->id]);
    // Only section B takes Urdu
    SubjectAssignment::create(['academic_class_id' => $this->class5->id, 'section_id' => $this->sectionB->id, 'subject_id' => $this->urdu->id]);

    $this->exam = Exam::create(['academic_session_id' => $this->session->id, 'name' => 'Mid-term']);
    $paper = fn (Subject $subject) => $this->exam->papers()->create(['academic_class_id' => $this->class5->id, 'subject_id' => $subject->id, 'total_marks' => 100, 'pass_marks' => 33]);
    $this->englishPaper = $paper($this->english);
    $this->mathsPaper = $paper($this->maths);
    $this->urduPaper = $paper($this->urdu);

    $place = function (?Section $section, string $roll, string $name) {
        $student = Student::factory()->create(['first_name' => $name, 'last_name' => '', 'is_active' => true]);

        return app(Enrollments::class)->enroll($student, $this->session, $this->class5, $section, $roll, Carbon::parse('2026-04-01'));
    };

    $this->a1 = $place($this->sectionA, '1', 'Amna');
    $this->a2 = $place($this->sectionA, '2', 'Bilal');
    $this->a3 = $place($this->sectionA, '3', 'Hina');
    $this->b1 = $place($this->sectionB, '1', 'Chand');

    $this->groupA = new AttendanceGroup($this->session, $this->class5, $this->sectionA);
    $this->groupB = new AttendanceGroup($this->session, $this->class5, $this->sectionB);

    $assign = fn (array $attributes) => TeacherAssignment::create(['academic_session_id' => $this->session->id, 'academic_class_id' => $this->class5->id, ...$attributes]);

    $this->classTeacher = userWithRole(UserRole::Teacher);
    $assign(['user_id' => $this->classTeacher->id, 'section_id' => $this->sectionA->id]);

    $this->mathsTeacher = userWithRole(UserRole::Teacher);
    $assign(['user_id' => $this->mathsTeacher->id, 'section_id' => $this->sectionA->id, 'subject_id' => $this->maths->id]);

    // A subject teacher assigned to the whole class covers every section
    $this->englishTeacher = userWithRole(UserRole::Teacher);
    $assign(['user_id' => $this->englishTeacher->id, 'subject_id' => $this->english->id]);

    $this->staff = userWithRole(UserRole::Staff);
    $this->scope = app(ExamScope::class);
    $this->sheet = app(MarkSheet::class);
    $this->results = app(ResultSheet::class);
});

afterEach(fn () => Carbon::setTestNow());

function enterMarks(ExamPaper $paper, array $obtained): void
{
    foreach ($obtained as $enrollmentId => $mark) {
        ExamMark::create([
            'exam_paper_id' => $paper->id,
            'enrollment_id' => $enrollmentId,
            'obtained_marks' => $mark === 'absent' ? null : $mark,
            'is_absent' => $mark === 'absent',
        ]);
    }
}

function subjects($papers): array
{
    return $papers->map(fn (ExamPaper $paper) => $paper->subject->name)->all();
}

describe('grading', function () {
    it('grades on the common scale', function (float $percent, string $grade) {
        expect(app(GradingScale::class)->grade($percent))->toBe($grade);
    })->with([
        [100, 'A1'], [80, 'A1'], [79.99, 'A'], [70, 'A'], [65, 'B'], [55, 'C'], [45, 'D'], [33, 'E'], [32.99, 'F'], [0, 'F'],
    ]);

    it('suggests a pass mark of 33 percent, rounded up', function () {
        expect(app(GradingScale::class)->suggestedPassMarks(100))->toBe(33)
            ->and(app(GradingScale::class)->suggestedPassMarks(50))->toBe(17)
            ->and(app(GradingScale::class)->legend())->toBe('A1 80%+ · A 70-79% · B 60-69% · C 50-59% · D 40-49% · E 33-39% · F below 33%');
    });
});

describe('who can enter which marks', function () {
    it('gives each section only the subjects it takes', function () {
        expect(subjects($this->scope->papersIn($this->exam, $this->groupA)))->toBe(['English', 'Maths'])
            ->and(subjects($this->scope->papersIn($this->exam, $this->groupB)))->toBe(['English', 'Maths', 'Urdu']);
    });

    it('treats subjects not set up for the class as taken by every section', function () {
        $science = Subject::factory()->create(['name' => 'Science']);
        $this->exam->papers()->create(['academic_class_id' => $this->class5->id, 'subject_id' => $science->id, 'total_marks' => 50, 'pass_marks' => 17]);

        expect(subjects($this->scope->papersIn($this->exam, $this->groupA)))->toContain('Science');
    });

    it('lets a class teacher enter every paper of their section only', function () {
        expect($this->scope->groupsFor($this->classTeacher, $this->exam)->map->key()->all())->toBe([$this->groupA->key()])
            ->and(subjects($this->scope->papersFor($this->classTeacher, $this->exam, $this->groupA)))->toBe(['English', 'Maths']);
    });

    it('lets a subject teacher enter only their subject', function () {
        expect(subjects($this->scope->papersFor($this->mathsTeacher, $this->exam, $this->groupA)))->toBe(['Maths'])
            ->and($this->scope->papersFor($this->mathsTeacher, $this->exam, $this->groupB))->toBeEmpty()
            ->and($this->scope->groupsFor($this->englishTeacher, $this->exam)->map->label()->all())->toBe(['Class 5 - A', 'Class 5 - B'])
            ->and(subjects($this->scope->papersFor($this->englishTeacher, $this->exam, $this->groupB)))->toBe(['English']);
    });

    it('ignores assignments from another session and inactive teachers', function () {
        $other = AcademicSession::factory()->startingIn(2025)->create();
        $teacher = userWithRole(UserRole::Teacher);
        TeacherAssignment::create(['user_id' => $teacher->id, 'academic_session_id' => $other->id, 'academic_class_id' => $this->class5->id]);
        $inactive = userWithRole(UserRole::Teacher, active: false);
        TeacherAssignment::create(['user_id' => $inactive->id, 'academic_session_id' => $this->session->id, 'academic_class_id' => $this->class5->id]);

        expect($this->scope->groupsFor($teacher, $this->exam))->toBeEmpty()
            ->and($this->scope->groupsFor($inactive, $this->exam))->toBeEmpty();
    });

    it('shows result sheets to class teachers and staff, not subject teachers', function () {
        expect($this->scope->resultGroupsFor($this->classTeacher, $this->exam)->map->label()->all())->toBe(['Class 5 - A'])
            ->and($this->scope->resultGroupsFor($this->mathsTeacher, $this->exam))->toBeEmpty()
            ->and($this->scope->resultGroupsFor($this->staff, $this->exam)->map->label()->all())->toBe(['Class 5 - A', 'Class 5 - B']);
    });
});

describe('the mark sheet', function () {
    it('saves part of a sheet, updates marks and clears blanked ones', function () {
        $this->sheet->save($this->mathsTeacher, $this->exam, $this->groupA, $this->mathsPaper, [
            $this->a1->id => ['obtained' => '78', 'remarks' => '  Good  '],
            $this->a2->id => ['obtained' => '', 'absent' => true],
            $this->a3->id => ['obtained' => ''],
        ]);

        expect(ExamMark::count())->toBe(2)
            ->and(ExamMark::where('enrollment_id', $this->a1->id)->sole()->only(['obtained_marks', 'is_absent', 'remarks', 'recorded_by']))
            ->toBe(['obtained_marks' => 78, 'is_absent' => false, 'remarks' => 'Good', 'recorded_by' => $this->mathsTeacher->id]);

        $this->sheet->save($this->mathsTeacher, $this->exam, $this->groupA, $this->mathsPaper, [
            $this->a1->id => ['obtained' => ''],
            $this->a2->id => ['obtained' => '40', 'absent' => false],
        ]);

        expect(ExamMark::count())->toBe(1)
            ->and(ExamMark::sole()->only(['enrollment_id', 'obtained_marks', 'is_absent']))->toBe(['enrollment_id' => $this->a2->id, 'obtained_marks' => 40, 'is_absent' => false]);
    });

    it('refuses marks above the maximum, negative or fractional marks', function (string $value) {
        expect(fn () => $this->sheet->save($this->classTeacher, $this->exam, $this->groupA, $this->mathsPaper, [$this->a1->id => ['obtained' => $value]]))
            ->toThrow(ExamException::class, 'Amna');
        expect(ExamMark::count())->toBe(0);
    })->with(['101', '-1', '45.5', 'abc']);

    it('refuses students from another section and papers the teacher does not teach', function () {
        expect(fn () => $this->sheet->save($this->classTeacher, $this->exam, $this->groupA, $this->mathsPaper, [$this->b1->id => ['obtained' => '50']]))
            ->toThrow(ExamException::class, 'not on this mark sheet');
        expect(fn () => $this->sheet->save($this->mathsTeacher, $this->exam, $this->groupA, $this->englishPaper, [$this->a1->id => ['obtained' => '50']]))
            ->toThrow(ExamException::class, 'do not teach English');
        expect(fn () => $this->sheet->save($this->classTeacher, $this->exam, $this->groupA, $this->urduPaper, [$this->a1->id => ['obtained' => '50']]))
            ->toThrow(ExamException::class, 'do not teach Urdu');
    });

    it('refuses everyone once the exam is locked', function () {
        $this->results->lock($this->exam);

        expect(fn () => $this->sheet->save($this->classTeacher, $this->exam, $this->groupA, $this->mathsPaper, [$this->a1->id => ['obtained' => '50']]))
            ->toThrow(ExamException::class, 'locked');
        expect(fn () => $this->sheet->save($this->staff, $this->exam, $this->groupA, $this->mathsPaper, [$this->a1->id => ['obtained' => '50']]))
            ->toThrow(ExamException::class, 'Unlock it');
    });

    it('keeps students who left after being marked, and drops those who left before', function () {
        enterMarks($this->mathsPaper, [$this->a1->id => 60]);
        app(Enrollments::class)->withdraw($this->a1->student, Carbon::parse('2026-10-20'));
        app(Enrollments::class)->withdraw($this->a3->student, Carbon::parse('2026-10-20'));

        expect($this->scope->roster($this->exam, $this->groupA)->map->student->map->first_name->all())->toBe(['Amna', 'Bilal']);
    });

    it('keeps promoted students on old exams', function () {
        $next = AcademicSession::factory()->startingIn(2027)->create();
        $class6 = AcademicClass::factory()->create(['name' => 'Class 6', 'sort_order' => 6]);
        app(Enrollments::class)->promote($this->a2->student, $next, $class6);

        expect($this->a2->fresh()->status)->toBe(EnrollmentStatus::Promoted)
            ->and($this->scope->roster($this->exam, $this->groupA)->map->student->map->first_name->all())->toBe(['Amna', 'Bilal', 'Hina']);
    });
});

describe('results', function () {
    beforeEach(function () {
        // Amna 160/200, Bilal fails maths, Hina absent in English, Chand 150/300 in B
        enterMarks($this->englishPaper, [$this->a1->id => 80, $this->a2->id => 90, $this->a3->id => 'absent', $this->b1->id => 50]);
        enterMarks($this->mathsPaper, [$this->a1->id => 80, $this->a2->id => 20, $this->a3->id => 95, $this->b1->id => 50]);
        enterMarks($this->urduPaper, [$this->b1->id => 50]);
    });

    it('totals, grades and positions a section', function () {
        $rows = $this->results->forGroup($this->exam, $this->groupA)->keyBy(fn ($row) => $row['enrollment']->student->first_name);

        expect($rows['Amna'])->toMatchArray(['obtained' => 160, 'total' => 200, 'percent' => 80.0, 'grade' => 'A1', 'result' => ExaminationResultStatus::Pass, 'position' => 1])
            ->and($rows['Bilal'])->toMatchArray(['obtained' => 110, 'grade' => 'F', 'result' => ExaminationResultStatus::Fail, 'failed' => [$this->mathsPaper->id], 'position' => null])
            ->and($rows['Hina'])->toMatchArray(['obtained' => 95, 'grade' => 'F', 'result' => ExaminationResultStatus::Fail, 'failed' => [$this->englishPaper->id]]);
    });

    it('shares positions on equal totals', function () {
        ExamMark::where('enrollment_id', $this->a2->id)->where('exam_paper_id', $this->mathsPaper->id)->update(['obtained_marks' => 70]);

        $positions = $this->results->forGroup($this->exam, $this->groupA)->mapWithKeys(fn ($row) => [$row['enrollment']->student->first_name => $row['position']])->all();

        expect($positions)->toBe(['Amna' => 1, 'Bilal' => 1, 'Hina' => null]);
    });

    it('leaves students with missing marks incomplete', function () {
        ExamMark::where('enrollment_id', $this->a1->id)->where('exam_paper_id', $this->mathsPaper->id)->delete();

        $amna = $this->results->forGroup($this->exam, $this->groupA)->first();

        expect($amna)->toMatchArray(['result' => null, 'grade' => null, 'position' => null, 'failed' => []]);
    });

    it('publishes to the public results only after locking, and unpublishes cleanly', function () {
        expect(fn () => $this->results->publish($this->exam))->toThrow(ExamException::class, 'Lock the exam');

        $this->results->lock($this->exam);

        expect($this->results->publish($this->exam->fresh()))->toBe(4)
            ->and($this->exam->fresh()->status())->toBe(ExamStatus::Published);

        $amna = ExaminationResult::where('student_id', $this->a1->student_id)->sole();
        expect($amna->only(['exam_id', 'academic_class_id', 'year', 'detail', 'total_marks', 'obtained_marks', 'status', 'grade']))->toBe([
            'exam_id' => $this->exam->id, 'academic_class_id' => $this->class5->id, 'year' => '2026-2027', 'detail' => 'Mid-term',
            'total_marks' => 200, 'obtained_marks' => 160, 'status' => ExaminationResultStatus::Pass, 'grade' => 'A1',
        ]);

        // Published results must be unpublished before the marks can change
        expect(fn () => $this->results->unlock($this->exam->fresh()))->toThrow(ExamException::class, 'Unpublish');

        // Hand-entered results are left alone
        $manual = ExaminationResult::create(['student_id' => $this->a1->student_id, 'detail' => 'Annual 2025']);
        $this->results->unpublish($this->exam->fresh());

        expect(ExaminationResult::pluck('id')->all())->toBe([$manual->id])
            ->and($this->exam->fresh()->status())->toBe(ExamStatus::Locked);
    });

    it('re-publishing replaces the earlier copy instead of duplicating', function () {
        $this->results->lock($this->exam);
        $this->results->publish($this->exam->fresh());
        $this->results->unpublish($this->exam->fresh());
        $this->results->publish($this->exam->fresh());

        expect(ExaminationResult::count())->toBe(4);
    });

    it('refuses to publish while marks are missing', function () {
        ExamMark::where('enrollment_id', $this->b1->id)->where('exam_paper_id', $this->urduPaper->id)->delete();
        $this->results->lock($this->exam);

        expect(fn () => $this->results->publish($this->exam->fresh()))->toThrow(ExamException::class, 'Chand');
        expect(ExaminationResult::count())->toBe(0);
    });

    it('prints result cards for allowed users only', function () {
        $url = route('exams.result-cards', ['exam' => $this->exam, 'group' => $this->groupA->key()]);

        $this->actingAs($this->classTeacher)->get($url)->assertOk()->assertSee('Amna')->assertSee('1 of 3')->assertDontSee('Chand');
        $this->actingAs($this->mathsTeacher)->get($url)->assertForbidden();
        $this->actingAs($this->staff)->get(route('exams.result-cards', ['exam' => $this->exam, 'group' => $this->groupB->key()]))->assertOk()->assertSee('Chand');
        $this->actingAs($this->staff)->get(route('exams.result-cards', ['exam' => $this->exam, 'group' => 'nonsense']))->assertNotFound();
    });

    it('prints the whole class on one result sheet for allowed users only', function () {
        $url = route('exams.result-sheet', ['exam' => $this->exam, 'group' => $this->groupA->key()]);

        $this->actingAs($this->classTeacher)->get($url)->assertOk()
            ->assertSeeInOrder(['Amna', 'Bilal', 'Hina'])
            ->assertSee('Abs')
            ->assertSee('Pass rate:')
            ->assertSee('33.3%')
            ->assertDontSee('Chand');
        $this->actingAs($this->mathsTeacher)->get($url)->assertForbidden();
        $this->actingAs($this->staff)->get(route('exams.result-sheet', ['exam' => $this->exam, 'group' => $this->groupB->key()]))->assertOk()->assertSee('Urdu');
    });

    it('sends guests to log in before printing', function () {
        $this->get(route('exams.result-cards', ['exam' => $this->exam, 'group' => $this->groupA->key()]))->assertRedirect('/login');
    });
});

describe('pages', function () {
    it('lets teachers enter marks and see results in the teacher panel', function () {
        $this->actingAs($this->classTeacher);

        $this->get('/teacher/enter-marks')->assertOk()->assertSee('Class 5 - A')->assertDontSee('Class 5 - B');
        $this->get('/teacher/exam-results')->assertOk()->assertSee('Amna');

        $this->actingAs($this->mathsTeacher)->get('/teacher/enter-marks')->assertOk()->assertSee('Maths')->assertDontSee('English');
    });

    it('saves marks from the page', function () {
        Filament::setCurrentPanel(Filament::getPanel('teacher'));
        $this->actingAs($this->mathsTeacher);

        Livewire::test(EnterMarks::class)
            ->assertSet('paper', (string) $this->mathsPaper->id)
            ->set("marks.{$this->a1->id}.obtained", '67')
            ->set("marks.{$this->a2->id}.absent", true)
            ->call('save')
            ->assertNotified();

        expect(ExamMark::where('exam_paper_id', $this->mathsPaper->id)->pluck('obtained_marks', 'enrollment_id')->all())
            ->toBe([$this->a1->id => 67, $this->a2->id => null]);
    });

    it('shows a locked sheet without letting it be saved', function () {
        $this->results->lock($this->exam);
        Filament::setCurrentPanel(Filament::getPanel('teacher'));
        $this->actingAs($this->mathsTeacher);

        Livewire::test(EnterMarks::class)
            ->assertSee('locked')
            ->set("marks.{$this->a1->id}.obtained", '67')
            ->call('save');

        expect(ExamMark::count())->toBe(0);
    });

    it('lets staff manage exams and papers in the admin panel', function () {
        $this->actingAs($this->staff);

        $this->get('/admin/exams')->assertOk()->assertSee('Mid-term');
        $this->get('/admin/exams/create')->assertOk();
        $this->get('/admin/enter-marks')->assertOk();
        $this->get('/admin/exam-results')->assertOk();

        Livewire::test(PapersRelationManager::class, ['ownerRecord' => $this->exam, 'pageClass' => EditExam::class])
            ->assertCanSeeTableRecords([$this->englishPaper, $this->mathsPaper, $this->urduPaper]);
    });

    it('adds one paper per class subject, skipping existing papers', function () {
        $science = Subject::factory()->create(['name' => 'Science']);
        SubjectAssignment::create(['academic_class_id' => $this->class5->id, 'subject_id' => $science->id]);
        $this->actingAs($this->staff);

        Livewire::test(PapersRelationManager::class, ['ownerRecord' => $this->exam, 'pageClass' => EditExam::class])
            ->callTableAction('addClassSubjects', data: ['academic_class_id' => $this->class5->id, 'total_marks' => 50]);

        expect($this->exam->papers()->count())->toBe(4)
            ->and($this->exam->papers()->where('subject_id', $science->id)->sole()->only(['total_marks', 'pass_marks']))->toBe(['total_marks' => 50, 'pass_marks' => 17]);
    });

    it('keeps exams and papers with marks from being deleted', function () {
        $empty = Exam::create(['academic_session_id' => $this->session->id, 'name' => 'Final']);
        enterMarks($this->mathsPaper, [$this->a1->id => 50]);

        expect($this->staff->can('delete', $empty))->toBeTrue()
            ->and($this->staff->can('delete', $this->exam))->toBeFalse()
            ->and($this->staff->can('delete', $this->mathsPaper))->toBeFalse()
            ->and($this->staff->can('delete', $this->englishPaper))->toBeTrue()
            ->and($this->classTeacher->can('create', Exam::class))->toBeFalse();
    });

    it('keeps teachers out of the exam setup', function () {
        $this->actingAs($this->classTeacher)->get('/admin/exams')->assertForbidden();
    });
});
