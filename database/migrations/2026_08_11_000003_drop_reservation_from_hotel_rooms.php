<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Second half of the booking split. 2026_08_11_000002 copied every reservation blob
 * into hotel_guests / hotel_bookings / hotel_booking_payments; this drops the blob, so
 * hotel_rooms finally describes nothing but the room.
 *
 * Kept as its own migration on purpose: the backfill can be run and eyeballed against
 * the live data before the only copy of the old bookings goes away.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('hotel_rooms', 'reservation')) {
            return;
        }

        Schema::table('hotel_rooms', function (Blueprint $table) {
            $table->dropColumn('reservation');
        });
    }

    public function down(): void
    {
        if (Schema::hasColumn('hotel_rooms', 'reservation')) {
            return;
        }

        // Comes back empty: hotel_bookings is the record now, and rolling this back is
        // only ever about column shape.
        Schema::table('hotel_rooms', function (Blueprint $table) {
            $table->json('reservation')->nullable();
        });
    }
};
