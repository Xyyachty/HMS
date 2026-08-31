<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * The hotel's facilities — the pool, the gym, the function room — owned by Housekeeping
 * and listed on the Amenities page of the team's site.
 *
 * This is the third thing in this codebase called an "amenity", so the distinction matters.
 * hotel_addons is what a guest borrows for their room (a folding bed, an extra towel) and is
 * billed against a stay. The `amenities` arrays inside template_content_items are the little
 * icon chips on a room card ("King Bed", "WiFi") and are website copy, not records. These rows
 * are neither: a facility exists whether or not anyone books it, has posted opening hours, and
 * can be shut for the afternoon or broken for a week. None of that fits on an add-on.
 *
 * status is one of Available / Temporarily Closed / Under Maintenance, and it is the only
 * status stored. Where a repair stands is NOT a fourth value here — an amenity under repair
 * has a hotel_complaints row pointing back at it (see the sibling migration), and how far
 * Maintenance has got is read off that complaint instead. Two status columns that can
 * disagree with each other is the bug hotel_addons avoids by deriving availability, and the
 * same reasoning applies: the amenity stays Under Maintenance until a housekeeper looks at
 * the finished repair and says otherwise.
 *
 * There is deliberately no delete path. A repair request outlives the shutdown that caused
 * it, and dropping the amenity would strand it. Retiring one means Temporarily Closed.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('hotel_amenities')) {
            return;
        }

        Schema::create('hotel_amenities', function (Blueprint $table) {
            $table->id('hotel_amenity_id');
            $table->string('group_name');
            $table->unsignedBigInteger('faculty_id');
            // The explicit 'group_id' second argument is not optional here: constrained()
            // would otherwise guess a column named "id", which groups does not have.
            $table->foreignId('group_id')->nullable()->constrained('groups', 'group_id')->nullOnDelete();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('location')->nullable();
            // 'HH:MM' strings rather than time columns. These are the hours printed on a
            // brochure page — nothing ever subtracts them — and a string makes the round
            // trip to <input type="time"> and back without a cast or a timezone in the way.
            $table->string('opens_at', 5)->nullable();
            $table->string('closes_at', 5)->nullable();
            // Plain string: this repo validates enums in PHP rather than trusting a DB
            // CHECK constraint. See HotelAmenity::STATUSES.
            $table->string('status')->default('Available');
            // A path on the media disk — see App\Support\HotelImageStore. Never base64.
            $table->text('image')->nullable();
            $table->timestamps();
            $table->index(['group_name', 'faculty_id']);
        });

        // student_groups.group_name, group_settings, groups and tasks are all citext. A
        // plain varchar here would compare case-sensitively against them and quietly miss
        // a team whose name differs only in case.
        if (DB::getDriverName() === 'pgsql') {
            DB::statement('ALTER TABLE "hotel_amenities" ALTER COLUMN "group_name" TYPE citext');
        }

        $this->enableRowLevelSecurity('hotel_amenities');
    }

    public function down(): void
    {
        Schema::dropIfExists('hotel_amenities');
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
