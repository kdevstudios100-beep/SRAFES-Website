<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

/**
 * Finds files on publicly served disks that no media record points to and
 * moves them to a private quarantine folder. Nothing is deleted; each run
 * writes a manifest so files can be moved back if needed.
 */
class QuarantineOrphanFiles extends Command
{
    protected $signature = 'srafes:quarantine-orphans {--dry-run : Only list the files that would be moved}';

    protected $description = 'Move publicly reachable files that no media record references into private quarantine';

    /** Disks whose files are reachable by URL without logging in */
    private const PUBLIC_DISKS = ['public', 'filament'];

    public function handle(): int
    {
        $referenced = Media::query()
            ->whereIn('disk', self::PUBLIC_DISKS)
            ->get()
            ->map(fn (Media $media) => $media->disk.'/'.$media->getPathRelativeToRoot())
            ->flip();

        $orphans = collect(self::PUBLIC_DISKS)->flatMap(fn (string $disk) => collect(Storage::disk($disk)->allFiles())
            ->reject(fn (string $path) => basename($path) === '.gitignore')
            ->reject(fn (string $path) => $referenced->has($disk.'/'.$path))
            ->map(fn (string $path) => ['disk' => $disk, 'path' => $path]));

        if ($orphans->isEmpty()) {
            $this->info('No orphaned public files found.');

            return self::SUCCESS;
        }

        $this->table(['Disk', 'Path'], $orphans->map(fn (array $file) => [$file['disk'], $file['path']]));

        if ($this->option('dry-run')) {
            $this->warn("Dry run: {$orphans->count()} file(s) would be quarantined.");

            return self::SUCCESS;
        }

        $quarantineDir = 'quarantine/'.now()->format('Ymd-His');
        $private = Storage::disk('local');

        foreach ($orphans as $file) {
            $target = "{$quarantineDir}/{$file['disk']}/{$file['path']}";
            $private->writeStream($target, Storage::disk($file['disk'])->readStream($file['path']));
            Storage::disk($file['disk'])->delete($file['path']);
        }

        $private->put("{$quarantineDir}/MANIFEST.json", json_encode([
            'moved_at' => now()->toIso8601String(),
            'note' => 'Files were publicly reachable but not referenced by any media record. Move a file back to its original disk/path to restore it.',
            'files' => $orphans->values(),
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

        $this->info("Moved {$orphans->count()} file(s) to storage/app/private/{$quarantineDir}");

        return self::SUCCESS;
    }
}
