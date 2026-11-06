<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Adults past what the room sleeps pay an extra-person fee each night
 * (HotelBooking::EXTRA_ADULT_RATE) on top of the folding bed they sleep on.
 * Snapshot like room_rate: the rate a stay was sold at stays with it.
 * Bookings taken before this stay null and bill exactly as they did.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('hotel_bookings') && !Schema::hasColumn('hotel_bookings', 'extra_adults')) {
            Schema::table('hotel_bookings', function (Blueprint $table) {
                $table->unsignedSmallInteger('extra_adults')->nullable();
                $table->unsignedInteger('extra_adult_rate')->nullable();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('hotel_bookings', 'extra_adults')) {
            Schema::table('hotel_bookings', function (Blueprint $table) {
                $table->dropColumn(['extra_adults', 'extra_adult_rate']);
            });
        }
    }
};
