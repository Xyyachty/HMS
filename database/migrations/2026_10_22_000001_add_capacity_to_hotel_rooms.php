<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * How many guests one room sleeps, set by Room Management on the room itself.
 * Null means the room follows its category's capacity, so nothing is backfilled.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('hotel_rooms') && !Schema::hasColumn('hotel_rooms', 'capacity')) {
            Schema::table('hotel_rooms', function (Blueprint $table) {
                $table->unsignedSmallInteger('capacity')->nullable();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('hotel_rooms', 'capacity')) {
            Schema::table('hotel_rooms', function (Blueprint $table) {
                $table->dropColumn('capacity');
            });
        }
    }
};
