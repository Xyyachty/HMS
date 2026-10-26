<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * The Amenities page shows photographs again, several per facility.
 *
 * The clip that briefly lived on `video` is gone: one 50 MB file per amenity is
 * a heavy page for a guest on a phone, and a gallery of compressed stills says
 * the same thing about a pool for a fraction of the bytes.
 *
 * `gallery` holds the extra shots as a JSON list of media-disk paths — the same
 * kind of value `image` holds, which stays as the card's primary photograph, so
 * an amenity that already has one keeps it and the gallery is what View Details
 * pages through.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('hotel_amenities')) {
            return;
        }

        Schema::table('hotel_amenities', function (Blueprint $table) {
            if (!Schema::hasColumn('hotel_amenities', 'gallery')) {
                $table->json('gallery')->nullable()->after('image');
            }
        });

        Schema::table('hotel_amenities', function (Blueprint $table) {
            if (Schema::hasColumn('hotel_amenities', 'video')) {
                $table->dropColumn('video');
            }
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('hotel_amenities')) {
            return;
        }

        Schema::table('hotel_amenities', function (Blueprint $table) {
            if (!Schema::hasColumn('hotel_amenities', 'video')) {
                $table->string('video', 2048)->nullable()->after('image');
            }
            if (Schema::hasColumn('hotel_amenities', 'gallery')) {
                $table->dropColumn('gallery');
            }
        });
    }
};
