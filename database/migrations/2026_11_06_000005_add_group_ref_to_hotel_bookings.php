<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Two or more rooms booked together for one guest share a group_ref: one
 * reservation, billed together on a Group Folio and paid with one payment and one
 * receipt when settled together, while each room can still check out on its own
 * date. Null on a room booked alone, and on every booking from before this.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('hotel_bookings') && !Schema::hasColumn('hotel_bookings', 'group_ref')) {
            Schema::table('hotel_bookings', function (Blueprint $table) {
                $table->string('group_ref', 40)->nullable()->index();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('hotel_bookings') && Schema::hasColumn('hotel_bookings', 'group_ref')) {
            Schema::table('hotel_bookings', function (Blueprint $table) {
                $table->dropColumn('group_ref');
            });
        }
    }
};
