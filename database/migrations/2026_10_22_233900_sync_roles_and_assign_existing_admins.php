<?php

use App\Authorization\RoleRegistry;
use App\Enum\UserRole;
use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Role;

return new class extends Migration
{
    /**
     * Before this phase every user had full admin access. To avoid locking
     * anyone out, users who existed before roles were introduced become
     * Super Admins; review them in the Users screen afterwards.
     */
    public function up(): void
    {
        RoleRegistry::sync();

        $superAdmin = Role::findByName(UserRole::SuperAdmin->value, RoleRegistry::GUARD);

        if ($superAdmin->users()->doesntExist()) {
            User::query()->each(fn (User $user) => $user->assignRole($superAdmin));
        }
    }

    public function down(): void
    {
        // Role and permission tables are dropped by the create_permission_tables migration
    }
};
