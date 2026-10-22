<?php

namespace App\Support\Backup;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use PDO;
use RuntimeException;
use ZipArchive;

/**
 * Creates, verifies and restores zip backups of the SQLite database and
 * uploaded files. See config/srafes.php and docs/OPERATIONS.md.
 */
class BackupManager
{
    private const DATABASE_ENTRY = 'database/database.sqlite';

    private const MANIFEST_ENTRY = 'manifest.json';

    public function __construct(private readonly array $config) {}

    public static function fromConfig(): self
    {
        return new self(config('srafes.backup'));
    }

    /**
     * @return string Absolute path of the new backup zip
     */
    public function create(): string
    {
        $this->ensureSqlite();

        File::ensureDirectoryExists($this->config['path']);

        $zipPath = $this->config['path'].DIRECTORY_SEPARATOR.'srafes-backup-'.now()->format('Ymd-His-v').'.zip';

        // VACUUM INTO writes a consistent copy even while the site is in use
        $databaseCopy = $this->tempPath('srafes-db-');
        DB::statement('VACUUM INTO ?', [$databaseCopy]);

        $manifest = [
            'format' => 1,
            'created_at' => now()->toIso8601String(),
            'app' => config('app.name'),
            'database_driver' => 'sqlite',
            'tables' => $this->rowCounts($databaseCopy),
            'files' => [self::DATABASE_ENTRY => hash_file('sha256', $databaseCopy)],
        ];

        $zip = new ZipArchive;

        if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            throw new RuntimeException("Could not create backup file [{$zipPath}].");
        }

        $zip->addFile($databaseCopy, self::DATABASE_ENTRY);

        foreach ($this->config['directories'] as $label => $directory) {
            if (! is_dir($directory)) {
                continue;
            }

            foreach (File::allFiles($directory, true) as $file) {
                $relativePath = str_replace('\\', '/', $file->getRelativePathname());

                if ($this->isExcluded($relativePath)) {
                    continue;
                }

                $entry = "files/{$label}/{$relativePath}";
                $zip->addFile($file->getRealPath(), $entry);
                $manifest['files'][$entry] = hash_file('sha256', $file->getRealPath());
            }
        }

        $zip->addFromString(self::MANIFEST_ENTRY, json_encode($manifest, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

        if (! $zip->close()) {
            throw new RuntimeException("Could not write backup file [{$zipPath}].");
        }

        File::delete($databaseCopy);

        return $zipPath;
    }

    /**
     * @return array{ok: bool, errors: array<string>, files: int, tables: array<string, int>, created_at: ?string}
     */
    public function verify(string $zipPath): array
    {
        $errors = [];
        $zip = new ZipArchive;

        if (! is_file($zipPath) || $zip->open($zipPath, ZipArchive::RDONLY) !== true) {
            return ['ok' => false, 'errors' => ["Cannot open [{$zipPath}]."], 'files' => 0, 'tables' => [], 'created_at' => null];
        }

        $manifest = json_decode((string) $zip->getFromName(self::MANIFEST_ENTRY), true);

        if (! is_array($manifest) || ! isset($manifest['files'], $manifest['tables'])) {
            $zip->close();

            return ['ok' => false, 'errors' => ['Manifest is missing or unreadable.'], 'files' => 0, 'tables' => [], 'created_at' => null];
        }

        foreach ($manifest['files'] as $entry => $expectedHash) {
            $stream = $zip->getStream($entry);

            if ($stream === false) {
                $errors[] = "Missing file: {$entry}";

                continue;
            }

            $context = hash_init('sha256');
            hash_update_stream($context, $stream);
            fclose($stream);

            if (! hash_equals($expectedHash, hash_final($context))) {
                $errors[] = "Checksum mismatch: {$entry}";
            }
        }

        $databaseCopy = $this->tempPath('srafes-verify-');
        $tables = [];

        if (file_put_contents($databaseCopy, (string) $zip->getFromName(self::DATABASE_ENTRY)) === false) {
            $errors[] = 'Could not extract the database.';
        } else {
            $pdo = new PDO('sqlite:'.$databaseCopy);
            $integrity = $pdo->query('PRAGMA integrity_check')->fetchColumn();
            $pdo = null;

            if ($integrity !== 'ok') {
                $errors[] = "Database integrity check failed: {$integrity}";
            }

            $tables = $this->rowCounts($databaseCopy);

            if ($tables !== $manifest['tables']) {
                $errors[] = 'Database row counts differ from the manifest.';
            }
        }

        File::delete($databaseCopy);
        $zip->close();

        return [
            'ok' => $errors === [],
            'errors' => $errors,
            'files' => count($manifest['files']),
            'tables' => $tables,
            'created_at' => $manifest['created_at'] ?? null,
        ];
    }

    /**
     * Unpack a verified backup into an empty folder without touching the live site.
     */
    public function extractTo(string $zipPath, string $targetDirectory): void
    {
        $this->assertVerified($zipPath);

        if (is_dir($targetDirectory) && File::files($targetDirectory) + File::directories($targetDirectory) !== []) {
            throw new RuntimeException("Target folder [{$targetDirectory}] must be empty.");
        }

        File::ensureDirectoryExists($targetDirectory);

        $zip = new ZipArchive;
        $zip->open($zipPath, ZipArchive::RDONLY);
        $zip->extractTo($targetDirectory);
        $zip->close();
    }

    /**
     * Replace the live database and uploaded files with a verified backup.
     * A safety backup of the current state is taken first, and the replaced
     * directories are kept as "<dir>.pre-restore-<timestamp>".
     *
     * @return array{safety_backup: string, kept_directories: array<string>}
     */
    public function restore(string $zipPath): array
    {
        $this->ensureSqlite();
        $this->assertVerified($zipPath);

        $safetyBackup = $this->create();
        $suffix = '.pre-restore-'.now()->format('Ymd-His');

        $zip = new ZipArchive;
        $zip->open($zipPath, ZipArchive::RDONLY);

        $databasePath = config('database.connections.'.config('database.default').'.database');
        $restoredDatabase = $this->tempPath('srafes-restore-');
        file_put_contents($restoredDatabase, (string) $zip->getFromName(self::DATABASE_ENTRY));

        DB::disconnect();
        File::copy($databasePath, $databasePath.$suffix);
        File::copy($restoredDatabase, $databasePath);
        File::delete([$restoredDatabase, $databasePath.'-wal', $databasePath.'-shm']);

        $keptDirectories = [$databasePath.$suffix];

        foreach ($this->config['directories'] as $label => $directory) {
            if (is_dir($directory)) {
                File::moveDirectory($directory, $directory.$suffix);
                $keptDirectories[] = $directory.$suffix;
            }

            File::ensureDirectoryExists($directory);
            $this->extractPrefix($zip, "files/{$label}/", $directory);
        }

        $zip->close();

        return ['safety_backup' => $safetyBackup, 'kept_directories' => $keptDirectories];
    }

    /**
     * Copy the backup to the off-machine location and drop old local backups.
     *
     * @return array{copied_to: ?string, pruned: array<string>}
     */
    public function distributeAndPrune(string $zipPath): array
    {
        $copiedTo = null;

        if (filled($this->config['copy_to'])) {
            File::ensureDirectoryExists($this->config['copy_to']);
            $copiedTo = $this->config['copy_to'].DIRECTORY_SEPARATOR.basename($zipPath);
            File::copy($zipPath, $copiedTo);
        }

        $pruned = collect($this->list())
            ->slice(max(1, $this->config['keep']))
            ->each(fn (string $path) => File::delete($path))
            ->values()
            ->all();

        return ['copied_to' => $copiedTo, 'pruned' => $pruned];
    }

    /**
     * @return array<string> Backup zips in `path`, newest first
     */
    public function list(): array
    {
        return collect(File::glob($this->config['path'].DIRECTORY_SEPARATOR.'srafes-backup-*.zip'))
            ->sortDesc()
            ->values()
            ->all();
    }

    private function assertVerified(string $zipPath): void
    {
        $result = $this->verify($zipPath);

        if (! $result['ok']) {
            throw new RuntimeException('Backup failed verification: '.implode('; ', $result['errors']));
        }
    }

    private function extractPrefix(ZipArchive $zip, string $prefix, string $targetDirectory): void
    {
        for ($i = 0; $i < $zip->numFiles; $i++) {
            $entry = $zip->getNameIndex($i);

            if (! str_starts_with($entry, $prefix) || str_ends_with($entry, '/')) {
                continue;
            }

            $relativePath = substr($entry, strlen($prefix));

            // Never write outside the target directory
            if (str_contains($relativePath, '..') || str_starts_with($relativePath, '/')) {
                throw new RuntimeException("Unsafe path in backup: {$entry}");
            }

            $target = $targetDirectory.DIRECTORY_SEPARATOR.$relativePath;
            File::ensureDirectoryExists(dirname($target));

            $source = $zip->getStream($entry);
            $destination = fopen($target, 'wb');
            stream_copy_to_stream($source, $destination);
            fclose($source);
            fclose($destination);
        }
    }

    private function isExcluded(string $relativePath): bool
    {
        foreach ($this->config['exclude'] as $excluded) {
            if ($relativePath === $excluded || str_starts_with($relativePath, $excluded.'/')) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return array<string, int>
     */
    private function rowCounts(string $sqlitePath): array
    {
        $pdo = new PDO('sqlite:'.$sqlitePath);

        $counts = collect($pdo->query("SELECT name FROM sqlite_master WHERE type = 'table' AND name NOT LIKE 'sqlite_%' ORDER BY name")->fetchAll(PDO::FETCH_COLUMN))
            ->mapWithKeys(fn (string $table) => [$table => (int) $pdo->query('SELECT COUNT(*) FROM "'.str_replace('"', '""', $table).'"')->fetchColumn()])
            ->all();

        $pdo = null;

        return $counts;
    }

    private function ensureSqlite(): void
    {
        $driver = DB::connection()->getDriverName();

        if ($driver !== 'sqlite') {
            throw new RuntimeException("srafes backups currently support SQLite only (current driver: {$driver}). Use the hosting provider's database backups for other drivers.");
        }
    }

    private function tempPath(string $prefix): string
    {
        // VACUUM INTO refuses to overwrite, so return a path that does not exist yet
        return sys_get_temp_dir().DIRECTORY_SEPARATOR.$prefix.bin2hex(random_bytes(8)).'.sqlite';
    }
}
