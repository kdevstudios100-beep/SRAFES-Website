<?php

namespace App\Policies;

use App\Enum\Permission;
use App\Models\User;

class AcademicSessionPolicy extends ResourcePolicy
{
    protected function viewPermission(): Permission
    {
        return Permission::ViewStudents;
    }

    protected function managePermission(): Permission
    {
        return Permission::ManageAcademicSessions;
    }

    // A session that holds enrollments is history and can't be deleted
    public function delete(User $user, mixed $record): bool
    {
        return $this->canManage($user) && ! $record->enrollments()->exists();
    }
}
