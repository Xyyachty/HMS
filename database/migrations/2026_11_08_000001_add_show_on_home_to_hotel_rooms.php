<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Lets Room Management choose the rooms the home page's Available Rooms section
 * shows, the way the Restaurant picks its Best Seller dishes. Off for every room
 * to begin with: while none is ticked the section keeps showing the first eight.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('hotel_rooms') && !Schema::hasColumn('hotel_rooms', 'show_on_home')) {
            Schema::table('hotel_rooms', function (Blueprint $table) {
                $table->boolean('show_on_home')->default(false);
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('hotel_rooms', 'show_on_home')) {
            Schema::table('hotel_rooms', function (Blueprint $table) {
                $table->dropColumn('show_on_home');
            });
        }
    }
};
