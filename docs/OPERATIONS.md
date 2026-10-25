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
