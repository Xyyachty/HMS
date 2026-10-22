<?php

use App\Models\Group;
use App\Support\HotelTableDesk;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Every team starts with a dining room (HotelTableDesk::DEFAULT_TABLES), so a guest
 * reserving a table online has something to choose from. groups.tables_seeded_at
 * records that a team was handed them, so removing every table never brings them
 * back. Teams that exist now are seeded here; new ones on their first table load.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('groups') || !Schema::hasTable('hotel_dine_in_tables')) {
            return;
        }

        if (!Schema::hasColumn('groups', 'tables_seeded_at')) {
            Schema::table('groups', function (Blueprint $table) {
                $table->timestamp('tables_seeded_at')->nullable();
            });
        }

        Group::whereNull('tables_seeded_at')->get()->each(fn (Group $group) => HotelTableDesk::seed($group));
    }

    public function down(): void
    {
        if (Schema::hasColumn('groups', 'tables_seeded_at')) {
            Schema::table('groups', function (Blueprint $table) {
                $table->dropColumn('tables_seeded_at');
            });
        }
    }
};
