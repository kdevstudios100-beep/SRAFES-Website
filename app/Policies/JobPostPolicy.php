<?php

namespace App\Policies;

use App\Enum\Permission;

class JobPostPolicy extends ResourcePolicy
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
