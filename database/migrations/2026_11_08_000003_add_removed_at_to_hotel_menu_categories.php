<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Lets Restaurant Management remove a menu course from the Restaurant tab bar.
 *
 * The row stays and is only marked, as for room categories: the five starting
 * courses are re-created for any slot with no row
 * (HotelMenuDefaults::ensureCategoriesFor), so deleting the row would bring
 * "Desserts" straight back on the next load.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('hotel_menu_categories') && !Schema::hasColumn('hotel_menu_categories', 'removed_at')) {
            Schema::table('hotel_menu_categories', function (Blueprint $table) {
                $table->timestamp('removed_at')->nullable();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('hotel_menu_categories', 'removed_at')) {
            Schema::table('hotel_menu_categories', function (Blueprint $table) {
                $table->dropColumn('removed_at');
            });
        }
    }
};
