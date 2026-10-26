<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * One guest's use of a facility they have to be signed into — the pool, the gym.
 *
 * Front Desk verifies the guest is checked in, registers the entry, and registers the exit
 * when they come back out. Between those two writes the row is the answer to "who is in the
 * pool right now", which is the question a lifeguard, a capacity limit and an evacuation all
 * need answered.
 *
 * Append-only, one row per visit, exited_at nullable. That is deliberate and it is why this
 * is not modelled on hotel_dine_in_tables: that table overwrites one row per table and keeps
 * no history, which is right for a live board and useless for a log. The shape here is
 * hotel_bookings' instead — a timestamp per milestone on a row that is never reused — so
 * "how many people used the gym last week" is a query rather than a guess.
 *
 * amenity_name and room_name are snapshots, the same call hotel_booking_addons.name makes:
 * renaming the Gym must not rewrite last month's register.
 *
 * Only 'registered' amenities get rows here. The playground is open access and has nothing
 * to sign; the spa and the function room are booked ahead and live in
 * hotel_amenity_reservations instead.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('hotel_amenity_visits')) {
            return;
        }

        Schema::create('hotel_amenity_visits', function (Blueprint $table) {
            $table->id('hotel_amenity_visit_id');
            $table->string('group_name');
            $table->unsignedBigInteger('faculty_id');
            // The explicit 'group_id' second argument is not optional here: constrained()
            // would otherwise guess a column named "id", which groups does not have.
            $table->foreignId('group_id')->nullable()->constrained('groups', 'group_id')->nullOnDelete();
            // nullOnDelete on both: retiring an amenity, or losing the stay behind a guest,
            // must not erase the record that somebody was in the pool at four o'clock.
            $table->foreignId('hotel_amenity_id')->nullable()
                ->constrained('hotel_amenities', 'hotel_amenity_id')->nullOnDelete();
            $table->foreignId('hotel_booking_id')->nullable()
                ->constrained('hotel_bookings', 'hotel_booking_id')->nullOnDelete();
            // Snapshots. What the register says happened must not move when a name changes.
            $table->string('amenity_name');
            $table->string('guest_name');
            $table->string('room_name')->nullable();
            // A guest signs in their whole party at once — one row, four swimmers — because
            // the capacity check counts people, not registrations.
            $table->unsignedSmallInteger('party_size')->default(1);
            $table->timestamp('entered_at');
            // Null is the whole state machine: null means still inside.
            $table->timestamp('exited_at')->nullable();
            $table->string('registered_by')->nullable();
            $table->string('closed_by')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->index(['group_name', 'faculty_id']);
            // "Who is in the pool right now" and the capacity check both run this pair.
            $table->index(['hotel_amenity_id', 'exited_at']);
        });

        // student_groups.group_name, group_settings, groups and hotel_amenities are all
        // citext. A plain varchar here would compare case-sensitively against them and
        // quietly miss a team whose name differs only in case.
        if (DB::getDriverName() === 'pgsql') {
            DB::statement('ALTER TABLE "hotel_amenity_visits" ALTER COLUMN "group_name" TYPE citext');
        }

        $this->enableRowLevelSecurity('hotel_amenity_visits');
    }

    public function down(): void
    {
        Schema::dropIfExists('hotel_amenity_visits');
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
