<?php

namespace App\Policies;

use App\Enum\Permission;

class ExaminationResultPolicy extends ResourcePolicy
{
    protected function viewPermission(): Permission
    {
        return Permission::ViewExaminationResults;
    }

    protected function managePermission(): Permission
    {
        return Permission::ManageExaminationResults;
    }
}
