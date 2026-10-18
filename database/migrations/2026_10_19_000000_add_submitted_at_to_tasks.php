<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * When a student last handed a task in. updated_at cannot say: faculty's
 * approval writes the row again. Reports ranks Top Performing Students by it,
 * first to finish everything first. Rows handed in before this column fall
 * back on updated_at there, so nothing is backfilled.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('tasks') && !Schema::hasColumn('tasks', 'submitted_at')) {
            Schema::table('tasks', function (Blueprint $table) {
                $table->timestamp('submitted_at')->nullable();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('tasks', 'submitted_at')) {
            Schema::table('tasks', function (Blueprint $table) {
                $table->dropColumn('submitted_at');
            });
        }
    }
};
