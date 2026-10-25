<?php

namespace App\Policies;

use App\Enum\Permission;
use App\Models\User;

class EnrollmentPolicy extends ResourcePolicy
{
    protected function viewPermission(): Permission
    {
        return Permission::ViewStudents;
    }

    protected function managePermission(): Permission
    {
        return Permission::ManageEnrollments;
    }

    // Enrollments are opened and closed only through App\Support\Academics\Enrollments; the history itself is never deleted
    public function create(User $user): bool
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

    public function forceDelete(User $user, mixed $record): bool
    {
        return false;
    }

    public function forceDeleteAny(User $user): bool
    {
        return false;
    }

    public function replicate(User $user, mixed $record): bool
    {
        return false;
    }

    // Placement changes (enroll, transfer, promote, graduate, withdraw) are allowed for these users
    public function changePlacement(User $user): bool
    {
        return $this->canManage($user);
    }
}
