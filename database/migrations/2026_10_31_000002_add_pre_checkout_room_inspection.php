<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Housekeeping inspects the room before the guest's check-out is finished, not after.
 *
 * Front Desk starting a check-out now moves the booking to Pending Room Inspection and
 * opens the hotel_room_inspections row right away. Housekeeping reports what it found;
 * damage or missing items become hotel_inspection_damages rows, one per item. Nothing
 * is billed from them until Front Desk reviews each one: Charged adds a
 * hotel_booking_charges row and keeps its id here, Waived adds nothing.
 *
 * reported_at is when Housekeeping sent the report to Front Desk. Inspections opened
 * by the old after-checkout flow leave it null.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('hotel_room_inspections') && !Schema::hasColumn('hotel_room_inspections', 'reported_at')) {
            Schema::table('hotel_room_inspections', function (Blueprint $table) {
                $table->timestamp('reported_at')->nullable();
                // The pre-checkout report is looked up by its booking.
                $table->index('hotel_booking_id');
            });
        }

        if (Schema::hasTable('hotel_inspection_damages')) {
            return;
        }

        Schema::create('hotel_inspection_damages', function (Blueprint $table) {
            $table->id('hotel_inspection_damage_id');
            $table->string('group_name');
            $table->unsignedBigInteger('faculty_id');
            $table->foreignId('group_id')->nullable()->constrained('groups', 'group_id')->nullOnDelete();
            $table->foreignId('hotel_room_inspection_id')->constrained('hotel_room_inspections', 'hotel_room_inspection_id')->cascadeOnDelete();
            $table->foreignId('hotel_booking_id')->constrained('hotel_bookings', 'hotel_booking_id')->cascadeOnDelete();
            $table->string('item', 120);
            $table->text('description')->nullable();
            // Storage path of a photo, through HotelImageStore. Null when none was taken.
            $table->string('evidence')->nullable();
            // Pending Review / Charged / Waived — see HotelInspectionDamage::STATUSES.
            $table->string('status')->default('Pending Review');
            $table->decimal('amount', 10, 2)->nullable();
            // Unique: one damage item is billed once, however many times Approve is sent.
            $table->foreignId('hotel_booking_charge_id')->nullable()->unique()
                ->constrained('hotel_booking_charges', 'hotel_booking_charge_id')->nullOnDelete();
            $table->string('reported_by')->nullable();
            $table->string('reviewed_by')->nullable();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamps();
            $table->index(['group_name', 'faculty_id']);
            $table->index(['hotel_booking_id', 'status']);
        });

        if (DB::getDriverName() === 'pgsql') {
            DB::statement('ALTER TABLE "hotel_inspection_damages" ENABLE ROW LEVEL SECURITY');
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('hotel_inspection_damages');

        if (Schema::hasColumn('hotel_room_inspections', 'reported_at')) {
            Schema::table('hotel_room_inspections', function (Blueprint $table) {
                $table->dropIndex(['hotel_booking_id']);
                $table->dropColumn('reported_at');
            });
        }
    }
};
