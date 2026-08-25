<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Puts a task on one team.
 *
 * `tasks` was the only team-owned table with no team of its own. Faculty assigned a
 * role, storeTask() fanned the row out to every team under that faculty holding it,
 * and a task's team was recovered at read time by joining student_groups back on
 * student_id — done in one place correctly and skipped in two others. A row with no
 * student (created when nobody held the role yet) belonged to no team at all and so
 * showed up on every team's dashboard.
 *
 * Carry the same tuple every other team-owned table carries — group_name + faculty_id,
 * with group_id alongside once groups exist — so a task states its team instead of
 * having it inferred, and an unclaimed row can be scoped like any other.
 *
 * Backfill takes each task's team from its student's membership. Rows with no student,
 * or whose student has since left every team, keep a null group_name and stay visible
 * to whoever holds the role, which is what they did before this migration.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            if (!Schema::hasColumn('tasks', 'group_name')) {
                $table->string('group_name')->nullable()->after('faculty_id');
            }
            if (!Schema::hasColumn('tasks', 'group_id')) {
                $table->foreignId('group_id')->nullable()->after('group_name')
                    ->constrained('groups', 'group_id')->nullOnDelete();
            }
        });

        // student_groups.group_name, group_settings, team_role_templates and groups are
        // all citext. A plain varchar here would compare case-sensitively against them
        // and quietly miss a team whose name differs only in case.
        if (DB::getDriverName() === 'pgsql') {
            DB::statement('ALTER TABLE "tasks" ALTER COLUMN "group_name" TYPE citext');
        }

        Schema::table('tasks', function (Blueprint $table) {
            $table->index(['faculty_id', 'group_name']);
        });

        // One statement per task rather than a correlated update, so a student sitting
        // in two teams resolves the same way the rest of the app resolves it — first
        // membership under the task's own faculty.
        DB::table('tasks')
            ->whereNotNull('student_id')
            ->whereNull('group_name')
            ->orderBy('task_id')
            ->select('task_id', 'student_id', 'faculty_id')
            ->chunk(200, function ($tasks) {
                foreach ($tasks as $task) {
                    $membership = DB::table('student_groups')
                        ->where('student_id', $task->student_id)
                        ->where('faculty_id', $task->faculty_id)
                        ->orderBy('student_group_id')
                        ->first(['group_name', 'group_id']);

                    if (!$membership) {
                        continue;
                    }

                    DB::table('tasks')
                        ->where('task_id', $task->task_id)
                        ->update([
                            'group_name' => $membership->group_name,
                            'group_id' => $membership->group_id,
                        ]);
                }
            });
    }

    public function down(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            $table->dropIndex(['faculty_id', 'group_name']);

            if (Schema::hasColumn('tasks', 'group_id')) {
                $table->dropConstrainedForeignId('group_id');
            }
            if (Schema::hasColumn('tasks', 'group_name')) {
                $table->dropColumn('group_name');
            }
        });
    }
};
