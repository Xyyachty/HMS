<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * How many people a stay is for. HotelBookingDesk::reserve() checks it against the
 * room category's capacity. Bookings taken before this column stay null, which
 * reads as "not asked", so nothing is backfilled.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('hotel_bookings') && !Schema::hasColumn('hotel_bookings', 'guest_count')) {
            Schema::table('hotel_bookings', function (Blueprint $table) {
                $table->unsignedSmallInteger('guest_count')->nullable();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('hotel_bookings', 'guest_count')) {
            Schema::table('hotel_bookings', function (Blueprint $table) {
                $table->dropColumn('guest_count');
            });
        }
    }
};
