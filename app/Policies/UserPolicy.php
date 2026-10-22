<?php

namespace App\Policies;

use App\Enum\Permission;
use App\Models\User;

class UserPolicy extends ResourcePolicy
{
    protected function viewPermission(): Permission
    {
        return Permission::ManageUsers;
    }

    protected function managePermission(): Permission
    {
        return Permission::ManageUsers;
    }

    // Nobody can delete their own account, so the last administrator can't lock everyone out
    public function delete(User $user, mixed $record): bool
    {
        return $this->canManage($user) && ! $user->is($record);
    }
}
