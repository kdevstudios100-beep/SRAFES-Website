<?php

namespace App\Support\Attendance;

use App\Enum\AttendanceStatus;
use App\Models\AcademicSession;
use App\Models\AttendanceRecord;
use App\Models\Enrollment;
use App\Models\Student;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

/**
 * Attendance totals. Percentage = (present + late) / (days marked - leave):
 * approved leave neither counts for nor against a student.
 */
class AttendanceSummary
{
    public function __construct(private readonly AttendanceScope $scope) {}

    /**
     * Totals for every student placed in the register between $from and $to.
     *
     * @return Collection<int, array{enrollment: Enrollment, counts: array<string, int>, days: int, percent: ?float, low: bool}>
     */
    public function forGroup(AttendanceGroup $group, Carbon $from, Carbon $to): Collection
    {
        $enrollments = $group->enrollments()->with('student')->get()->keyBy('id');
        $counts = $this->countsByEnrollment($enrollments->keys(), $from, $to);

        return $enrollments
            ->map(fn (Enrollment $enrollment) => $this->row($counts[$enrollment->id] ?? []) + ['enrollment' => $enrollment])
            // Students with no marks in the range (e.g. joined later) are left out of the report
            ->filter(fn (array $row) => $row['days'] > 0)
            ->sortBy([
                fn ($a, $b) => strnatcasecmp($a['enrollment']->roll_number ?? "\u{FFFF}", $b['enrollment']->roll_number ?? "\u{FFFF}"),
                fn ($a, $b) => strnatcasecmp($a['enrollment']->student->name, $b['enrollment']->student->name),
            ])
            ->values();
    }

    /**
     * Totals per placement for one student, newest first.
     *
     * @return Collection<int, array{enrollment: Enrollment, counts: array<string, int>, days: int, percent: ?float, low: bool}>
     */
    public function forStudent(Student $student): Collection
    {
        $enrollments = $student->enrollments()->with(['academicSession', 'academicClass', 'section'])->orderByDesc('id')->get()->keyBy('id');
        $counts = $this->countsByEnrollment($enrollments->keys());

        return $enrollments
            ->map(fn (Enrollment $enrollment) => $this->row($counts[$enrollment->id] ?? []) + ['enrollment' => $enrollment])
            ->filter(fn (array $row) => $row['days'] > 0)
            ->values();
    }

    /**
     * For one date: each register the user may see, how many are on it and whether it was taken.
     *
     * @return Collection<int, array{group: AttendanceGroup, on_roll: int, marked: int, counts: array<string, int>}>
     */
    public function day(User $user, AcademicSession $session, Carbon $date): Collection
    {
        return $this->scope->groupsFor($user, $session)->map(function (AttendanceGroup $group) use ($date) {
            $rosterIds = $group->enrollments()->coveringDate($date->toDateString())->pluck('id');
            $counts = AttendanceRecord::query()
                ->whereIn('enrollment_id', $rosterIds)
                ->whereDate('date', $date->toDateString())
                ->selectRaw('status, COUNT(*) as total')
                ->groupBy('status')
                ->pluck('total', 'status')
                ->map(fn ($total) => (int) $total)
                ->all();

            return [
                'group' => $group,
                'on_roll' => $rosterIds->count(),
                'marked' => array_sum($counts),
                'counts' => $this->fillCounts($counts),
            ];
        })->filter(fn (array $row) => $row['on_roll'] > 0)->values();
    }

    /**
     * @return array<int, array<string, int>> enrollment id => status => days
     */
    private function countsByEnrollment(Collection $enrollmentIds, ?Carbon $from = null, ?Carbon $to = null): array
    {
        $counts = [];

        AttendanceRecord::query()
            ->whereIn('enrollment_id', $enrollmentIds)
            ->when($from, fn ($query) => $query->whereDate('date', '>=', $from->toDateString()))
            ->when($to, fn ($query) => $query->whereDate('date', '<=', $to->toDateString()))
            ->selectRaw('enrollment_id, status, COUNT(*) as total')
            ->groupBy('enrollment_id', 'status')
            ->toBase()
            ->get()
            ->each(function ($row) use (&$counts) {
                $counts[$row->enrollment_id][$row->status] = (int) $row->total;
            });

        return $counts;
    }

    /**
     * @param  array<string, int>  $counts
     */
    private function row(array $counts): array
    {
        $counts = $this->fillCounts($counts);
        $days = array_sum($counts);
        $countable = $days - $counts[AttendanceStatus::Leave->value];
        $attended = $counts[AttendanceStatus::Present->value] + $counts[AttendanceStatus::Late->value];
        $percent = $countable > 0 ? round($attended / $countable * 100, 1) : null;

        return [
            'counts' => $counts,
            'days' => $days,
            'percent' => $percent,
            'low' => $percent !== null && $percent < (float) config('srafes.attendance.low_attendance_percent', 75),
        ];
    }

    private function fillCounts(array $counts): array
    {
        return collect(AttendanceStatus::cases())
            ->mapWithKeys(fn (AttendanceStatus $status) => [$status->value => (int) ($counts[$status->value] ?? 0)])
            ->all();
    }
}
