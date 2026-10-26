<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * When a student first saved work on a task in the Template Editor. Replaces the
 * ticked steps as the line between Not Started and In Progress: the editor
 * stamps it for the task the student has picked, on the first save that
 * changes something. Rows ticked under the old checklist still read as started
 * (Task::getIsStartedAttribute), so nothing is backfilled.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('tasks') && !Schema::hasColumn('tasks', 'started_at')) {
            Schema::table('tasks', function (Blueprint $table) {
                $table->timestamp('started_at')->nullable();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('tasks', 'started_at')) {
            Schema::table('tasks', function (Blueprint $table) {
                $table->dropColumn('started_at');
            });
        }
    }
};
