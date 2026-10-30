<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * A stay booked under one of Front Desk's promos or packages. The name and price
 * are a snapshot, like room_rate: editing or removing the promo later must not
 * move a sold stay. promo_blocks is how many 12-hour blocks the package price
 * covers; blocks added by Extend Stay are charged at the room rate
 * (HotelBooking::totalDue). Bookings without a promo stay null.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('hotel_bookings') && !Schema::hasColumn('hotel_bookings', 'promo_name')) {
            Schema::table('hotel_bookings', function (Blueprint $table) {
                $table->string('promo_name', 120)->nullable();
                $table->unsignedInteger('promo_price')->nullable();
                $table->unsignedSmallInteger('promo_blocks')->nullable();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('hotel_bookings', 'promo_name')) {
            Schema::table('hotel_bookings', function (Blueprint $table) {
                $table->dropColumn(['promo_name', 'promo_price', 'promo_blocks']);
            });
        }
    }
};
