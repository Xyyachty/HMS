<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Widen tasks.due_date from a date to a timestamp so faculty can set the hour a
 * task is due, not just the day.
 *
 * Written as raw SQL rather than $table->timestamp(...)->change(): the ->change()
 * builder needs doctrine/dbal on Laravel 10, which this project does not ship.
 *
 * Widening is lossless. Existing dates become that day at 00:00, which is what
 * "due on the 9th" already meant everywhere it was compared or displayed.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('tasks', 'due_date')) {
            return;
        }

        match (DB::getDriverName()) {
            'pgsql' => DB::statement(
                'ALTER TABLE tasks ALTER COLUMN due_date TYPE timestamp(0) without time zone USING due_date::timestamp'
            ),
            'mysql', 'mariadb' => DB::statement(
                'ALTER TABLE tasks MODIFY due_date DATETIME NULL'
            ),
            // SQLite stores dates as text and does not enforce the column type,
            // so the existing column already accepts a full timestamp.
            default => null,
        };
    }

    public function down(): void
    {
        if (!Schema::hasColumn('tasks', 'due_date')) {
            return;
        }

        // Narrowing back drops the time component. That is the point of the
        // rollback, but it cannot be undone a second time.
        match (DB::getDriverName()) {
            'pgsql' => DB::statement(
                'ALTER TABLE tasks ALTER COLUMN due_date TYPE date USING due_date::date'
            ),
            'mysql', 'mariadb' => DB::statement(
                'ALTER TABLE tasks MODIFY due_date DATE NULL'
            ),
            default => null,
        };
    }
};
