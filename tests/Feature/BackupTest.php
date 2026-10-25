<?php

use App\Models\Student;
use App\Support\Backup\BackupManager;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

/*
 * Backups need a file-based database (VACUUM INTO can't run inside the
 * RefreshDatabase transaction), so each test switches the default connection
 * to a throwaway SQLite file and points every backup path at a temp folder.
 */
beforeEach(function () {
    $this->originalConnection = config('database.default');
    $this->sandbox = sys_get_temp_dir().DIRECTORY_SEPARATOR.'srafes-backup-test-'.bin2hex(random_bytes(6));
    File::ensureDirectoryExists($this->sandbox.'/uploads/1');
    File::put($this->sandbox.'/uploads/1/photo.jpg', 'original photo');
    File::ensureDirectoryExists($this->sandbox.'/uploads/livewire-tmp');
    File::put($this->sandbox.'/uploads/livewire-tmp/skip.tmp', 'temporary');
    touch($this->sandbox.'/school.sqlite');

    config([
        'database.connections.backup_test' => [
            'driver' => 'sqlite',
            'database' => $this->sandbox.'/school.sqlite',
            'prefix' => '',
            'foreign_key_constraints' => true,
        ],
        'database.default' => 'backup_test',
        'srafes.backup' => [
            'path' => $this->sandbox.'/backups',
            'copy_to' => $this->sandbox.'/offsite',
            'keep' => 2,
            'directories' => ['uploads' => $this->sandbox.'/uploads'],
            'exclude' => ['livewire-tmp'],
        ],
    ]);

    Artisan::call('migrate', ['--database' => 'backup_test', '--force' => true]);
});

afterEach(function () {
    // RefreshDatabase rolls back the *current* default connection on teardown, so point it back
    config(['database.default' => $this->originalConnection]);
    DB::purge('backup_test');
    File::deleteDirectory($this->sandbox);
});

it('creates a verified backup with an off-site copy', function () {
    Student::factory()->count(3)->create();

    $this->artisan('srafes:backup')->assertSuccessful();

    $backups = BackupManager::fromConfig();
    $path = $backups->list()[0];
    $result = $backups->verify($path);

    expect($result['ok'])->toBeTrue()
        ->and($result['tables']['students'])->toBe(3)
        ->and(File::exists($this->sandbox.'/offsite/'.basename($path)))->toBeTrue();

    $zip = new ZipArchive;
    $zip->open($path);
    expect($zip->locateName('files/uploads/1/photo.jpg'))->not->toBeFalse()
        ->and($zip->locateName('files/uploads/livewire-tmp/skip.tmp'))->toBeFalse();
    $zip->close();
});

it('detects a tampered backup', function () {
    $path = BackupManager::fromConfig()->create();

    $zip = new ZipArchive;
    $zip->open($path);
    $zip->addFromString('files/uploads/1/photo.jpg', 'tampered');
    $zip->close();

    $result = BackupManager::fromConfig()->verify($path);

    expect($result['ok'])->toBeFalse()
        ->and($result['errors'])->toContain('Checksum mismatch: files/uploads/1/photo.jpg');

    $this->artisan('srafes:backup:verify', ['file' => $path])->assertFailed();
});

it('extracts a backup for a restore drill without touching live data', function () {
    Student::factory()->count(2)->create();
    $path = BackupManager::fromConfig()->create();
    Student::query()->delete();

    $this->artisan('srafes:backup:restore', ['file' => $path, '--to' => $this->sandbox.'/drill'])->assertSuccessful();

    $drill = new PDO('sqlite:'.$this->sandbox.'/drill/database/database.sqlite');
    expect((int) $drill->query('select count(*) from students')->fetchColumn())->toBe(2)
        ->and(Student::count())->toBe(0);
    $drill = null;
});

it('restores the live database and files and keeps the replaced data', function () {
    Student::factory()->count(2)->create();
    $path = BackupManager::fromConfig()->create();

    Student::query()->delete();
    File::put($this->sandbox.'/uploads/1/photo.jpg', 'changed after backup');

    $this->artisan('srafes:backup:restore', ['file' => $path, '--force' => true])->assertSuccessful();

    DB::purge('backup_test');
    expect(Student::count())->toBe(2)
        ->and(File::get($this->sandbox.'/uploads/1/photo.jpg'))->toBe('original photo')
        ->and(File::glob($this->sandbox.'/uploads.pre-restore-*'))->toHaveCount(1)
        ->and(File::glob($this->sandbox.'/school.sqlite.pre-restore-*'))->toHaveCount(1);
});

it('keeps only the configured number of backups', function () {
    $this->artisan('srafes:backup')->assertSuccessful();
    $this->artisan('srafes:backup')->assertSuccessful();
    $this->artisan('srafes:backup')->assertSuccessful();

    expect(BackupManager::fromConfig()->list())->toHaveCount(2);
});

it('restores only files covered by the manifest checksums', function () {
    $path = BackupManager::fromConfig()->create();

    // A file slipped into the zip after the backup was made has no checksum, so verification can't vouch for it
    $zip = new ZipArchive;
    $zip->open($path);
    $zip->addFromString('files/uploads/planted.php', '<?php echo "not from the backup";');
    $zip->close();

    $this->artisan('srafes:backup:restore', ['file' => $path, '--force' => true])->assertSuccessful();

    expect(File::exists($this->sandbox.'/uploads/planted.php'))->toBeFalse()
        ->and(File::get($this->sandbox.'/uploads/1/photo.jpg'))->toBe('original photo');
});
