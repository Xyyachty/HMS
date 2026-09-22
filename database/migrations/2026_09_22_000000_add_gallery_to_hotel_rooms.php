<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * A room is shown with three photographs instead of one.
 *
 * One picture has to do the work of the room, the bathroom and the view, and the
 * team ends up choosing which of the three to show. `gallery` holds the other two
 * as a JSON list of media-disk paths — the same kind of value `image` holds, and
 * the same shape hotel_amenities.gallery already uses.
 *
 * `image` stays as the card's primary photograph, so every room that already has
 * one keeps it and rooms saved before this reads as a gallery of one.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('hotel_rooms')) {
            return;
        }

        Schema::table('hotel_rooms', function (Blueprint $table) {
            if (!Schema::hasColumn('hotel_rooms', 'gallery')) {
                $table->json('gallery')->nullable()->after('image');
            }
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('hotel_rooms')) {
            return;
        }

        Schema::table('hotel_rooms', function (Blueprint $table) {
            if (Schema::hasColumn('hotel_rooms', 'gallery')) {
                $table->dropColumn('gallery');
            }
        });
    }
};
