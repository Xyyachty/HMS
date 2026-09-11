<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * What a room category tells a guest before they book it.
 *
 * The category already carried a picture, a rate, a description and its
 * inclusions. A guest deciding between two of them asks four more things the
 * hotel could not answer: how many people fit, what the bed is, how big the
 * room is, and what the rest of it looks like — one photograph of a room is a
 * photograph of one corner of it.
 *
 * All nullable: a category described before these existed simply has none of
 * them, and the page leaves out what has not been written.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('hotel_room_categories')) {
            return;
        }

        Schema::table('hotel_room_categories', function (Blueprint $table) {
            if (!Schema::hasColumn('hotel_room_categories', 'gallery')) {
                // The other pictures of the category, in the order they show.
                $table->json('gallery')->nullable()->after('image_path');
            }
            if (!Schema::hasColumn('hotel_room_categories', 'capacity')) {
                $table->unsignedSmallInteger('capacity')->nullable()->after('gallery');
            }
            if (!Schema::hasColumn('hotel_room_categories', 'bed_type')) {
                $table->string('bed_type', 80)->nullable()->after('capacity');
            }
            if (!Schema::hasColumn('hotel_room_categories', 'room_size')) {
                // Text, not a number: a team may write "32 m²" or "345 sq ft".
                $table->string('room_size', 40)->nullable()->after('bed_type');
            }
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('hotel_room_categories')) {
            return;
        }

        foreach (['gallery', 'capacity', 'bed_type', 'room_size'] as $column) {
            if (Schema::hasColumn('hotel_room_categories', $column)) {
                Schema::table('hotel_room_categories', function (Blueprint $table) use ($column) {
                    $table->dropColumn($column);
                });
            }
        }
    }
};
