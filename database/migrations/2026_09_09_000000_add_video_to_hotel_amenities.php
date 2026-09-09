<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * The clip a facility shows on the hotel site.
 *
 * The Amenities page plays a video where it used to print a photograph, so the
 * file Housekeeping uploads has to live somewhere. It is a path on the media
 * disk exactly like `image` — Supabase Storage in production, the public disk
 * locally — never the bytes: a video in a column would be megabytes on every
 * catalogue query, which is the mistake HotelImageStore was written to undo.
 *
 * `image` stays: it is the poster frame the card shows before anyone presses
 * play, and it is still what the staff screens list.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('hotel_amenities')) {
            return;
        }

        Schema::table('hotel_amenities', function (Blueprint $table) {
            if (!Schema::hasColumn('hotel_amenities', 'video')) {
                $table->string('video', 2048)->nullable()->after('image');
            }
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('hotel_amenities')) {
            return;
        }

        Schema::table('hotel_amenities', function (Blueprint $table) {
            if (Schema::hasColumn('hotel_amenities', 'video')) {
                $table->dropColumn('video');
            }
        });
    }
};
