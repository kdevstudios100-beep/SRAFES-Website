<?php

namespace App\Policies;

use App\Enum\Permission;
use App\Models\User;

class AcademicClassPolicy extends ResourcePolicy
{
    protected function viewPermission(): Permission
    {
        return Permission::ViewStudents;
    }

    protected function managePermission(): Permission
    {
        return Permission::ManageClasses;
    }

    // Classes with students, enrollment history or results are kept so no history is lost
    public function delete(User $user, mixed $record): bool
    {
        return $this->canManage($user) && ! $record->isInUse();
    }
}
