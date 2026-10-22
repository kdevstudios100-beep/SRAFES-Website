<?php

namespace App\Console\Commands;

use App\Support\Backup\BackupManager;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class VerifyBackup extends Command
{
    protected $signature = 'srafes:backup:verify {file? : Backup zip to check (defaults to the newest)}';

    protected $description = 'Check a backup zip: file checksums, database integrity and row counts';

    public function handle(): int
    {
        $backups = BackupManager::fromConfig();
        $path = $this->argument('file') ?? ($backups->list()[0] ?? null);

        if ($path === null) {
            $this->error('No backups found.');
            Log::error('Backup verification found no backups');

            return self::FAILURE;
        }

        $result = $backups->verify($path);

        if (! $result['ok']) {
            Log::error('Backup verification failed', ['path' => $path, 'errors' => $result['errors']]);
            $this->error("FAILED: {$path}");

            foreach ($result['errors'] as $error) {
                $this->line("  - {$error}");
            }

            return self::FAILURE;
        }

        $this->info("OK: {$path}");
        $this->line("Created: {$result['created_at']} | Files: {$result['files']}");
        $this->table(['Table', 'Rows'], collect($result['tables'])->map(fn (int $rows, string $table) => [$table, $rows])->values());

        return self::SUCCESS;
    }
}
