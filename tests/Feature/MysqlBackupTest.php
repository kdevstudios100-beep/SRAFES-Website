<?php

use App\Models\Student;
use App\Support\Backup\BackupManager;
use App\Support\Backup\MysqlDumper;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

/*
 * Runs the backup cycle against a real MySQL/MariaDB server, in a dedicated
 * database whose name must end in "_testing". Skipped when no server is
 * reachable (configure with TEST_MYSQL_HOST / _PORT / _USERNAME / _PASSWORD).
 */
beforeEach(function () {
    $host = env('TEST_MYSQL_HOST', '127.0.0.1');
    $port = env('TEST_MYSQL_PORT', '3306');
    $username = env('TEST_MYSQL_USERNAME', 'root');
    $password = env('TEST_MYSQL_PASSWORD', '');
    $database = 'srafes_backup_testing';

    try {
        $server = new PDO("mysql:host={$host};port={$port}", $username, $password, [PDO::ATTR_TIMEOUT => 2]);
        $server->exec("CREATE DATABASE IF NOT EXISTS `{$database}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    } catch (PDOException) {
        $this->markTestSkipped('No MySQL/MariaDB server available for backup tests.');
    }

    $this->originalConnection = config('database.default');
    $this->sandbox = sys_get_temp_dir().DIRECTORY_SEPARATOR.'srafes-mysql-backup-test-'.bin2hex(random_bytes(6));
    File::ensureDirectoryExists($this->sandbox.'/uploads');
    File::put($this->sandbox.'/uploads/report.pdf', 'original report');

    config([
        'database.connections.mysql_backup_test' => array_merge(config('database.connections.mysql'), [
            'host' => $host, 'port' => $port, 'username' => $username, 'password' => $password, 'database' => $database,
        ]),
        'database.default' => 'mysql_backup_test',
        'srafes.backup' => [
            'path' => $this->sandbox.'/backups',
            'copy_to' => null,
            'keep' => 5,
            'directories' => ['uploads' => $this->sandbox.'/uploads'],
            'exclude' => [],
        ],
    ]);

    expect($database)->toEndWith('_testing');
    Artisan::call('migrate:fresh', ['--database' => 'mysql_backup_test', '--force' => true]);
});

afterEach(function () {
    if (isset($this->originalConnection)) {
        config(['database.default' => $this->originalConnection]);
        DB::purge('mysql_backup_test');
        File::deleteDirectory($this->sandbox);
    }
});

it('backs up, verifies and restores a MySQL database', function () {
    Student::factory()->count(3)->create(['father_name' => "O'Brien\nline two"]);

    $this->artisan('srafes:backup')->assertSuccessful();

    $backups = BackupManager::fromConfig();
    $path = $backups->list()[0];
    $result = $backups->verify($path);

    expect($result['ok'])->toBeTrue()
        ->and($result['database_driver'])->toBe('mysql')
        ->and($result['tables']['students'])->toBe(3);

    Student::query()->delete();
    File::put($this->sandbox.'/uploads/report.pdf', 'changed');

    $this->artisan('srafes:backup:restore', ['file' => $path, '--force' => true])->assertSuccessful();

    DB::purge('mysql_backup_test');
    expect(Student::count())->toBe(3)
        ->and(Student::first()->father_name)->toBe("O'Brien\nline two")
        ->and(File::get($this->sandbox.'/uploads/report.pdf'))->toBe('original report');
});

it('detects a tampered MySQL dump', function () {
    Student::factory()->count(2)->create();
    $path = BackupManager::fromConfig()->create();

    $zip = new ZipArchive;
    $zip->open($path);
    $dump = $zip->getFromName('database/database.sql');
    $zip->addFromString('database/database.sql', preg_replace('/^INSERT INTO `students`.*\n/m', '', $dump, 1));
    $zip->close();

    expect(BackupManager::fromConfig()->verify($path)['ok'])->toBeFalse();
});

it('counts rows in a dump independently of the manifest', function () {
    Student::factory()->count(4)->create();

    $file = $this->sandbox.'/dump.sql';
    $written = (new MysqlDumper(DB::connection()))->dump($file);

    expect(MysqlDumper::countRows($file))->toBe($written)
        ->and($written['students'])->toBe(4);
});
