<?php

namespace App\Policies;

use App\Enum\Permission;

class JobApplicationPolicy extends ResourcePolicy
{
    protected function viewPermission(): Permission
    {
        return Permission::ManageCareers;
    }

    protected function managePermission(): Permission
    {
        return Permission::ManageCareers;
    }
}
