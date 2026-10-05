<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Lets the Restaurant team choose the dishes its Best Seller section shows
 * (RS TASK 2). Off for every dish to begin with: while none is chosen the
 * section keeps showing the first six on the menu, as it always has.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('hotel_menu_items') && !Schema::hasColumn('hotel_menu_items', 'best_seller')) {
            Schema::table('hotel_menu_items', function (Blueprint $table) {
                $table->boolean('best_seller')->default(false);
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('hotel_menu_items', 'best_seller')) {
            Schema::table('hotel_menu_items', function (Blueprint $table) {
                $table->dropColumn('best_seller');
            });
        }
    }
};
