<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Lets Room Management remove a room category from the Rooms tab bar.
 *
 * The row stays and is only marked: the five starting categories are re-created
 * for any hundreds block that has no row (HotelRoomDefaults::ensureCategoriesFor),
 * so deleting the row would bring "Classic" straight back on the next load.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('hotel_room_categories') && !Schema::hasColumn('hotel_room_categories', 'removed_at')) {
            Schema::table('hotel_room_categories', function (Blueprint $table) {
                $table->timestamp('removed_at')->nullable();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('hotel_room_categories', 'removed_at')) {
            Schema::table('hotel_room_categories', function (Blueprint $table) {
                $table->dropColumn('removed_at');
            });
        }
    }
};
