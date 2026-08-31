<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * A facility held for someone at a time — a spa appointment, a function room event.
 *
 * One table for both, discriminated by `kind`, which is the call 2026_08_11_000001 already
 * made when a dine-in order joined hotel_food_orders rather than getting a table of its
 * own. What they share is the whole spine: an amenity, a date, a start and an end, a
 * customer who may or may not be staying here, a status, and a bill. What differs is a
 * handful of columns that stay null on the other kind.
 *
 * starts_at / ends_at are 'HH:MM' strings on a separate date column, matching
 * hotel_amenities.opens_at. That shape is what makes the overlap check a plain string
 * comparison within one day, and it survives the JSON round trip to <input type="time">
 * without a cast or a timezone in the way. An event that runs past midnight is booked as
 * two days, which is also how a hotel actually sells one.
 *
 * MONEY. The fees are decimal, not integer, because these are amounts a desk types in
 * against a rate card rather than the rate card itself — the same split
 * hotel_booking_charges.amount makes against hotel_rooms.price. Catering is deliberately
 * NOT a column here: it lives on the linked hotel_food_orders row and is summed on read,
 * because a stored copy could disagree with the order Restaurant is actually cooking.
 *
 * charge_to_room + posted_to_folio_at are the billing hand-off. When the customer is a
 * checked-in guest the desk can push the whole thing onto their folio as
 * hotel_booking_charges rows, which HotelBooking::grandTotal() already sums and the
 * check-out gate already refuses to let them leave without settling. posted_to_folio_at is
 * the guard that stops a second press charging them twice.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('hotel_amenity_reservations')) {
            return;
        }

        Schema::create('hotel_amenity_reservations', function (Blueprint $table) {
            $table->id('hotel_amenity_reservation_id');
            $table->string('group_name');
            $table->unsignedBigInteger('faculty_id');
            $table->foreignId('group_id')->nullable()->constrained('groups', 'group_id')->nullOnDelete();
            // nullOnDelete throughout: a reservation is a record of money and a promise.
            // Retiring the facility, the service or the stay behind it must not erase it.
            $table->foreignId('hotel_amenity_id')->nullable()
                ->constrained('hotel_amenities', 'hotel_amenity_id')->nullOnDelete();
            $table->foreignId('hotel_amenity_service_id')->nullable()
                ->constrained('hotel_amenity_services', 'hotel_amenity_service_id')->nullOnDelete();
            // Null for a walk-in: a function room can be booked by somebody who is not
            // staying at the hotel at all, and that is the normal case for a wedding.
            $table->foreignId('hotel_booking_id')->nullable()
                ->constrained('hotel_bookings', 'hotel_booking_id')->nullOnDelete();

            // 'appointment' | 'event' — see HotelAmenityReservation::KINDS.
            $table->string('kind');
            // Human handle the desk reads out over the phone, e.g. SPA-000012.
            $table->string('reference')->nullable();
            $table->string('amenity_name');          // snapshot, like hotel_amenity_visits
            $table->string('customer_name');
            $table->string('contact_no')->nullable();
            $table->string('email')->nullable();

            $table->date('scheduled_on');
            $table->string('starts_at', 5);
            $table->string('ends_at', 5);
            // Plain string: this repo validates enums in PHP, not with a DB CHECK.
            $table->string('status')->default('Pending');
            $table->text('special_requests')->nullable();

            /* ── event only ─────────────────────────────────────────────── */
            $table->string('event_type')->nullable();
            $table->unsignedSmallInteger('guest_count')->nullable();
            $table->string('package')->nullable();
            $table->foreignId('hotel_catering_package_id')->nullable()
                ->constrained('hotel_catering_packages', 'hotel_catering_package_id')->nullOnDelete();
            // Housekeeping's turnaround for one event, separate from the amenity's own
            // condition: a hall being cleaned after a wedding is not a broken hall.
            $table->string('housekeeping_status')->nullable();

            /* ── money ──────────────────────────────────────────────────── */
            $table->decimal('venue_fee', 12, 2)->default(0);      // event
            $table->decimal('setup_fee', 12, 2)->default(0);      // event, Hall + Setup
            $table->decimal('service_fee', 12, 2)->default(0);    // appointment
            $table->decimal('additional_fee', 12, 2)->default(0); // anything else agreed
            $table->string('additional_note')->nullable();
            $table->boolean('charge_to_room')->default(false);
            $table->timestamp('posted_to_folio_at')->nullable();

            $table->string('booked_by')->nullable();
            $table->timestamps();
            $table->index(['group_name', 'faculty_id']);
            // The overlap check: one facility, one date, ordered by start time.
            $table->index(['hotel_amenity_id', 'scheduled_on', 'starts_at']);
        });

        if (DB::getDriverName() === 'pgsql') {
            DB::statement('ALTER TABLE "hotel_amenity_reservations" ALTER COLUMN "group_name" TYPE citext');
        }

        $this->enableRowLevelSecurity('hotel_amenity_reservations');
    }

    public function down(): void
    {
        Schema::dropIfExists('hotel_amenity_reservations');
    }

    private function enableRowLevelSecurity(string $table): void
    {
        if (DB::getDriverName() === 'pgsql') {
            DB::statement('ALTER TABLE "' . $table . '" ENABLE ROW LEVEL SECURITY');
        }
    }
};
