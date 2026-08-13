<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * The first stage of a stay is called 'Booked' now, not 'Reserved' — the desk staff
 * reading these screens call it a booking, and 'Reserved' read like the old
 * hotel_rooms.status value that was dropped in 2026_08_12_000000.
 *
 * hotel_bookings.status is a plain string with no DB-level enum (see the baseline
 * migration's header for why), so the rename is a data rewrite rather than a schema
 * change. HotelBooking::normalizeStatus() also still maps the old word onto the new
 * one, which covers any payload written by a page that was already open when this ran.
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::table('hotel_bookings')
            ->where('status', 'Reserved')
            ->update(['status' => 'Booked']);

        $this->setStatusDefault('Booked');
    }

    public function down(): void
    {
        DB::table('hotel_bookings')
            ->where('status', 'Booked')
            ->update(['status' => 'Reserved']);

        $this->setStatusDefault('Reserved');
    }

    /**
     * The column default still said 'Reserved' from the 2026_08_11_000002 create.
     * Nothing inserts a booking without a status — HotelBookingDesk::reserve() always
     * sets one — but leaving a stale default behind is how the old word creeps back.
     */
    private function setStatusDefault(string $status): void
    {
        if (DB::getDriverName() === 'pgsql') {
            DB::statement("ALTER TABLE \"hotel_bookings\" ALTER COLUMN \"status\" SET DEFAULT '" . $status . "'");
        }
    }
};
