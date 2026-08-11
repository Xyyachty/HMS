<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * hotel_rooms.status just dropped 'Reserved' and 'Occupied' from
 * App\Models\HotelRoom::STATUSES — occupancy is the booking's job now (see
 * HotelBooking::status / HotelRoom::activeBooking()), and the room's own status is
 * housekeeping-only: Available / Cleaning / Maintenance.
 *
 * The column is a plain string with no DB-level enum, so nothing stops old rows from
 * still holding one of the two dropped values. Left alone they'd render however
 * HotelRoom::normalizeStatus() falls back (Available) on every future write, but the
 * stored value itself would stay stale — this backfills it to match now, once.
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::table('hotel_rooms')
            ->whereIn('status', ['Reserved', 'Occupied'])
            ->update(['status' => 'Available']);
    }

    public function down(): void
    {
        // Not reversible: which rows were Reserved vs Occupied vs already Available
        // is not recoverable from here.
    }
};
