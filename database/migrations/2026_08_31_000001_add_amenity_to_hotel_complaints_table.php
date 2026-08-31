<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Links a complaint back to the amenity whose repair it is.
 *
 * The same call hotel_room_inspection_id makes: an amenity that breaks does not get its own
 * queue, its own statuses and its own Maintenance screen — it becomes a hotel_complaints row
 * with department 'maintenance', because all of that already exists and Maintenance is
 * already watching it.
 *
 * Front Desk guest complaints and inspection findings leave this null. Only
 * HotelAmenityDesk::requestRepair() sets it, and HotelAmenityDesk reads it back to work out
 * whether an amenity is still with Maintenance or is sitting ready for Housekeeping to
 * verify. A complaint never carries both this and hotel_room_inspection_id.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('hotel_complaints', 'hotel_amenity_id')) {
            return;
        }

        Schema::table('hotel_complaints', function (Blueprint $table) {
            $table->foreignId('hotel_amenity_id')
                ->nullable()
                ->after('hotel_room_inspection_id')
                ->constrained('hotel_amenities', 'hotel_amenity_id')
                ->nullOnDelete();
            $table->index('hotel_amenity_id');
        });
    }

    public function down(): void
    {
        if (!Schema::hasColumn('hotel_complaints', 'hotel_amenity_id')) {
            return;
        }

        Schema::table('hotel_complaints', function (Blueprint $table) {
            $table->dropConstrainedForeignId('hotel_amenity_id');
        });
    }
};
