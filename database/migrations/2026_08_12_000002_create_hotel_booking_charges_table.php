<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Ad-hoc charges Front Desk adds to a stay at check-out: minibar, laundry, a late
 * checkout fee — anything the hotel bills for that is neither the room nor a kitchen
 * order.
 *
 * Room charges are computed from the booking and room service from hotel_food_orders;
 * this table is the third and last thing the final bill sums, and the only one a
 * member of staff types in by hand.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('hotel_booking_charges')) {
            return;
        }

        Schema::create('hotel_booking_charges', function (Blueprint $table) {
            $table->id('hotel_booking_charge_id');
            $table->string('group_name');
            $table->unsignedBigInteger('faculty_id');
            $table->foreignId('group_id')->nullable()->constrained('groups', 'group_id')->nullOnDelete();
            // A charge without its stay is not a record of anything, so it goes with it —
            // the same call hotel_bookings' own foreign keys make.
            $table->foreignId('hotel_booking_id')->constrained('hotel_bookings', 'hotel_booking_id')->cascadeOnDelete();
            $table->string('description');
            // Money is decimal here for the same reason it is on hotel_booking_payments:
            // a typed-in amount can carry halves the whole-peso room rate never does.
            $table->decimal('amount', 12, 2)->default(0);
            $table->string('added_by')->nullable();
            $table->timestamps();
            $table->index(['group_name', 'faculty_id']);
            // "what else does this stay owe" — the only lookup this table exists for.
            $table->index('hotel_booking_id');
        });

        $this->enableRowLevelSecurity('hotel_booking_charges');
    }

    public function down(): void
    {
        Schema::dropIfExists('hotel_booking_charges');
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
