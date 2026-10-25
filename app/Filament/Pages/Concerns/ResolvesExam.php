<?php

namespace App\Filament\Pages\Concerns;

use App\Models\Exam;
use App\Support\Attendance\AttendanceGroup;
use App\Support\Exams\ExamScope;
use Illuminate\Support\Collection;

/**
 * Shared by the exam pages. The exam id and group key are plain strings from
 * the browser, so both are resolved and permission-checked again on every
 * request rather than trusted.
 */
trait ResolvesExam
{
    public static function canAccess(): bool
    {
        $user = auth()->user();

        return $user !== null && app(ExamScope::class)->canUseExams($user);
    }

    /**
     * Exams that have papers, newest session first.
     *
     * @return Collection<int, Exam>
     */
    protected function examOptions(): Collection
    {
        return Exam::query()->has('papers')->with('academicSession')->latestFirst()->get();
    }

    // The exam the id names, or the newest open exam, or the newest exam
    protected function resolveExam(?string $id, Collection $exams): ?Exam
    {
        return $exams->firstWhere('id', (int) $id)
            ?? $exams->first(fn (Exam $exam) => ! $exam->isLocked())
            ?? $exams->first();
    }

    // The group the key names if it is among $groups, otherwise the first one
    protected function resolveGroup(?string $key, Collection $groups): ?AttendanceGroup
    {
        return $groups->first(fn (AttendanceGroup $group) => $group->key() === $key) ?? $groups->first();
    }
}
