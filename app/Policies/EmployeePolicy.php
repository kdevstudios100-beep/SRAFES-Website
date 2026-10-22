<?php

namespace App\Policies;

use App\Enum\Permission;

class EmployeePolicy extends ResourcePolicy
{
    protected function viewPermission(): Permission
    {
        return Permission::ViewStaff;
    }

    protected function managePermission(): Permission
    {
        return Permission::ManageStaff;
    }
}
