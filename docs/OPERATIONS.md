# SRAFES operations

Day-to-day commands for running, backing up and recovering the site. All
commands run from the project folder with PHP 8.4 or newer
(locally: `C:\tools\php84\php.exe`; XAMPP's PHP 8.2 is too old).

## Tests

```
php artisan test                          # in-memory SQLite (phpunit.xml)
php vendor/bin/pest -c phpunit.mysql.xml  # MySQL/MariaDB, database srafes_testing
```

Tests can never touch school data: `tests/TestCase.php` aborts unless the
database is in-memory SQLite or a MySQL database whose name ends in `_testing`,
and aborts if the config is cached. If you see "Tests aborted", run
`php artisan config:clear`. The MySQL backup tests are skipped when no server
is running on 127.0.0.1:3306.

## Roles and accounts

| Command | Purpose |
| --- | --- |
| `php artisan srafes:sync-roles` | Apply `app/Authorization/RoleRegistry.php` to the database. Run after changing roles or permissions. |
| `php artisan srafes:reset-password <email> [--new-email=...]` | Set a password typed in the terminal (e-mail password reset is not configured yet). |

Only Super Admins manage users. Nobody can delete, deactivate or change the
roles of their own account, so the last administrator cannot lock everyone out.

## Academic records

Admin panel, **Academics** group:

| Screen | Who can change it | What it holds |
| --- | --- | --- |
| Academic Sessions | Principal, Super Admin | School years (e.g. 2026-2027). Exactly one is *current*; it is the default for new enrollments and imports. Dates may not overlap. |
| Classes | Staff and above | Each class has a **position** (1 = Class 1) used by promotion, plus its **sections** and **subjects** (whole class, or one section only). |
| Subjects | Staff and above | The subject list. Deactivate a subject instead of deleting it once it is in use. |
| Students | Staff and above | Search, filter (session, class, section, status, scholarship), student details with enrollment history and results. |
| Import students | Principal, Super Admin | CSV import (see below). |

**Placements.** A student's class is changed only through the *Placement* menu
on the student page, or the *Promote / move to session* bulk action on the
student list. Each change closes the old enrollment (kept as history with its
end date and status) and opens a new one:

- *Transfer* - another class or section in the same session.
- *Promote* - into a later session: the next class (promoted) or the same class (retained).
- *Mark as graduated* / *Mark as left school* - the student becomes inactive.
- *Enroll / Re-enroll* - for a student with no active placement; makes them active.

Examination results are never changed by any of these. Classes, sections and
sessions that hold students, history or results cannot be deleted, and
enrollment history cannot be deleted from the panel.

**End of year.** Create the next session, filter the student list by class,
select all, choose *Promote / move to session* -> *Next class*. Students in the
final class are listed as skipped; mark them graduated. Then make the new
session current.

**CSV import.** Download the template from the import page. Required columns:
`admission_number`, `first_name`, `class`; optional: `last_name`, `surname`,
`father_name`, `date_of_birth` (YYYY-MM-DD or DD/MM/YYYY), `section`,
`roll_number`, `has_scholarship` (yes/no). Classes and sections must exist
first. *Check file* writes nothing and lists every row as ready, duplicate or
error. Duplicates (an existing or repeated admission number, or the same name,
father's name and birth date as another student) are always skipped. *Import*
re-checks and saves all ready rows in one transaction. The uploaded file is
kept privately only until the import finishes. Limit: 2,000 rows, 2 MB.

`ACADEMIC_SESSION_START_MONTH` in `.env` (default 4 = April) sets the dates
suggested for new sessions.

## Attendance

**Setup.**
1. Create each teacher a user account (Users → Teacher role).
2. Under Academics → Teacher Assignments, make one teacher the **class teacher** of each class or section for the session (leave Subject empty). Subject teachers can be recorded too; they don't take attendance.

**Teachers** sign in at `/teacher/login` (not `/admin`). They see only the classes they are class teacher of:

- **Daily overview:** which of their registers are taken today.
- **Take attendance:** pick a date and class, *Mark all present*, change the exceptions, save. Every student must be marked. Teachers can mark or correct up to `ATTENDANCE_TEACHER_EDIT_DAYS` days back (default 7); never future dates.
- **Attendance report:** totals and percentage per student for a date range, with a CSV download.

**Staff, Principal and Super Admin** have the same three pages in the admin panel (Attendance group) for every class, can correct any date in a session, and see each student's attendance on their student page.

Attendance % = (present + late) ÷ (days marked − leave). Approved leave neither counts for nor against a student. Students below 75% are highlighted. Days with no register (holidays, weekends) simply aren't counted.

Attendance belongs to the student's placement on that day. After a transfer, earlier days stay with the old class and the transfer day itself belongs to the new class. Promotion and leaving never remove attendance.

## Exams

**Setup** (Staff, Principal, Super Admin; admin panel → **Exams**):
1. Give each class its subjects (Academics → Classes → Subjects). A subject set for one section only is sat by that section only.
2. Create the exam (e.g. *Mid-term*) in its session. On the exam page, *Add a class's subjects* creates one paper per subject for a class; or add papers one by one. Each paper has maximum marks and pass marks (33% suggested, editable).
3. Teacher assignments (Academics → Teacher Assignments) decide who marks what: a **subject teacher** enters their subject; a **class teacher** enters every paper of their class or section and sees its result sheet. An assignment without a section covers every section of the class.

**Entering marks** (`/teacher/enter-marks`, or Exams → Enter marks in the admin panel): pick the exam, class and subject, type whole marks or tick *Absent*, save. Sheets can be saved part-filled; a blank mark is "not entered yet".

**Results** (Exam results page): every paper, total, percentage, grade, result and position per student, with a CSV download, *Print result sheet* (the whole class on landscape pages, with subject pass counts, highest marks and the pass rate) and *Print result cards* (one card per student per page). Both open in a new tab; use the browser's print / save as PDF.

A teacher can hold several assignments in a session, e.g. class teacher of Class 1 - A and Maths teacher of Class 3. *Enter marks* lists all of them; result sheets and printing are for the class-teacher class only.

- A student **passes** by passing every paper. Absent counts as 0 and a fail.
- **Grade** from the overall percentage: A1 80%+, A 70-79, B 60-69, C 50-59, D 40-49, E 33-39, F below 33. Failing students are graded F. The scale is in `config/srafes.php` (`exams.grades`).
- **Position** ranks passing students in the class or section by total marks. Equal totals share a position and the next total takes the next one (1, 2, 2, 3), so there is always a 1st, 2nd and 3rd position holder.
- A student missing any mark is *Incomplete*: no grade, result or position.

**Finishing an exam** (Exams list → row menu):
1. *Lock* when marking is done. Nobody can change marks while locked; *Unlock* reopens it.
2. *Publish results* copies each student's total, grade and result to Examination Results, so families can look them up on the public results page by admission number. Every mark must be entered first.
3. *Unpublish results* removes them again (marks are kept); unpublish before unlocking to correct a mark, then lock and publish again. Results entered by hand under Examination Results are never touched.

Exams and papers that hold marks cannot be deleted. Marks belong to the student's placement, like attendance, so transfers and promotion never move or remove them.

## Backups

Each backup is one zip in `BACKUP_PATH` (default `storage/app/backups`) holding a
consistent copy of the database, all uploaded files, and a manifest with a
SHA-256 checksum per file and a row count per table. Settings live in
`config/srafes.php`.

| Command | Purpose |
| --- | --- |
| `php artisan srafes:backup` | Create and verify a backup, copy it to `BACKUP_COPY_PATH`, keep the newest `BACKUP_KEEP`. |
| `php artisan srafes:backup:verify [file]` | Check checksums, database integrity and row counts (newest backup by default). |
| `php artisan srafes:backup:restore <file> --to=<empty folder>` | Restore drill: unpack a verified backup without touching the live site. |
| `php artisan srafes:backup:restore <file>` | Replace the live database and uploaded files (asks first; `--force` skips the question). |

Always keep `BACKUP_COPY_PATH` on a different drive or synced folder from the site.

### Schedule

`routes/console.php` runs a backup at 01:30 and verifies it at 02:30. This needs
the scheduler to run every minute:

- Linux / cPanel cron: `* * * * * cd /path/to/site && php artisan schedule:run >> /dev/null 2>&1`
- Windows (local): a Task Scheduler task running `php artisan schedule:run` in
  the project folder every minute.

### Restoring

1. Run a drill first and check the extracted data:
   `php artisan srafes:backup:restore <file> --to=D:/restore-drill`
2. Put the site in maintenance mode so nobody writes during the restore:
   `php artisan down`
3. `php artisan srafes:backup:restore <file>`. A safety backup of the current
   state is taken first, and the replaced database file and upload folders are
   kept beside the originals as `*.pre-restore-<timestamp>`.
4. `php artisan up`, then check the site and the admin panel.

A backup can only be restored onto the same database driver it was taken from.
To move data between SQLite and MySQL, use `srafes:copy-database` below.
Only files listed with a checksum in the manifest are restored.

## Moving to MySQL

```
php artisan migrate --database=mysql
php artisan srafes:copy-database sqlite mysql
```

The copy checks row counts per table. Then set `DB_CONNECTION=mysql` in `.env`.

## Uploaded files

Applicant documents (CVs, cover letters) and examination result files are on the
private `local` disk (`storage/app/private`). They are reachable only through
the admin panel (5-minute signed links) or the public result lookup for a
matching admission number. `php artisan srafes:quarantine-orphans --dry-run`
lists publicly reachable files that no record references.
