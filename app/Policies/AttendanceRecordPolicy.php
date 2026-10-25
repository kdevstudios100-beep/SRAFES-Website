<?php

namespace App\Policies;

use App\Enum\Permission;
use App\Models\User;

/**
 * Attendance is read here; it is written only through the attendance
 * register page (App\Support\Attendance\AttendanceRegister), which applies
 * the per-section and edit-window rules.
 */
class AttendanceRecordPolicy extends ResourcePolicy
{
    protected function viewPermission(): Permission
    {
        return Permission::ManageAttendance;
    }

    protected function managePermission(): Permission
    {
        return Permission::ManageAttendance;
    }

    public function create(User $user): bool
    {
        return false;
    }

    public function update(User $user, mixed $record): bool
    {
        return false;
    }

    public function delete(User $user, mixed $record): bool
    {
        return false;
    }

    public function deleteAny(User $user): bool
    {
        return false;
    }
}
