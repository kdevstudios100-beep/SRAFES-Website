<?php

namespace App\Policies;

use App\Enum\Permission;

class SiteSettingPolicy extends ResourcePolicy
{
    protected function viewPermission(): Permission
    {
        return Permission::ManageSiteContent;
    }

    protected function managePermission(): Permission
    {
        return Permission::ManageSiteContent;
    }
}
