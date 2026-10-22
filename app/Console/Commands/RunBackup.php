<?php

namespace App\Console\Commands;

use App\Support\Backup\BackupManager;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Throwable;

class RunBackup extends Command
{
    protected $signature = 'srafes:backup';

    protected $description = 'Back up the database and uploaded files, verify the backup, copy it off-site and prune old ones';

    public function handle(): int
    {
        $backups = BackupManager::fromConfig();

        try {
            $path = $backups->create();
            $result = $backups->verify($path);

            if (! $result['ok']) {
                Log::error('Backup created but failed verification', ['path' => $path, 'errors' => $result['errors']]);
                $this->error('Backup failed verification: '.implode('; ', $result['errors']));

                return self::FAILURE;
            }

            $distribution = $backups->distributeAndPrune($path);
        } catch (Throwable $exception) {
            Log::error('Backup failed', ['exception' => $exception]);
            $this->error('Backup failed: '.$exception->getMessage());

            return self::FAILURE;
        }

        Log::info('Backup completed', ['path' => $path, 'files' => $result['files'], 'copied_to' => $distribution['copied_to']]);

        $this->info("Backup created and verified: {$path}");
        $this->line("Files: {$result['files']} | Students: ".($result['tables']['students'] ?? 0).' | Users: '.($result['tables']['users'] ?? 0));
        $this->line($distribution['copied_to'] ? "Off-site copy: {$distribution['copied_to']}" : 'No off-site copy: BACKUP_COPY_PATH is not set.');

        if ($distribution['pruned'] !== []) {
            $this->line('Pruned old backups: '.count($distribution['pruned']));
        }

        return self::SUCCESS;
    }
}
