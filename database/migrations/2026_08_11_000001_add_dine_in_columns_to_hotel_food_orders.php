<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Let hotel_food_orders carry a dine-in order as well as a room-service one, rather
 * than standing up a parallel table for it. A dine-in order has no room to bill and
 * a table to bill instead, so room_number has to stop being required.
 *
 * The room_number column type change uses raw SQL rather than Blueprint::change():
 * that needs doctrine/dbal, which is not installed in this app (only pulled in
 * transitively as an unmet "suggest"), and this app is Postgres-only in every
 * environment (see DB_CONNECTION in .env / render.yaml) so one raw statement covers
 * every deploy target that matters without adding a dependency for a single column.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('hotel_food_orders', 'order_type')) {
            Schema::table('hotel_food_orders', function (Blueprint $table) {
                $table->string('order_type')->default('room_service')->after('faculty_id');
                // nullOnDelete: a removed table should not take its order history with it.
                $table->foreignId('dine_in_table_id')->nullable()
                    ->after('order_type')
                    ->constrained('hotel_dine_in_tables', 'hotel_dine_in_table_id')
                    ->nullOnDelete();
            });
        }

        if (Schema::hasColumn('hotel_food_orders', 'room_number')) {
            DB::statement('ALTER TABLE hotel_food_orders ALTER COLUMN room_number DROP NOT NULL');
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('hotel_food_orders', 'dine_in_table_id')) {
            Schema::table('hotel_food_orders', function (Blueprint $table) {
                $table->dropConstrainedForeignId('dine_in_table_id');
            });
        }

        if (Schema::hasColumn('hotel_food_orders', 'order_type')) {
            Schema::table('hotel_food_orders', function (Blueprint $table) {
                $table->dropColumn('order_type');
            });
        }

        if (Schema::hasColumn('hotel_food_orders', 'room_number')) {
            // Backfill first — a NOT NULL constraint cannot be restored over nulls a
            // dine-in order may have left behind.
            DB::table('hotel_food_orders')->whereNull('room_number')->update(['room_number' => '']);
            DB::statement('ALTER TABLE hotel_food_orders ALTER COLUMN room_number SET NOT NULL');
        }
    }
};
