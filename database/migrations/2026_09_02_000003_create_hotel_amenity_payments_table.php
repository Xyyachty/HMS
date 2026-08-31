<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Money taken against a reservation — the down payment on a function room, the settlement
 * after the event, the fee for a spa treatment.
 *
 * Its own table rather than hotel_booking_payments because the customer very often has no
 * booking: a wedding party books the hall without anybody staying the night. That is the
 * same reason hotel_dine_in_payments exists for a walk-in diner.
 *
 * Columns mirror hotel_booking_payments deliberately, including the two snapshots.
 * total_due and balance record what was owed AT THE MOMENT THE MONEY CHANGED HANDS, so a
 * receipt printed in March still says what it said in March even after the event grew a
 * catering package in April. The live figures are always recomputed from the reservation;
 * these are history, never a source of truth.
 *
 * 'Charged to Room' is a third type alongside Full and Partial: no cash crossed the desk,
 * the amount moved onto the guest's folio as hotel_booking_charges instead. Recording it
 * here is what lets the reservation read as settled without pretending it was paid.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('hotel_amenity_payments')) {
            return;
        }

        Schema::create('hotel_amenity_payments', function (Blueprint $table) {
            $table->id('hotel_amenity_payment_id');
            $table->string('group_name');
            $table->unsignedBigInteger('faculty_id');
            $table->foreignId('group_id')->nullable()->constrained('groups', 'group_id')->nullOnDelete();
            // cascadeOnDelete: a payment line is not a record of anything without the
            // reservation it was taken against — the same call hotel_booking_payments makes.
            $table->foreignId('hotel_amenity_reservation_id')
                ->constrained('hotel_amenity_reservations', 'hotel_amenity_reservation_id')
                ->cascadeOnDelete();
            $table->string('type')->default('Full');   // Full | Partial | Charged to Room
            // Typed-in money: decimal, unlike the whole-peso rate cards it is priced off.
            $table->decimal('amount_paid', 12, 2)->default(0);
            // Snapshots at the moment of payment. Not the live figures — see the docblock.
            $table->decimal('total_due', 12, 2)->default(0);
            $table->decimal('balance', 12, 2)->default(0);
            $table->string('method')->default('Cash');
            $table->string('reference')->nullable();
            $table->string('payer_name')->nullable();
            $table->text('notes')->nullable();
            $table->string('collected_by')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();
            $table->index(['group_name', 'faculty_id']);
            $table->index('hotel_amenity_reservation_id');
        });

        if (DB::getDriverName() === 'pgsql') {
            DB::statement('ALTER TABLE "hotel_amenity_payments" ALTER COLUMN "group_name" TYPE citext');
        }

        $this->enableRowLevelSecurity('hotel_amenity_payments');
    }

    public function down(): void
    {
        Schema::dropIfExists('hotel_amenity_payments');
    }

    private function enableRowLevelSecurity(string $table): void
    {
        if (DB::getDriverName() === 'pgsql') {
            DB::statement('ALTER TABLE "' . $table . '" ENABLE ROW LEVEL SECURITY');
        }
    }
};
