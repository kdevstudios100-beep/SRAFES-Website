<?php

namespace App\Support\Attendance;

use App\Enum\Permission;
use App\Models\AcademicClass;
use App\Models\AcademicSession;
use App\Models\Enrollment;
use App\Models\Section;
use App\Models\TeacherAssignment;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

/**
 * Who may see and mark which attendance registers. Staff with "manage
 * attendance" cover every register on any date; teachers cover only the
 * registers they are class teacher of, and only within the edit window.
 */
class AttendanceScope
{
    public function canManageAll(User $user): bool
    {
        return $this->allows($user, Permission::ManageAttendance);
    }

    public function canUseAttendance(User $user): bool
    {
        return $this->canManageAll($user) || $this->allows($user, Permission::MarkAttendance);
    }

    /**
     * Registers the user may open in $session, in class order.
     *
     * @return Collection<int, AttendanceGroup>
     */
    public function groupsFor(User $user, AcademicSession $session): Collection
    {
        if ($this->canManageAll($user)) {
            return $this->allGroups($session);
        }

        if (! $this->allows($user, Permission::MarkAttendance)) {
            return collect();
        }

        return TeacherAssignment::query()
            ->classTeacher()
            ->where('user_id', $user->id)
            ->where('academic_session_id', $session->id)
            ->with(['academicClass', 'section'])
            ->get()
            ->map(fn (TeacherAssignment $assignment) => new AttendanceGroup($session, $assignment->academicClass, $assignment->section))
            ->unique(fn (AttendanceGroup $group) => $group->key())
            ->sortBy([fn ($a, $b) => [$a->class->sort_order ?? PHP_INT_MAX, $a->label()] <=> [$b->class->sort_order ?? PHP_INT_MAX, $b->label()]])
            ->values();
    }

    public function canView(User $user, AttendanceGroup $group): bool
    {
        return $this->groupsFor($user, $group->session)->contains(fn (AttendanceGroup $allowed) => $allowed->is($group));
    }

    // Null when allowed, otherwise the reason
    public function whyCannotMark(User $user, AttendanceGroup $group, Carbon $date): ?string
    {
        if (! $this->canView($user, $group)) {
            return 'You are not the class teacher of '.$group->label().'.';
        }

        if ($date->isAfter(today())) {
            return 'Attendance cannot be marked for a future date.';
        }

        if (! $date->betweenIncluded($group->session->starts_on, $group->session->ends_on)) {
            return "{$date->toFormattedDateString()} is outside the {$group->session->name} session.";
        }

        $editDays = (int) config('srafes.attendance.teacher_edit_days', 7);

        if (! $this->canManageAll($user) && $date->lt(today()->subDays($editDays))) {
            return "Teachers can mark attendance up to {$editDays} days back. Ask the office to correct older dates.";
        }

        return null;
    }

    /**
     * Students on the register for $date, in roll-number order.
     *
     * @return Collection<int, Enrollment>
     */
    public function roster(AttendanceGroup $group, Carbon $date): Collection
    {
        return $group->enrollments()
            ->coveringDate($date->toDateString())
            ->with('student')
            ->get()
            ->sortBy([
                fn (Enrollment $a, Enrollment $b) => strnatcasecmp($a->roll_number ?? "\u{FFFF}", $b->roll_number ?? "\u{FFFF}"),
                fn (Enrollment $a, Enrollment $b) => strnatcasecmp($a->student->name, $b->student->name),
            ])
            ->values();
    }

    /**
     * Every class (or section) that has had at least one placement in $session.
     *
     * @return Collection<int, AttendanceGroup>
     */
    private function allGroups(AcademicSession $session): Collection
    {
        $used = Enrollment::query()
            ->where('academic_session_id', $session->id)
            ->select(['academic_class_id', 'section_id'])
            ->distinct()
            ->get();

        $classes = AcademicClass::query()->whereIn('id', $used->pluck('academic_class_id'))->ordered()->get();
        $sections = Section::query()->whereIn('id', $used->pluck('section_id')->filter())->get()->keyBy('id');

        return $classes->flatMap(fn (AcademicClass $class) => $used
            ->where('academic_class_id', $class->id)
            ->sortBy(fn ($row) => $row->section_id ? $sections[$row->section_id]->name : '')
            ->map(fn ($row) => new AttendanceGroup($session, $class, $row->section_id ? $sections[$row->section_id] : null)))
            ->values();
    }

    private function allows(User $user, Permission $permission): bool
    {
        return $user->is_active && $user->checkPermissionTo($permission->value);
    }
}
