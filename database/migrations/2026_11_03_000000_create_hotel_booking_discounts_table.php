<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Senior Citizen (RA 9994) and PWD (RA 10754) discounts claimed on a stay.
 *
 * One row per eligible person in the room, the booker or a companion, so the
 * discount belongs to that person and not to the whole booking. A row only lowers the
 * bill once Front Desk has verified the person's ID (verified_at); until then it is a
 * pending claim the bill shows but does not apply.
 *
 * The amounts are a record of the last computation; HotelBooking prices the bill live
 * from the same rule, so they never disagree for an open stay.
 *
 * Additive only: no existing table or row is touched.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('hotel_booking_discounts')) {
            return;
        }

        Schema::create('hotel_booking_discounts', function (Blueprint $table) {
            $table->id('hotel_booking_discount_id');
            $table->string('group_name');
            $table->unsignedBigInteger('faculty_id');
            $table->foreignId('group_id')->nullable()->constrained('groups', 'group_id')->nullOnDelete();
            $table->foreignId('hotel_booking_id')->constrained('hotel_bookings', 'hotel_booking_id')->cascadeOnDelete();
            // 'Senior Citizen' or 'PWD'.
            $table->string('type', 20);
            // 'adult' shares the room charge; 'child' is discounted on their own child rate.
            $table->string('occupant', 10)->default('adult');
            // Which entry of hotel_bookings.children, for a child.
            $table->unsignedSmallInteger('child_index')->nullable();
            // The person who made the booking, as opposed to a companion.
            $table->boolean('is_primary')->default(false);
            $table->string('full_name');
            // Senior Citizens only: the age check runs off it.
            $table->date('birth_date')->nullable();
            // Encrypted by the model, so it is text and cannot be indexed directly.
            $table->text('id_number');
            // sha256 of type + normalised ID number: the same card cannot be claimed twice
            // on one stay, which the encrypted column alone could not enforce.
            $table->string('id_hash', 64);
            $table->timestamp('verified_at')->nullable();
            $table->string('verified_by')->nullable();
            $table->decimal('eligible_amount', 12, 2)->default(0);
            $table->decimal('vat_exempt_amount', 12, 2)->default(0);
            $table->decimal('discount_amount', 12, 2)->default(0);
            $table->timestamps();
            $table->index(['group_name', 'faculty_id']);
            $table->index('hotel_booking_id');
            $table->unique(['hotel_booking_id', 'id_hash']);
        });

        if (DB::getDriverName() === 'pgsql') {
            DB::statement('ALTER TABLE "hotel_booking_discounts" ENABLE ROW LEVEL SECURITY');
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('hotel_booking_discounts');
    }
};
