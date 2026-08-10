<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Writes the live MySQL data out as a PostgreSQL script that can be opened and run
 * in pgAdmin against Supabase.
 *
 * Deliberately produces a file rather than copying over a live connection: the import
 * is then inspectable before it runs, cannot be interrupted half-way by the network,
 * and does not depend on Laravel being able to reach Supabase at all.
 *
 * This is NOT a mysqldump. A MySQL dump cannot be fed to PostgreSQL — backticks alone
 * make every CREATE TABLE fail — which is the whole reason this command exists.
 *
 * Reads and writes the pre-rename schema, where every primary key is called `id`. Both
 * sides of that are still true for the MySQL source, which is now a cold backup and was
 * never renamed; the PostgreSQL side is not, so the script this produces no longer
 * matches the live database. Kept as-is because the one migration it existed for is
 * done — it would need the column map from
 * 2026_08_10_000000_rename_primary_keys_to_entity_ids before it could run again.
 */
class ExportPostgresData extends Command
{
    protected $signature = 'hms:export-pgsql
        {--out=database/hms_pgsql.sql : Where to write the script}
        {--table=* : Limit to specific tables}
        {--batch=100 : Rows per INSERT statement}';

    protected $description = 'Export MySQL data as a PostgreSQL script for import via pgAdmin';

    /**
     * Parents before children. PostgreSQL has no usable FOREIGN_KEY_CHECKS=0 — the
     * equivalent, SET session_replication_role = replica, needs superuser, which the
     * Supabase `postgres` role does not have. So insert order is not optional.
     *
     * `migrations` is deliberately absent: the baseline migration writes its own row.
     */
    private const TABLE_ORDER = [
        // users first: faculties.user_id references it.
        'users',
        'faculties',
        'faculty_classes',
        'students',
        'student_groups',
        'student_group_roles',
        'tasks',
        'group_settings',
        'team_role_templates',
        'team_role_template_versions',
        'template_layouts',
        'template_elements',
        'template_content_items',
        'template_content_fields',
        'template_images',
        'team_template_edit_grants',
        'hotel_rooms',
        'hotel_menu_items',
        'hotel_food_orders',
        'hotel_customers',
        'reservation_notifications',
        'activity_logs',
    ];

    /**
     * tinyint(1) columns that are real booleans in PostgreSQL. MySQL's PDO hands these
     * back as the strings "0"/"1"; emitting them as 0/1 fails with "column ... is of
     * type boolean but expression is of type integer".
     *
     * tasks.revision_count is also a tinyint but is a counter, so it stays numeric.
     */
    private const BOOLEAN_COLUMNS = [
        'group_settings.is_published',
        'team_role_templates.is_published',
        'team_role_template_versions.is_published',
        'template_elements.free_position',
        'template_elements.keep_fixed',
        'template_layouts.is_visible',
        'reservation_notifications.acknowledged',
    ];

    /** Stored lowercased so PostgreSQL lookups behave the way MySQL's collation did. */
    private const LOWERCASE_COLUMNS = [
        'users.email',
        'hotel_customers.email',
    ];

    private const NUMERIC_TYPES = ['tinyint', 'smallint', 'mediumint', 'int', 'integer', 'bigint', 'decimal', 'float', 'double'];

    public function handle(): int
    {
        $out = $this->option('out');
        $batchSize = max(1, (int) $this->option('batch'));
        $only = (array) $this->option('table');

        $tables = $only ? array_values(array_intersect(self::TABLE_ORDER, $only)) : self::TABLE_ORDER;
        if ($only && count($tables) !== count($only)) {
            $this->error('Unknown table(s): ' . implode(', ', array_diff($only, self::TABLE_ORDER)));

            return self::FAILURE;
        }

        $handle = fopen(base_path($out), 'w');
        if (!$handle) {
            $this->error("Could not open $out for writing.");

            return self::FAILURE;
        }

        $this->writeHeader($handle, $tables);

        $total = 0;
        foreach ($tables as $table) {
            $count = $this->writeTable($handle, $table, $batchSize);
            $total += $count;
            $this->line(sprintf('  %-30s %d row(s)', $table, $count));
        }

        $this->writeSequenceResets($handle, $tables);
        fwrite($handle, "\nCOMMIT;\n");
        fclose($handle);

        $path = base_path($out);
        $this->newLine();
        $this->info(sprintf('Wrote %s — %d rows, %s.', $out, $total, $this->humanBytes(filesize($path))));

        return $this->selfCheck($path) ? self::SUCCESS : self::FAILURE;
    }

    private function writeHeader($handle, array $tables): void
    {
        $stamp = now()->toDateTimeString();
        fwrite($handle, <<<SQL
-- HMS data export for PostgreSQL / Supabase
-- Generated {$stamp} from the live MySQL database by: php artisan hms:export-pgsql
--
-- Run this in pgAdmin against Supabase AFTER the schema exists
-- (php artisan migrate --database=pgsql). The whole file is one transaction:
-- it either loads completely or leaves the database untouched.
--
-- Safe to re-run — each table is truncated first.

SET standard_conforming_strings = on;

BEGIN;


SQL);

        // Truncate in reverse dependency order; CASCADE covers anything referencing them.
        $reversed = array_reverse($tables);
        fwrite($handle, "-- Clear existing rows so this script can be re-run.\n");
        fwrite($handle, 'TRUNCATE TABLE ' . implode(', ', array_map(
            fn ($t) => $this->quoteIdentifier($t),
            $reversed
        )) . " RESTART IDENTITY CASCADE;\n\n");
    }

    private function writeTable($handle, string $table, int $batchSize): int
    {
        $columns = $this->columnsFor($table);
        if (!$columns) {
            return 0;
        }

        $names = array_keys($columns);
        $quotedColumns = implode(', ', array_map(fn ($c) => $this->quoteIdentifier($c), $names));

        fwrite($handle, "-- {$table}\n");

        $written = 0;
        $buffer = [];

        // Ordered by id so parents land before children — template_content_items has a
        // self-referencing parent_id, and parent_id < id holds for every row.
        DB::connection('mysql')->table($table)->orderBy('id')->chunk(500, function ($rows) use (
            &$buffer, &$written, $handle, $table, $columns, $names, $quotedColumns, $batchSize
        ) {
            foreach ($rows as $row) {
                $values = [];
                foreach ($names as $column) {
                    $values[] = $this->literal($table, $column, ((array) $row)[$column] ?? null, $columns[$column]);
                }
                $buffer[] = '(' . implode(', ', $values) . ')';

                if (count($buffer) >= $batchSize) {
                    $this->flush($handle, $table, $quotedColumns, $buffer);
                    $written += count($buffer);
                    $buffer = [];
                }
            }
        });

        if ($buffer) {
            $this->flush($handle, $table, $quotedColumns, $buffer);
            $written += count($buffer);
        }

        fwrite($handle, "\n");

        return $written;
    }

    private function flush($handle, string $table, string $columns, array $rows): void
    {
        fwrite($handle, sprintf(
            "INSERT INTO %s (%s) VALUES\n%s;\n",
            $this->quoteIdentifier($table),
            $columns,
            implode(",\n", $rows)
        ));
    }

    /** @return array<string,string> column name => MySQL data type */
    private function columnsFor(string $table): array
    {
        $rows = DB::connection('mysql')->select(
            'SELECT column_name, data_type FROM information_schema.columns
             WHERE table_schema = DATABASE() AND table_name = ? ORDER BY ordinal_position',
            [$table]
        );

        $out = [];
        foreach ($rows as $row) {
            $row = array_change_key_case((array) $row, CASE_LOWER);
            $out[$row['column_name']] = strtolower($row['data_type']);
        }

        return $out;
    }

    private function literal(string $table, string $column, mixed $value, string $type): string
    {
        if ($value === null) {
            return 'NULL';
        }

        $key = "$table.$column";

        if (in_array($key, self::BOOLEAN_COLUMNS, true)) {
            return ((string) $value === '1' || $value === true) ? 'true' : 'false';
        }

        if (in_array($type, self::NUMERIC_TYPES, true)) {
            return (string) $value;
        }

        $string = (string) $value;

        if (in_array($key, self::LOWERCASE_COLUMNS, true)) {
            $string = mb_strtolower(trim($string));
        }

        // standard_conforming_strings is on (set in the header), so backslashes are
        // literal and only the quote needs doubling. That keeps JSON escapes and
        // base64 payloads byte-identical.
        return "'" . str_replace("'", "''", $string) . "'";
    }

    /**
     * Rows are inserted with explicit ids, which leaves every sequence sitting at 1 —
     * the first application insert would then fail with a duplicate key.
     */
    private function writeSequenceResets($handle, array $tables): void
    {
        fwrite($handle, "-- Move each id sequence past the highest imported id.\n");
        foreach ($tables as $table) {
            fwrite($handle, sprintf(
                "SELECT setval(pg_get_serial_sequence('public.%s', 'id'), COALESCE((SELECT MAX(id) FROM %s), 1), (SELECT COUNT(*) FROM %s) > 0);\n",
                $table,
                $this->quoteIdentifier($table),
                $this->quoteIdentifier($table)
            ));
        }
    }

    /** Catch MySQL-isms before the file is ever opened in pgAdmin. */
    private function selfCheck(string $path): bool
    {
        $contents = file_get_contents($path);
        $problems = [];

        foreach (['`' => 'backtick', 'ENGINE=' => 'ENGINE=', 'AUTO_INCREMENT' => 'AUTO_INCREMENT'] as $needle => $label) {
            if (str_contains($contents, $needle)) {
                $problems[] = $label;
            }
        }

        if ($problems) {
            $this->error('MySQL syntax leaked into the output: ' . implode(', ', $problems));

            return false;
        }

        $this->line('Self-check: no backticks, no ENGINE=, no AUTO_INCREMENT — this is PostgreSQL syntax.');

        return true;
    }

    private function quoteIdentifier(string $name): string
    {
        return '"' . str_replace('"', '""', $name) . '"';
    }

    private function humanBytes(int $bytes): string
    {
        if ($bytes >= 1048576) {
            return round($bytes / 1048576, 2) . ' MB';
        }

        return $bytes >= 1024 ? round($bytes / 1024, 1) . ' KB' : $bytes . ' B';
    }
}
