<?php

namespace App\Console\Commands;

use App\Support\Backup\BackupManager;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class RestoreBackup extends Command
{
    protected $signature = 'srafes:backup:restore
        {file : Backup zip to restore}
        {--to= : Extract into this empty folder instead of replacing the live site (restore drill)}
        {--force : Replace the live database and files without asking}';

    protected $description = 'Restore a verified backup, or extract it to a folder for a restore drill';

    public function handle(): int
    {
        $backups = BackupManager::fromConfig();
        $file = $this->argument('file');

        if ($target = $this->option('to')) {
            $backups->extractTo($file, $target);
            $this->info("Backup verified and extracted to {$target}. The live site was not changed.");

            return self::SUCCESS;
        }

        if (! $this->option('force') && ! $this->confirm('This replaces the LIVE database and uploaded files. A safety backup is taken first. Continue?')) {
            $this->warn('Restore cancelled.');

            return self::FAILURE;
        }

        $result = $backups->restore($file);

        Log::warning('Live restore completed', ['file' => $file] + $result);

        $this->info("Restored from {$file}");
        $this->line("Safety backup of the previous state: {$result['safety_backup']}");
        $this->line('Replaced data kept at:');

        foreach ($result['kept_directories'] as $path) {
            $this->line("  {$path}");
        }

        $this->line('Run "php artisan optimize:clear" and check the site.');

        return self::SUCCESS;
    }
}
