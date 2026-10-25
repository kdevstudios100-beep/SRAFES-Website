<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Backups
    |--------------------------------------------------------------------------
    |
    | Used by `srafes:backup`, `srafes:backup:verify` and `srafes:backup:restore`.
    | Each backup is one zip holding a consistent copy of the database, every
    | uploaded file listed in `directories`, and a manifest with checksums.
    |
    | Keep at least one copy off this machine: set BACKUP_COPY_PATH to a second
    | drive, a synced folder, or a mounted network share.
    |
    */

    'backup' => [
        // `?:` so a blank BACKUP_PATH= in .env still falls back to the default
        'path' => env('BACKUP_PATH') ?: storage_path('app/backups'),

        'copy_to' => env('BACKUP_COPY_PATH'),

        // Number of most recent backups to keep in `path`; older ones are pruned
        'keep' => (int) env('BACKUP_KEEP', 14),

        // label => absolute directory; labels name the folders inside the zip
        'directories' => [
            'storage-private' => storage_path('app/private'),
            'storage-public' => storage_path('app/public'),
            'public-filament' => public_path('filament'),
            'public-images' => public_path('images'),
        ],

        // Relative paths (inside any directory above) that are never backed up
        'exclude' => [
            'livewire-tmp',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Academic records
    |--------------------------------------------------------------------------
    |
    | session_start_month: month (1-12) a school year begins, used to suggest
    | dates and names ("2026-2027") for new academic sessions. Each session's
    | dates can still be edited in the admin panel.
    |
    */

    'academics' => [
        'session_start_month' => (int) env('ACADEMIC_SESSION_START_MONTH', 4),

        // Largest student CSV accepted by the importer
        'import_max_rows' => 2000,
    ],

    /*
    |--------------------------------------------------------------------------
    | Attendance
    |--------------------------------------------------------------------------
    |
    | teacher_edit_days: how many days back a teacher may mark or correct
    | attendance (0 = today only). Staff with "manage attendance" can always
    | correct any date. low_attendance_percent: reports highlight students
    | below this.
    |
    */

    'attendance' => [
        'teacher_edit_days' => (int) env('ATTENDANCE_TEACHER_EDIT_DAYS', 7),
        'low_attendance_percent' => 75,
    ],

    /*
    |--------------------------------------------------------------------------
    | Exams
    |--------------------------------------------------------------------------
    |
    | grades: lowest percentage for each grade, highest grade first; anything
    | below the last one is "F". pass_percent: the pass mark suggested for new
    | papers (each paper's pass mark can still be edited). A student passes an
    | exam only by passing every paper.
    |
    */

    'exams' => [
        // Printed on result sheets and result cards, next to public/images/logo-emblem.png
        'school_name' => env('SCHOOL_NAME', 'Shaheed Rajib Anmol Free Education System Higher Secondary School'),

        // Positions up to this one are highlighted as position holders
        'position_holders' => 3,

        'grades' => [
            'A1' => 80,
            'A' => 70,
            'B' => 60,
            'C' => 50,
            'D' => 40,
            'E' => 33,
        ],
        'fail_grade' => 'F',
        'pass_percent' => 33,
    ],

];
