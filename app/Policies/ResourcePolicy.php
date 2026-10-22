<?php

namespace App\Policies;

use App\Enum\Permission;
use App\Models\User;

/**
 * Base policy for admin-managed records: a "view" permission grants read
 * access, a "manage" permission grants everything. Inactive users are
 * denied all abilities. Laravel discovers subclasses by model name.
 */
abstract class ResourcePolicy
{
    abstract protected function viewPermission(): Permission;

    abstract protected function managePermission(): Permission;

    protected function canView(User $user): bool
    {
        return $this->allows($user, $this->viewPermission()) || $this->canManage($user);
    }

    protected function canManage(User $user): bool
    {
        return $this->allows($user, $this->managePermission());
    }

    protected function allows(User $user, Permission $permission): bool
    {
        return $user->is_active && $user->checkPermissionTo($permission->value);
    }

    public function viewAny(User $user): bool
    {
        return $this->canView($user);
    }

    public function view(User $user, mixed $record): bool
    {
        return $this->canView($user);
    }

    public function create(User $user): bool
    {
        return $this->canManage($user);
    }

    public function update(User $user, mixed $record): bool
    {
        return $this->canManage($user);
    }

    public function delete(User $user, mixed $record): bool
    {
        return $this->canManage($user);
    }

    public function deleteAny(User $user): bool
    {
        return $this->canManage($user);
    }

    public function restore(User $user, mixed $record): bool
    {
        return $this->canManage($user);
    }

    public function restoreAny(User $user): bool
    {
        return $this->canManage($user);
    }

    public function forceDelete(User $user, mixed $record): bool
    {
        return $this->canManage($user);
    }

    public function forceDeleteAny(User $user): bool
    {
        return $this->canManage($user);
    }

    public function replicate(User $user, mixed $record): bool
    {
        return $this->canManage($user);
    }

    public function reorder(User $user): bool
    {
        return $this->canManage($user);
    }
}
