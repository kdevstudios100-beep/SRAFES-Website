<?php

namespace App\Enum;

/**
 * Every permission in the system. Roles are mapped to these in
 * App\Authorization\RoleRegistry; run `php artisan srafes:sync-roles`
 * after changing either file.
 */
enum Permission: string
{
    case AccessAdminPanel = 'access_admin_panel';
    case ManageUsers = 'manage_users';
    case ManageSiteContent = 'manage_site_content';
    case ViewStudents = 'view_students';
    case ManageStudents = 'manage_students';
    case ViewStaff = 'view_staff';
    case ManageStaff = 'manage_staff';
    case ManageClasses = 'manage_classes';
    case ViewExaminationResults = 'view_examination_results';
    case ManageExaminationResults = 'manage_examination_results';
    case ManageCareers = 'manage_careers';
}
