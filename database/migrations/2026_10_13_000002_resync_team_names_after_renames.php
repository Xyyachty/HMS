<?php

use App\Models\Group;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Renaming a team used to rename only its groups row, so its concepts, tasks and
 * the rest stayed filed under the old name. A team whose Front Desk had submitted
 * the hotel concept then read as never having submitted, and Set Task stayed locked.
 *
 * group_id survived the rename (groups is renamed in place), so each stranded row
 * is given back its team's current name through it. Rows already in step are not
 * touched, so this is a no-op on a clean database.
 *
 * Outside a transaction and per table: a row that would collide with one already
 * written under the new name fails that table alone instead of the whole deploy.
 */
return new class extends Migration
{
    public $withinTransaction = false;

    public function up(): void
    {
        foreach (Group::teamOwnedTables() as $table) {
            if ($table === 'groups' || !Schema::hasColumn($table, 'group_id')) {
                continue;
            }

            $match = "g.group_id = {$table}.group_id AND g.faculty_id = {$table}.faculty_id";

            try {
                DB::update(
                    "UPDATE {$table} SET group_name = (SELECT g.group_name FROM groups g WHERE {$match})
                     WHERE EXISTS (SELECT 1 FROM groups g WHERE {$match}
                                   AND CAST(g.group_name AS TEXT) <> CAST({$table}.group_name AS TEXT))"
                );
            } catch (\Throwable $e) {
                logger()->warning("Team name resync skipped {$table}: " . $e->getMessage());
            }
        }
    }

    public function down(): void
    {
        // The old names were wrong; there is nothing to put back.
    }
};
