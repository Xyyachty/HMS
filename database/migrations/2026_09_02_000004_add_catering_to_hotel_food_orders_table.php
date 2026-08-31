<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Links a kitchen ticket back to the function room event it is feeding.
 *
 * The same call 2026_08_11_000001 made for dine-in: catering is a third order_type on
 * hotel_food_orders rather than a parallel table, because Restaurant Services already has
 * a board, a status pipeline, a nav badge and a reports page watching that table. A
 * separate catering queue would be a second thing for the kitchen to remember to look at.
 *
 * Room service and dine-in orders leave this null. Only
 * HotelAmenityReservationDesk::book() sets it, when the event picks a package that
 * includes catering, and the reservation reads it back to price the event: the catering
 * charge is the order's own total, summed on read, so it can never disagree with what the
 * kitchen is actually cooking.
 *
 * A catering order carries no menu_item_id on its lines and deliberately skips the stock
 * transaction the other two order types run — a buffet package is priced per head and has
 * no shelf to deduct from. See HotelCateringDesk.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('hotel_food_orders', 'hotel_amenity_reservation_id')) {
            return;
        }

        Schema::table('hotel_food_orders', function (Blueprint $table) {
            $table->foreignId('hotel_amenity_reservation_id')
                ->nullable()
                ->after('dine_in_table_id')
                ->constrained('hotel_amenity_reservations', 'hotel_amenity_reservation_id')
                ->nullOnDelete();
            $table->index('hotel_amenity_reservation_id');
        });
    }

    public function down(): void
    {
        if (!Schema::hasColumn('hotel_food_orders', 'hotel_amenity_reservation_id')) {
            return;
        }

        Schema::table('hotel_food_orders', function (Blueprint $table) {
            $table->dropConstrainedForeignId('hotel_amenity_reservation_id');
        });
    }
};
