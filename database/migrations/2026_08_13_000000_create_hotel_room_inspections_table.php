<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Housekeeping's post-checkout pass on a room.
 *
 * Opened the moment a stay ends (see HotelBookingDesk::checkOut(), which now calls
 * HotelHousekeepingDesk::openInspection() inside the same transaction), so a room that
 * has just gone Cleaning always has exactly one open row here. Damage/repair findings
 * are not stored on this table — they become hotel_complaints rows (see the next
 * migration), because Maintenance already has a working queue, statuses and
 * notifications; this table only tracks the inspection itself.
 *
 * room_name / guest_name / checked_out_at are snapshots, the same call
 * hotel_complaints makes for room_number/guest_name: a room can be renamed and a
 * booking can be deleted, but the inspection is a record of what was found, not a
 * live join.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('hotel_room_inspections')) {
            return;
        }

        Schema::create('hotel_room_inspections', function (Blueprint $table) {
            $table->id('hotel_room_inspection_id');
            $table->string('group_name');
            $table->unsignedBigInteger('faculty_id');
            $table->foreignId('group_id')->nullable()->constrained('groups', 'group_id')->nullOnDelete();
            $table->foreignId('hotel_room_id')->constrained('hotel_rooms', 'hotel_room_id')->cascadeOnDelete();
            // nullOnDelete, not cascade: the inspection outlives the booking record the
            // same way hotel_room_inspections' own existence outlives the stay it closed.
            $table->foreignId('hotel_booking_id')->nullable()->constrained('hotel_bookings', 'hotel_booking_id')->nullOnDelete();
            $table->string('room_name');
            $table->string('guest_name')->nullable();
            $table->timestamp('checked_out_at')->nullable();
            // Pending / Inspecting / Awaiting Repair / Awaiting Re-inspection / Completed —
            // see HotelRoomInspection::STATUSES. Plain string: this repo validates enums
            // in PHP rather than trusting a DB CHECK constraint (baseline_schema.php).
            $table->string('status')->default('Pending');
            // Headline from HotelRoomInspection::FINDINGS. Null until the housekeeper
            // records one.
            $table->string('finding')->nullable();
            $table->text('notes')->nullable();
            // Display names, not user ids — the same convention hotel_complaints uses
            // for filed_by/handled_by.
            $table->string('inspected_by')->nullable();
            $table->string('completed_by')->nullable();
            $table->timestamp('inspected_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
            $table->index(['group_name', 'faculty_id']);
            $table->index('status');
            // The duplicate guard in openInspection() and the room card both ask
            // "does this room already have an open pass" — this is that lookup.
            $table->index(['hotel_room_id', 'status']);
        });

        $this->enableRowLevelSecurity('hotel_room_inspections');
    }

    public function down(): void
    {
        Schema::dropIfExists('hotel_room_inspections');
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
