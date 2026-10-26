<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * What a guest can actually book at a by-appointment facility — a Swedish massage, a
 * foot spa. The rate card behind the Spa.
 *
 * Keyed to an amenity rather than hard-wired to "Spa", so a team that adds a second
 * appointment facility (a salon, a clinic) gets the same machinery without a migration.
 *
 * duration_minutes is what makes a slot: Front Desk picks a start time and the end is
 * computed, which is what the overlap check needs and what stops a desk booking a 60
 * minute massage into a 30 minute gap.
 *
 * price is an integer for the same reason hotel_rooms.price and hotel_addons.price are —
 * a rate card is whole pesos. Only money someone types in freehand is decimal.
 *
 * Housekeeping owns this list, the same call the amenities catalogue itself makes: the
 * department that operates a thing owns what it is, and Front Desk reads it to sell it.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('hotel_amenity_services')) {
            return;
        }

        Schema::create('hotel_amenity_services', function (Blueprint $table) {
            $table->id('hotel_amenity_service_id');
            $table->string('group_name');
            $table->unsignedBigInteger('faculty_id');
            // The explicit 'group_id' second argument is not optional here: constrained()
            // would otherwise guess a column named "id", which groups does not have.
            $table->foreignId('group_id')->nullable()->constrained('groups', 'group_id')->nullOnDelete();
            // cascadeOnDelete: a treatment is meaningless without the facility that gives
            // it. Unlike a booking, there is no record of money here to strand.
            $table->foreignId('hotel_amenity_id')
                ->constrained('hotel_amenities', 'hotel_amenity_id')->cascadeOnDelete();
            $table->string('name');
            $table->string('description')->nullable();
            // How long one booking of it runs. The end time of a slot is derived from this.
            $table->unsignedSmallInteger('duration_minutes')->default(60);
            // Whole pesos, like the rest of the rate card.
            $table->integer('price')->default(0);
            // Retired rather than deleted, so past appointments keep pointing at something.
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->index(['group_name', 'faculty_id']);
            $table->index('hotel_amenity_id');
        });

        if (DB::getDriverName() === 'pgsql') {
            DB::statement('ALTER TABLE "hotel_amenity_services" ALTER COLUMN "group_name" TYPE citext');
        }

        $this->enableRowLevelSecurity('hotel_amenity_services');
    }

    public function down(): void
    {
        Schema::dropIfExists('hotel_amenity_services');
    }

    /**
     * The 2026_08_05_000001 migration closed the Supabase REST API off from every table
     * that existed then. Tables created afterwards need the same treatment: default
     * privileges are already revoked, RLS still has to be switched on.
     */
    private function enableRowLevelSecurity(string $table): void
    {
        if (DB::getDriverName() === 'pgsql') {
            DB::statement('ALTER TABLE "' . $table . '" ENABLE ROW LEVEL SECURITY');
        }
    }
};
