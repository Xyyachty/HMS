<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * When the user finished or skipped the first-login guided tour
 * (partials.guided-tour). Null means it has not run yet, so it opens by itself
 * on their dashboard; Restart Guided Tour in the sidebar replays it anytime.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('users') && !Schema::hasColumn('users', 'tour_completed_at')) {
            Schema::table('users', function (Blueprint $table) {
                $table->timestamp('tour_completed_at')->nullable();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('users', 'tour_completed_at')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('tour_completed_at');
            });
        }
    }
};
