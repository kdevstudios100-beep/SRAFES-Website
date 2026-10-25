<?php

namespace App\Support\Exams;

use App\Enum\ExaminationResultStatus;
use App\Models\Enrollment;
use App\Models\Exam;
use App\Models\ExaminationResult;
use App\Models\ExamMark;
use App\Models\ExamPaper;
use App\Support\Attendance\AttendanceGroup;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Totals, grades and positions from an exam's marks, and the exam's
 * lock / publish life cycle.
 *
 * A student passes by passing every paper; absent counts as 0 and a fail.
 * Grades come from the overall percentage, except that failing students are
 * graded F. Positions rank passing students by total marks; equal totals
 * share a position (1, 1, 3). A student missing any mark is "incomplete" and
 * gets no result, grade or position.
 */
class ResultSheet
{
    public function __construct(
        private readonly ExamScope $scope,
        private readonly GradingScale $scale,
    ) {}

    /**
     * @return Collection<int, array{
     *     enrollment: Enrollment,
     *     marks: array<int, ?ExamMark>,
     *     obtained: int,
     *     total: int,
     *     percent: float,
     *     grade: ?string,
     *     result: ?ExaminationResultStatus,
     *     failed: list<int>,
     *     position: ?int,
     * }> one row per student in roll order; marks keyed by paper id
     */
    public function forGroup(Exam $exam, AttendanceGroup $group, ?Collection $papers = null): Collection
    {
        $papers ??= $this->scope->papersIn($exam, $group);
        $roster = $this->scope->roster($exam, $group);

        $marks = ExamMark::query()
            ->whereIn('exam_paper_id', $papers->pluck('id'))
            ->whereIn('enrollment_id', $roster->pluck('id'))
            ->get()
            ->groupBy('enrollment_id');

        $rows = $roster->map(function (Enrollment $enrollment) use ($papers, $marks) {
            $own = $marks->get($enrollment->id, collect())->keyBy('exam_paper_id');
            $byPaper = $papers->mapWithKeys(fn (ExamPaper $paper) => [$paper->id => $own->get($paper->id)])->all();

            $complete = $papers->isNotEmpty() && collect($byPaper)->every(fn (?ExamMark $mark) => $mark !== null);
            $obtained = (int) collect($byPaper)->sum(fn (?ExamMark $mark) => $mark?->obtained_marks ?? 0);
            $total = (int) $papers->sum('total_marks');
            $percent = $total > 0 ? round($obtained / $total * 100, 2) : 0.0;
            $failed = $papers->reject(fn (ExamPaper $paper) => $paper->passes($byPaper[$paper->id]))->pluck('id')->all();

            $result = match (true) {
                ! $complete => null,
                $failed !== [] => ExaminationResultStatus::Fail,
                default => ExaminationResultStatus::Pass,
            };

            return [
                'enrollment' => $enrollment,
                'marks' => $byPaper,
                'obtained' => $obtained,
                'total' => $total,
                'percent' => $percent,
                'grade' => match ($result) {
                    null => null,
                    ExaminationResultStatus::Fail => $this->scale->failGrade(),
                    default => $this->scale->grade($percent),
                },
                'result' => $result,
                'failed' => $result === null ? [] : $failed,
                'position' => null,
            ];
        });

        return $this->withPositions($rows);
    }

    // Grade for one paper's mark, for result cards
    public function paperGrade(ExamPaper $paper, ?ExamMark $mark): ?string
    {
        if ($mark === null) {
            return null;
        }

        if (! $paper->passes($mark)) {
            return $this->scale->failGrade();
        }

        return $this->scale->grade($mark->obtained_marks / max($paper->total_marks, 1) * 100);
    }

    // Top positions (config srafes.exams.position_holders), highlighted on sheets and cards
    public function isPositionHolder(?int $position): bool
    {
        return $position !== null && $position <= (int) config('srafes.exams.position_holders', 3);
    }

    // 1st, 2nd, 3rd, 4th, 11th, 21st ...
    public function ordinal(int $position): string
    {
        $suffix = in_array($position % 100, [11, 12, 13], true) ? 'th' : match ($position % 10) {
            1 => 'st',
            2 => 'nd',
            3 => 'rd',
            default => 'th',
        };

        return $position.$suffix;
    }

    public function lock(Exam $exam): void
    {
        if ($exam->isLocked()) {
            return;
        }

        $exam->forceFill(['locked_at' => now()])->save();
    }

    public function unlock(Exam $exam): void
    {
        if ($exam->published_at !== null) {
            throw new ExamException('Unpublish the results before unlocking, so the public results never differ from the marks.');
        }

        $exam->forceFill(['locked_at' => null])->save();
    }

    /**
     * Copy every student's result into the public examination results, replacing
     * any earlier copy of this exam's results.
     *
     * @return int number of results published
     */
    public function publish(Exam $exam): int
    {
        if (! $exam->isLocked()) {
            throw new ExamException('Lock the exam before publishing, so marks cannot change afterwards.');
        }

        // A student transferred after sitting some papers is on two sheets; their complete row wins
        $rows = $this->scope->groupsIn($exam)
            ->flatMap(fn (AttendanceGroup $group) => $this->forGroup($exam, $group))
            ->groupBy(fn (array $row) => $row['enrollment']->student_id)
            ->map(fn (Collection $rows) => $rows->firstWhere('result', '!==', null) ?? $rows->first())
            ->values();

        if ($rows->isEmpty()) {
            throw new ExamException('There are no students with papers in this exam yet.');
        }

        $incomplete = $rows->whereNull('result');

        if ($incomplete->isNotEmpty()) {
            $names = $incomplete->take(10)->map(fn (array $row) => $row['enrollment']->student->name.' ('.$row['enrollment']->placement.')')->implode(', ');

            throw new ExamException("{$incomplete->count()} students are missing marks, so results cannot be published: {$names}".($incomplete->count() > 10 ? ', ...' : '.'));
        }

        return DB::transaction(function () use ($exam, $rows) {
            $studentIds = $rows->map(fn (array $row) => $row['enrollment']->student_id);

            // Through Eloquent so any uploaded result file is removed too
            $exam->examinationResults()->whereNotIn('student_id', $studentIds)->get()->each->delete();

            foreach ($rows as $row) {
                ExaminationResult::query()->updateOrCreate(
                    ['exam_id' => $exam->id, 'student_id' => $row['enrollment']->student_id],
                    [
                        'academic_class_id' => $row['enrollment']->academic_class_id,
                        'year' => $exam->academicSession->name,
                        'detail' => $exam->name,
                        'total_marks' => $row['total'],
                        'obtained_marks' => $row['obtained'],
                        'status' => $row['result'],
                        'grade' => $row['grade'],
                    ],
                );
            }

            $exam->forceFill(['published_at' => now()])->save();

            return $rows->count();
        });
    }

    public function unpublish(Exam $exam): void
    {
        DB::transaction(function () use ($exam) {
            $exam->examinationResults()->get()->each->delete();
            $exam->forceFill(['published_at' => null])->save();
        });
    }

    private function withPositions(Collection $rows): Collection
    {
        $passed = $rows->where('result', ExaminationResultStatus::Pass)->sortByDesc('obtained');
        $positions = [];
        $previous = null;
        $position = 0;

        foreach ($passed->values() as $index => $row) {
            if ($row['obtained'] !== $previous) {
                $position = $index + 1;
                $previous = $row['obtained'];
            }

            $positions[$row['enrollment']->id] = $position;
        }

        return $rows->map(fn (array $row) => [...$row, 'position' => $positions[$row['enrollment']->id] ?? null])->values();
    }
}
