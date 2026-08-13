<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Faculty feedback on a submitted task.
 *
 * Deliberately adds columns only — the `status` enum is left alone. Sending a
 * task back for revision reuses the existing 'active' value, so the row simply
 * reappears in the student's task list with the feedback attached. Widening the
 * enum would need ->change(), which requires doctrine/dbal (not installed here).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            $table->text('feedback')->nullable()->after('status');
            $table->timestamp('feedback_at')->nullable()->after('feedback');
            $table->foreignId('feedback_by')->nullable()->after('feedback_at')
                ->constrained('users')->nullOnDelete();
            $table->unsignedTinyInteger('revision_count')->default(0)->after('feedback_by');
        });
    }

    public function down(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            $table->dropConstrainedForeignId('feedback_by');
            $table->dropColumn(['feedback', 'feedback_at', 'revision_count']);
        });
    }
};
