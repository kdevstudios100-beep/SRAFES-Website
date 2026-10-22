<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Copies every row from one database connection to another, e.g. the current
 * SQLite database into MySQL before going live on shared hosting. The target
 * must already be migrated to the same schema and must not hold school data.
 */
class CopyDatabase extends Command
{
    protected $signature = 'srafes:copy-database
        {from : Source connection name, e.g. sqlite}
        {to : Target connection name, e.g. mysql}
        {--force : Empty the target tables even if they already contain rows}';

    protected $description = 'Copy all data between database connections (for moving from SQLite to MySQL)';

    /** Runtime-only tables that are rebuilt automatically */
    private const SKIP = ['migrations', 'cache', 'cache_locks', 'sessions', 'jobs', 'job_batches', 'failed_jobs'];

    public function handle(): int
    {
        [$from, $to] = [$this->argument('from'), $this->argument('to')];

        if ($from === $to) {
            $this->error('Source and target must be different connections.');

            return self::FAILURE;
        }

        $source = DB::connection($from);
        $target = DB::connection($to);

        $tables = collect(Schema::connection($from)->getTableListing(schemaQualified: false))
            ->reject(fn (string $table) => in_array($table, self::SKIP, true) || str_starts_with($table, 'sqlite_'))
            ->values();

        $missing = $tables->reject(fn (string $table) => Schema::connection($to)->hasTable($table));

        if ($missing->isNotEmpty()) {
            $this->error("Target is missing tables: {$missing->implode(', ')}. Run: php artisan migrate --database={$to}");

            return self::FAILURE;
        }

        $occupied = $tables->filter(fn (string $table) => $target->table($table)->exists());

        if ($occupied->isNotEmpty() && ! $this->option('force')) {
            $this->error("Target already has data in: {$occupied->implode(', ')}. Use --force to replace it.");

            return self::FAILURE;
        }

        Schema::connection($to)->withoutForeignKeyConstraints(function () use ($tables, $source, $target) {
            foreach ($tables as $table) {
                $target->table($table)->delete();

                $columns = Schema::connection($target->getName())->getColumnListing($table);

                $source->table($table)->orderBy($this->orderColumn($source->getName(), $table))->chunk(500, function ($rows) use ($target, $table, $columns) {
                    $target->table($table)->insert(
                        $rows->map(fn ($row) => array_intersect_key((array) $row, array_flip($columns)))->all()
                    );
                });
            }
        });

        $report = $tables->map(fn (string $table) => [
            $table,
            $sourceCount = $source->table($table)->count(),
            $targetCount = $target->table($table)->count(),
            $sourceCount === $targetCount ? 'OK' : 'MISMATCH',
        ]);

        $this->table(['Table', $from, $to, 'Check'], $report);

        if ($report->contains(fn (array $row) => $row[3] !== 'OK')) {
            $this->error('Row counts differ. Do not switch to the target database.');

            return self::FAILURE;
        }

        $this->info("All {$tables->count()} tables copied and row counts match.");

        return self::SUCCESS;
    }

    private function orderColumn(string $connection, string $table): string
    {
        $columns = Schema::connection($connection)->getColumnListing($table);

        return in_array('id', $columns, true) ? 'id' : $columns[0];
    }
}
