<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * What a room category shows a guest, beyond its name and its opening rate.
 *
 * The design stage asks Room Management to describe each category properly —
 * a picture, what the stay includes, and how many rooms of it the hotel has —
 * and none of that had anywhere to live. rate and description were already
 * here as the openers a new room copies; these three are the category's own,
 * read by the Rooms page rather than stamped onto each room.
 *
 * inclusions is one item per line rather than JSON: it is written in a textarea
 * and read back into a list, and a text column keeps it legible in the database
 * and diffable in a faculty review.
 *
 * rooms_available is what the team says the hotel offers, which is not the same
 * as the number of hotel_rooms rows they have built in the ops stage — this is
 * the brochure figure, and the two are allowed to disagree while the inventory
 * is still being filled in.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('hotel_room_categories')) {
            return;
        }

        Schema::table('hotel_room_categories', function (Blueprint $table) {
            if (!Schema::hasColumn('hotel_room_categories', 'image_path')) {
                $table->string('image_path', 2048)->nullable()->after('description');
            }
            if (!Schema::hasColumn('hotel_room_categories', 'inclusions')) {
                $table->text('inclusions')->nullable()->after('image_path');
            }
            if (!Schema::hasColumn('hotel_room_categories', 'rooms_available')) {
                $table->unsignedInteger('rooms_available')->nullable()->after('inclusions');
            }
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('hotel_room_categories')) {
            return;
        }

        Schema::table('hotel_room_categories', function (Blueprint $table) {
            foreach (['image_path', 'inclusions', 'rooms_available'] as $column) {
                if (Schema::hasColumn('hotel_room_categories', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
