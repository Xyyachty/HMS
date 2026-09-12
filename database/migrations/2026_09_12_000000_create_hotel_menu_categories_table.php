<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * A team's own menu courses.
 *
 * Menu categories were five hard-coded strings on HotelMenuItem, so a team could
 * file a dish but never name a course of its own — unlike room categories, which
 * have been the team's to add and rename since hotel_room_categories arrived.
 * This is that table's counterpart, and it follows the same rule: the five
 * defaults are seeded as ordinary rows on a team's first visit (see
 * HotelMenuDefaults::ensureCategoriesFor), so a default can be renamed like any
 * other row rather than being a constant nobody can touch.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hotel_menu_categories', function (Blueprint $table) {
            $table->id('hotel_menu_category_id');
            $table->string('group_name');
            $table->unsignedBigInteger('faculty_id')->nullable();
            $table->unsignedBigInteger('group_id')->nullable();
            $table->string('name', 60);
            /* The slot a default occupies, the way a room category occupies a
               hundreds block: renaming "Desserts" leaves position 4 taken, so
               the seeder never writes "Desserts" back underneath it. A course a
               team invents has no default to stand for and takes the next free
               number. */
            $table->unsignedInteger('position')->default(0);
            $table->timestamps();

            // One course name per team. Two teams may both have "Desserts".
            $table->unique(['group_name', 'faculty_id', 'name'], 'hotel_menu_categories_team_name_unique');
            $table->index(['group_name', 'faculty_id'], 'hotel_menu_categories_team_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hotel_menu_categories');
    }
};
