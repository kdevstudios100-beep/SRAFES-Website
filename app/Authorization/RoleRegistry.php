<?php

namespace App\Authorization;

use App\Enum\Permission;
use App\Enum\UserRole;
use Spatie\Permission\Models\Permission as PermissionModel;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

/**
 * Single source of truth for which role holds which permission.
 * Teachers, students and parents get no admin panel access; their own
 * portals (with record-level ownership checks) arrive in later phases.
 * Staff run day-to-day enrollment; only principals and super admins open
 * or close academic sessions and bulk-import students.
 */
final class RoleRegistry
{
    public const GUARD = 'web';

    /**
     * @return array<Permission>
     */
    public static function permissionsFor(UserRole $role): array
    {
        return match ($role) {
            UserRole::SuperAdmin => Permission::cases(),
            UserRole::Principal => array_values(array_filter(
                Permission::cases(),
                fn (Permission $permission) => $permission !== Permission::ManageUsers,
            )),
            UserRole::Staff => [
                Permission::AccessAdminPanel,
                Permission::ManageSiteContent,
                Permission::ViewStudents,
                Permission::ManageStudents,
                Permission::ViewStaff,
                Permission::ManageClasses,
                Permission::ManageEnrollments,
                Permission::ViewExaminationResults,
                Permission::ManageCareers,
                Permission::MarkAttendance,
                Permission::ManageAttendance,
            ],
            // Teachers use the separate /teacher panel, which only shows sections they are assigned to
            UserRole::Teacher => [
                Permission::ViewStudents,
                Permission::ViewExaminationResults,
                Permission::AccessTeacherPanel,
                Permission::MarkAttendance,
            ],
            UserRole::Student, UserRole::Parent => [],
        };
    }

    /**
     * Create any missing permissions and roles, and make each role's
     * permissions match the map above exactly. Safe to run repeatedly.
     */
    public static function sync(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        foreach (Permission::cases() as $permission) {
            PermissionModel::findOrCreate($permission->value, self::GUARD);
        }

        foreach (UserRole::cases() as $role) {
            Role::findOrCreate($role->value, self::GUARD)->syncPermissions(
                array_map(fn (Permission $permission) => $permission->value, self::permissionsFor($role)),
            );
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}
