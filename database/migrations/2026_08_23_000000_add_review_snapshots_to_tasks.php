<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Anchors the faculty review's Before/After comparison to the submission event.
 *
 * The comparison first read TeamRoleTemplateVersion save-snapshots, but those are
 * only written on a manual save or publish — the builder's 7s autosave deliberately
 * does not snapshot — so a team that never pressed Ctrl+S had no history to compare,
 * and MAX_VERSION_SNAPSHOTS pruning could drop what little there was.
 *
 * Submitting a task now takes its own snapshot and records it here, so "what changed
 * since last time" no longer depends on a student's save habits:
 *   submitted_version_id — the snapshot taken when this task was last submitted
 *   previous_version_id  — the one before it, which is the "Before" side
 *
 * Both are plain nullable ids rather than foreign keys: a snapshot may legitimately
 * be pruned out from under a very old task, and that should read as "no comparison
 * available", not block the delete or orphan the row.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            if (!Schema::hasColumn('tasks', 'submitted_version_id')) {
                $table->unsignedBigInteger('submitted_version_id')->nullable()->after('revision_count');
            }
            if (!Schema::hasColumn('tasks', 'previous_version_id')) {
                $table->unsignedBigInteger('previous_version_id')->nullable()->after('submitted_version_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            foreach (['submitted_version_id', 'previous_version_id'] as $column) {
                if (Schema::hasColumn('tasks', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
