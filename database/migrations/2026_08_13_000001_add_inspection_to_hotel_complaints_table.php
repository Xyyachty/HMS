<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Links a complaint back to the housekeeping inspection that raised it.
 *
 * Front Desk complaints leave this null — a guest complaint is not a housekeeping
 * finding. Only HotelHousekeepingDesk::reportIssue() sets it, and
 * HotelHousekeepingDesk::onIssueClosed() reads it back to know which inspection to
 * advance once every issue it raised is closed.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('hotel_complaints', 'hotel_room_inspection_id')) {
            return;
        }

        Schema::table('hotel_complaints', function (Blueprint $table) {
            $table->foreignId('hotel_room_inspection_id')
                ->nullable()
                ->after('group_id')
                ->constrained('hotel_room_inspections', 'hotel_room_inspection_id')
                ->nullOnDelete();
            $table->index('hotel_room_inspection_id');
        });
    }

    public function down(): void
    {
        if (!Schema::hasColumn('hotel_complaints', 'hotel_room_inspection_id')) {
            return;
        }

        Schema::table('hotel_complaints', function (Blueprint $table) {
            $table->dropConstrainedForeignId('hotel_room_inspection_id');
        });
    }
};
