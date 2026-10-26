<?php

use App\Support\HotelRoomDefaults;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * The five starting categories now come with a guest limit
 * (HotelRoomDefaults::CATEGORY_CAPACITY). Rows already written for those slots
 * take it too, matched by hundreds block so a renamed starting category counts,
 * but only where no limit has been set yet.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('hotel_room_categories', 'capacity')) {
            return;
        }

        foreach (HotelRoomDefaults::CATEGORY_FLOORS as $name => $floor) {
            DB::table('hotel_room_categories')
                ->where('floor_number', $floor)
                ->whereNull('capacity')
                ->update(['capacity' => HotelRoomDefaults::CATEGORY_CAPACITY[$name]]);
        }
    }

    public function down(): void
    {
        // Not reversed: a limit set here cannot be told apart from one Room Management set.
    }
};
