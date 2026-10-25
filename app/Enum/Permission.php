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
    case ManageAcademicSessions = 'manage_academic_sessions';
    case ManageEnrollments = 'manage_enrollments';
    case ImportStudents = 'import_students';
    case AccessTeacherPanel = 'access_teacher_panel';
    // Mark attendance for one's own class-teacher sections (teachers), within the edit window
    case MarkAttendance = 'mark_attendance';
    // Mark and review attendance for every section, on any date
    case ManageAttendance = 'manage_attendance';
    case ManageTeacherAssignments = 'manage_teacher_assignments';
    // Enter marks for papers one teaches, or every paper of one's class-teacher sections, while the exam is open
    case EnterMarks = 'enter_marks';
    // Set up exams and papers, enter any marks, lock exams and publish their results
    case ManageExams = 'manage_exams';
    case ViewExaminationResults = 'view_examination_results';
    case ManageExaminationResults = 'manage_examination_results';
    case ManageCareers = 'manage_careers';
}
