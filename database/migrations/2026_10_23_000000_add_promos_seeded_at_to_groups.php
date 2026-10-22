<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * When a team was handed the four starting promos (HotelPromo::ensureDefaultsFor).
 * Kept on the team rather than inferred from "has no promos", so a Front Desk that
 * deletes every promo is not handed the four back on the next load.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('groups') && !Schema::hasColumn('groups', 'promos_seeded_at')) {
            Schema::table('groups', function (Blueprint $table) {
                $table->timestamp('promos_seeded_at')->nullable();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('groups', 'promos_seeded_at')) {
            Schema::table('groups', function (Blueprint $table) {
                $table->dropColumn('promos_seeded_at');
            });
        }
    }
};
