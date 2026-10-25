<?php

namespace App\Support\Exams;

use App\Models\Exam;
use App\Models\ExamMark;
use App\Models\ExamPaper;
use App\Models\User;
use App\Support\Attendance\AttendanceGroup;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Saves one paper's marks for one class or section. Unlike attendance a
 * sheet may be saved part-filled: a blank mark is simply not entered yet
 * (and clears any earlier mark), so teachers can enter marks as they check
 * papers. Results stay "incomplete" until every mark is in.
 */
class MarkSheet
{
    public function __construct(private readonly ExamScope $scope) {}

    /**
     * @return Collection<int, ExamMark> existing marks keyed by enrollment id
     */
    public function existing(ExamPaper $paper, AttendanceGroup $group): Collection
    {
        return ExamMark::query()
            ->where('exam_paper_id', $paper->id)
            ->whereIn('enrollment_id', $group->enrollments()->select('id'))
            ->with('recordedBy')
            ->get()
            ->keyBy('enrollment_id');
    }

    /**
     * @param  array<int|string, array{obtained?: mixed, absent?: mixed, remarks?: ?string}>  $marks  keyed by enrollment id
     * @return int number of students with a mark (or absent) after saving
     */
    public function save(User $user, Exam $exam, AttendanceGroup $group, ExamPaper $paper, array $marks): int
    {
        if ($reason = $this->scope->whyCannotEnter($user, $exam, $group, $paper)) {
            throw new ExamException($reason);
        }

        $roster = $this->scope->roster($exam, $group)->keyBy('id');

        // Ids come from the browser: anything not on this sheet is refused outright
        if (array_diff(array_map('intval', array_keys($marks)), $roster->keys()->all())) {
            throw new ExamException('Some students are not on this mark sheet. Reload the page and try again.');
        }

        $parsed = [];
        $invalid = [];

        foreach ($marks as $id => $mark) {
            $absent = filter_var($mark['absent'] ?? false, FILTER_VALIDATE_BOOLEAN);
            $obtained = trim((string) ($mark['obtained'] ?? ''));

            if (! $absent && $obtained !== '' && (! ctype_digit($obtained) || (int) $obtained > $paper->total_marks)) {
                $invalid[] = $roster[(int) $id]->student->name;

                continue;
            }

            $remarks = trim((string) ($mark['remarks'] ?? ''));

            $parsed[(int) $id] = [
                'absent' => $absent,
                'obtained' => $absent || $obtained === '' ? null : (int) $obtained,
                'remarks' => $remarks === '' ? null : mb_substr($remarks, 0, 255),
            ];
        }

        if ($invalid) {
            throw new ExamException("Marks must be whole numbers from 0 to {$paper->total_marks}. Check: ".implode(', ', $invalid).'.');
        }

        return DB::transaction(function () use ($user, $paper, $parsed) {
            foreach ($parsed as $id => $mark) {
                if (! $mark['absent'] && $mark['obtained'] === null) {
                    ExamMark::query()->where('exam_paper_id', $paper->id)->where('enrollment_id', $id)->delete();

                    continue;
                }

                ExamMark::query()->updateOrCreate(
                    ['exam_paper_id' => $paper->id, 'enrollment_id' => $id],
                    [
                        'obtained_marks' => $mark['obtained'],
                        'is_absent' => $mark['absent'],
                        'remarks' => $mark['remarks'],
                        'recorded_by' => $user->id,
                    ],
                );
            }

            return collect($parsed)->filter(fn (array $mark) => $mark['absent'] || $mark['obtained'] !== null)->count();
        });
    }
}
