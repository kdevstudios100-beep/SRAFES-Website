<?php

namespace App\Console\Commands;

use App\Authorization\RoleRegistry;
use App\Enum\UserRole;
use Illuminate\Console\Command;
use Spatie\Permission\Models\Role;

class SyncRoles extends Command
{
    protected $signature = 'srafes:sync-roles';

    protected $description = 'Create or update roles and permissions from App\Authorization\RoleRegistry';

    public function handle(): int
    {
        RoleRegistry::sync();

        $this->table(
            ['Role', 'Permissions', 'Users'],
            collect(UserRole::cases())->map(fn (UserRole $role) => [
                $role->getLabel(),
                count(RoleRegistry::permissionsFor($role)),
                Role::findByName($role->value, RoleRegistry::GUARD)->users()->count(),
            ]),
        );

        $this->info('Roles and permissions are in sync.');

        return self::SUCCESS;
    }
}
