<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * The four activities a task is worked through as, and which of them are done.
 *
 * Copied onto the row at assignment time rather than read back from
 * TaskChecklist each time it is displayed, for the same reason the title and the
 * description are copied: the checklist is edited as the course is taught, and a
 * task a student is halfway through must not change its steps underneath them.
 *
 * One column rather than a table of its own. There are always four, they are only
 * ever read and written with the task that owns them, and nothing queries across
 * them — a join table would be four rows of ceremony per task for no question it
 * could answer.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('tasks')) {
            return;
        }

        Schema::table('tasks', function (Blueprint $table) {
            if (!Schema::hasColumn('tasks', 'activities')) {
                $table->json('activities')->nullable()->after('description');
            }
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('tasks')) {
            return;
        }

        Schema::table('tasks', function (Blueprint $table) {
            if (Schema::hasColumn('tasks', 'activities')) {
                $table->dropColumn('activities');
            }
        });
    }
};
