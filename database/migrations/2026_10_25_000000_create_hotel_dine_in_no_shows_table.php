<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * The reservations whose customer never turned up. A reservation lives on its
 * hotel_dine_in_tables row, so freeing the table (HotelTableDesk::releaseNoShows)
 * would otherwise lose it; this keeps a copy of what was booked. Same RLS step as
 * every hotel_* table created after 2026_08_05_000001.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('hotel_dine_in_no_shows')) {
            return;
        }

        Schema::create('hotel_dine_in_no_shows', function (Blueprint $table) {
            $table->id('hotel_dine_in_no_show_id');
            $table->string('group_name');
            $table->unsignedBigInteger('faculty_id');
            $table->foreignId('group_id')->nullable()->constrained('groups', 'group_id')->nullOnDelete();
            // No foreign key: the table can be removed and its history kept.
            $table->unsignedBigInteger('hotel_dine_in_table_id')->nullable();
            $table->string('table_name');
            $table->string('guest_name')->nullable();
            $table->string('contact_no')->nullable();
            $table->unsignedTinyInteger('party_size')->nullable();
            // Manila wall-clock, like hotel_dine_in_tables.reserved_for.
            $table->timestamp('reserved_for')->nullable();
            $table->string('assigned_by')->nullable();
            $table->timestamp('assigned_at')->nullable();
            $table->timestamps();
            $table->index(['group_name', 'faculty_id']);
        });

        if (DB::getDriverName() === 'pgsql') {
            DB::statement('ALTER TABLE "hotel_dine_in_no_shows" ENABLE ROW LEVEL SECURITY');
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('hotel_dine_in_no_shows');
    }
};
