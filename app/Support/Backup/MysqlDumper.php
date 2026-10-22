<?php

namespace App\Support\Backup;

use Illuminate\Database\Connection;
use PDO;
use RuntimeException;

/**
 * Pure-PHP MySQL/MariaDB dump and load, for shared hosting where exec() and
 * mysqldump are usually unavailable. The dump writes exactly one SQL statement
 * per line (one row per INSERT), which keeps loading simple and lets a backup
 * be verified by counting rows without a database server.
 */
class MysqlDumper
{
    public function __construct(private readonly Connection $connection) {}

    /**
     * @return array<string, int> Rows written per table
     */
    public function dump(string $path): array
    {
        $pdo = $this->connection->getPdo();
        $ownsTransaction = $this->connection->transactionLevel() === 0;

        if ($ownsTransaction) {
            // All tables are read from the same point in time while the site keeps running
            $pdo->exec('SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ');
            $pdo->exec('START TRANSACTION WITH CONSISTENT SNAPSHOT');
        }

        $out = fopen($path, 'wb');
        fwrite($out, "-- SRAFES database backup\n");
        fwrite($out, "SET NAMES utf8mb4;\n");
        fwrite($out, "SET FOREIGN_KEY_CHECKS=0;\n");

        $counts = [];

        foreach ($this->tables() as $table) {
            $create = $pdo->query('SHOW CREATE TABLE '.$this->wrap($table))->fetch(PDO::FETCH_NUM)[1];

            fwrite($out, 'DROP TABLE IF EXISTS '.$this->wrap($table).";\n");
            fwrite($out, preg_replace('/\s*\R\s*/', ' ', $create).";\n");

            $counts[$table] = 0;
            $rows = $pdo->query('SELECT * FROM '.$this->wrap($table));

            while ($row = $rows->fetch(PDO::FETCH_ASSOC)) {
                $columns = implode(', ', array_map($this->wrap(...), array_keys($row)));
                // PDO::quote escapes newlines, so every INSERT stays on one line
                $values = implode(', ', array_map(fn ($value) => $value === null ? 'NULL' : $pdo->quote((string) $value), $row));

                fwrite($out, 'INSERT INTO '.$this->wrap($table)." ({$columns}) VALUES ({$values});\n");
                $counts[$table]++;
            }
        }

        fwrite($out, "SET FOREIGN_KEY_CHECKS=1;\n");
        fclose($out);

        if ($ownsTransaction) {
            $pdo->exec('COMMIT');
        }

        ksort($counts);

        return $counts;
    }

    /**
     * Replace every table in the database with the contents of a dump.
     */
    public function load(string $path): void
    {
        $pdo = $this->connection->getPdo();
        $pdo->exec('SET FOREIGN_KEY_CHECKS=0');

        // Drop everything first so tables created after the backup don't linger
        foreach ($this->tables() as $table) {
            $pdo->exec('DROP TABLE IF EXISTS '.$this->wrap($table));
        }

        $in = fopen($path, 'rb');

        while (($line = fgets($in)) !== false) {
            $statement = rtrim($line, "\r\n");

            if ($statement === '' || str_starts_with($statement, '--')) {
                continue;
            }

            $pdo->exec($statement);
        }

        fclose($in);
        $pdo->exec('SET FOREIGN_KEY_CHECKS=1');
    }

    /**
     * @return array<string, int> Rows per table, counted from a dump file
     */
    public static function countRows(string $path): array
    {
        $counts = [];
        $in = fopen($path, 'rb');

        while (($line = fgets($in)) !== false) {
            if (preg_match('/^CREATE TABLE `((?:[^`]|``)+)`/', $line, $match)) {
                $counts[str_replace('``', '`', $match[1])] ??= 0;
            } elseif (preg_match('/^INSERT INTO `((?:[^`]|``)+)`/', $line, $match)) {
                $table = str_replace('``', '`', $match[1]);
                $counts[$table] = ($counts[$table] ?? 0) + 1;
            }
        }

        fclose($in);

        if ($counts === []) {
            throw new RuntimeException('The SQL dump contains no tables.');
        }

        ksort($counts);

        return $counts;
    }

    /**
     * @return array<string>
     */
    private function tables(): array
    {
        return $this->connection->getPdo()
            ->query("SHOW FULL TABLES WHERE Table_type = 'BASE TABLE'")
            ->fetchAll(PDO::FETCH_COLUMN);
    }

    private function wrap(string $identifier): string
    {
        return '`'.str_replace('`', '``', $identifier).'`';
    }
}
