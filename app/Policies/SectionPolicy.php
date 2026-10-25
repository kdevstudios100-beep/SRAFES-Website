<?php

namespace App\Policies;

use App\Enum\Permission;
use App\Models\User;

class SectionPolicy extends ResourcePolicy
{
    protected function viewPermission(): Permission
    {
        return Permission::ViewStudents;
    }

    protected function managePermission(): Permission
    {
        return Permission::ManageClasses;
    }

    // Students' placement history points at the section
    public function delete(User $user, mixed $record): bool
    {
        return $this->canManage($user) && ! $record->enrollments()->exists();
    }
}
