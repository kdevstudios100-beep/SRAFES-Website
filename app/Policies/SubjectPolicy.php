<?php

namespace App\Policies;

use App\Enum\Permission;

class SubjectPolicy extends ResourcePolicy
{
    protected function viewPermission(): Permission
    {
        return Permission::ViewStudents;
    }

    protected function managePermission(): Permission
    {
        return Permission::ManageClasses;
    }
}
