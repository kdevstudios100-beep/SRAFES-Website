<?php

namespace App\Support\Attendance;

use App\Enum\AttendanceStatus;
use App\Models\AttendanceRecord;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Saves a whole day's register for one class or section. Every student on
 * the register must be marked, and only students on it can be, so a saved
 * day is always complete.
 */
class AttendanceRegister
{
    public function __construct(private readonly AttendanceScope $scope) {}

    /**
     * @return Collection<int, AttendanceRecord> existing marks keyed by enrollment id
     */
    public function existing(AttendanceGroup $group, Carbon $date): Collection
    {
        return AttendanceRecord::query()
            ->whereIn('enrollment_id', $group->enrollments()->select('id'))
            ->whereDate('date', $date->toDateString())
            ->get()
            ->keyBy('enrollment_id');
    }

    /**
     * @param  array<int|string, array{status: ?string, remarks?: ?string}>  $marks  keyed by enrollment id
     * @return int number of students saved
     */
    public function save(User $user, AttendanceGroup $group, Carbon $date, array $marks): int
    {
        if ($reason = $this->scope->whyCannotMark($user, $group, $date)) {
            throw new AttendanceException($reason);
        }

        $roster = $this->scope->roster($group, $date)->keyBy('id');

        if ($roster->isEmpty()) {
            throw new AttendanceException('No students are enrolled in '.$group->label().' on this date.');
        }

        // Ids come from the browser: anything not on today's register is refused outright
        if (array_diff(array_map('intval', array_keys($marks)), $roster->keys()->all())) {
            throw new AttendanceException('Some students are not on this register. Reload the page and try again.');
        }

        $unmarked = $roster->filter(fn ($enrollment, $id) => AttendanceStatus::tryFrom((string) ($marks[$id]['status'] ?? '')) === null);

        if ($unmarked->isNotEmpty()) {
            throw new AttendanceException('Mark every student before saving. Not marked: '.$unmarked->map(fn ($enrollment) => $enrollment->student->name)->implode(', ').'.');
        }

        return DB::transaction(function () use ($user, $date, $marks, $roster) {
            foreach ($roster as $id => $enrollment) {
                $remarks = trim((string) ($marks[$id]['remarks'] ?? ''));

                // A Carbon value is formatted the same way the date cast stores it, on SQLite and MySQL alike
                AttendanceRecord::query()->updateOrCreate(
                    ['enrollment_id' => $id, 'date' => $date->copy()->startOfDay()],
                    [
                        'status' => AttendanceStatus::from($marks[$id]['status']),
                        'remarks' => $remarks === '' ? null : mb_substr($remarks, 0, 255),
                        'recorded_by' => $user->id,
                    ],
                );
            }

            return $roster->count();
        });
    }
}
