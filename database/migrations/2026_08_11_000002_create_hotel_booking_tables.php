<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Splits guest bookings out of hotel_rooms.
 *
 * hotel_rooms used to be two things at once: the room inventory Room Management
 * creates, and — inside a `reservation` JSON blob — whoever happened to be staying in
 * it. That blob could hold exactly one guest, could not be searched or joined, and had
 * no history: reserving a room a second time overwrote the first stay outright.
 *
 * Three tables replace it:
 *   hotel_guests           — the person, reusable across stays
 *   hotel_bookings         — one stay: a room, a guest, dates, lifecycle timestamps
 *   hotel_booking_payments — money taken against a booking, one row per payment
 *
 * hotel_rooms keeps only what a room is (name, category, price, status, photo).
 * The `reservation` column is backfilled here and dropped by the migration that
 * follows, so the two steps can be verified separately on a live database.
 */
return new class extends Migration
{
    public function up(): void
    {
        $this->createGuests();
        $this->createBookings();
        $this->createPayments();
        $this->backfillFromReservationJson();
    }

    public function down(): void
    {
        Schema::dropIfExists('hotel_booking_payments');
        Schema::dropIfExists('hotel_bookings');
        Schema::dropIfExists('hotel_guests');
    }

    private function createGuests(): void
    {
        if (Schema::hasTable('hotel_guests')) {
            return;
        }

        Schema::create('hotel_guests', function (Blueprint $table) {
            $table->id('hotel_guest_id');
            $table->string('group_name');
            $table->unsignedBigInteger('faculty_id');
            // nullOnDelete matches every other hotel_* table: they orphan rather than
            // cascade, and there is still no group-deletion feature to exercise it.
            // groups' primary key is group_id, not id — constrained() has to be told,
            // its default guess fails on Postgres.
            $table->foreignId('group_id')->nullable()->constrained('groups', 'group_id')->nullOnDelete();
            $table->string('full_name');
            $table->string('contact_no')->nullable();
            $table->string('email')->nullable();
            // Passport / driver's licence / student number — whatever the desk recorded.
            $table->string('id_number')->nullable();
            $table->timestamps();
            $table->index(['group_name', 'faculty_id']);
            // Verify Guest searches by name, email, contact and ID; name is the common one.
            $table->index(['group_name', 'faculty_id', 'full_name']);
        });

        $this->enableRowLevelSecurity('hotel_guests');
    }

    private function createBookings(): void
    {
        if (Schema::hasTable('hotel_bookings')) {
            return;
        }

        Schema::create('hotel_bookings', function (Blueprint $table) {
            $table->id('hotel_booking_id');
            $table->string('group_name');
            $table->unsignedBigInteger('faculty_id');
            $table->foreignId('group_id')->nullable()->constrained('groups', 'group_id')->nullOnDelete();
            // Unlike the other hotel_* foreign keys these cascade: a booking without its
            // room or its guest is not a record of anything.
            $table->foreignId('hotel_room_id')->constrained('hotel_rooms', 'hotel_room_id')->cascadeOnDelete();
            $table->foreignId('hotel_guest_id')->constrained('hotel_guests', 'hotel_guest_id')->cascadeOnDelete();
            // One of App\Models\HotelBooking::STATUSES.
            $table->string('status')->default('Reserved');
            $table->date('check_in')->nullable();
            // Kept as the typed 'HH:MM' string: it is a wall-clock time on check_in's
            // date, and the 12-hour stay blocks are counted from it.
            $table->string('check_in_time', 10)->nullable();
            $table->date('check_out')->nullable();
            // Nightly rate as it stood when the room was booked. Room Management may
            // reprice the room later; the stay it was already sold at must not move.
            $table->integer('room_rate')->default(0);
            $table->timestamp('reserved_at')->nullable();
            // Front Desk confirmed the guest at the desk (Verify Guest).
            $table->timestamp('arrived_at')->nullable();
            // Room Management handed over the room and set it Occupied.
            $table->timestamp('checked_in_at')->nullable();
            $table->timestamp('checked_out_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->string('booked_by')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->index(['group_name', 'faculty_id']);
            // "the live booking for this room" — the single hottest lookup there is.
            $table->index(['hotel_room_id', 'status']);
            $table->index(['group_name', 'faculty_id', 'status']);
        });

        $this->enableRowLevelSecurity('hotel_bookings');
    }

    private function createPayments(): void
    {
        if (Schema::hasTable('hotel_booking_payments')) {
            return;
        }

        Schema::create('hotel_booking_payments', function (Blueprint $table) {
            $table->id('hotel_booking_payment_id');
            $table->string('group_name');
            $table->unsignedBigInteger('faculty_id');
            $table->foreignId('group_id')->nullable()->constrained('groups', 'group_id')->nullOnDelete();
            $table->foreignId('hotel_booking_id')->constrained('hotel_bookings', 'hotel_booking_id')->cascadeOnDelete();
            // 'Full' or 'Partial' — what the desk said this payment settled.
            $table->string('type')->default('Full');
            // Money is decimal, not integer: a partial payment is typed freehand and
            // the room rate itself is whole pesos, so halves only ever appear here.
            $table->decimal('amount_paid', 12, 2)->default(0);
            // Snapshots at the moment of payment, so a later rate change or an added
            // second payment cannot retroactively rewrite what the guest was told.
            $table->decimal('total_due', 12, 2)->default(0);
            $table->decimal('balance', 12, 2)->default(0);
            $table->string('method')->default('Cash');
            $table->string('reference')->nullable();
            $table->string('payer_name')->nullable();
            $table->text('notes')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();
            $table->index(['group_name', 'faculty_id']);
            $table->index('hotel_booking_id');
        });

        $this->enableRowLevelSecurity('hotel_booking_payments');
    }

    /**
     * Copies every hotel_rooms.reservation blob into the new tables.
     *
     * One blob = one guest + one booking + at most one payment. The room's current
     * status decides the booking's lifecycle state, because that is all the old data
     * recorded: a room back on Available had been checked out (or reset) even though
     * its blob still said "Reserved".
     */
    private function backfillFromReservationJson(): void
    {
        if (!Schema::hasColumn('hotel_rooms', 'reservation')) {
            return;
        }

        // Re-running the migration must not duplicate stays.
        if (DB::table('hotel_bookings')->exists()) {
            return;
        }

        $now = now();

        DB::table('hotel_rooms')
            ->whereNotNull('reservation')
            ->orderBy('hotel_room_id')
            ->chunk(100, function ($rooms) use ($now) {
                foreach ($rooms as $room) {
                    $reservation = $this->decodeReservation($room->reservation);
                    if (!$reservation) {
                        continue;
                    }

                    $guestId = DB::table('hotel_guests')->insertGetId([
                        'group_name' => $room->group_name,
                        'faculty_id' => $room->faculty_id,
                        'group_id'   => $room->group_id ?? null,
                        'full_name'  => $this->str($reservation, 'fullName') ?: 'Guest',
                        'contact_no' => $this->str($reservation, 'contactNo'),
                        'email'      => $this->str($reservation, 'email'),
                        'id_number'  => $this->str($reservation, 'idNumber'),
                        'created_at' => $now,
                        'updated_at' => $now,
                    ], 'hotel_guest_id');

                    $arrived = strtolower($this->str($reservation, 'arrivalStatus') ?? '') === 'arrived';
                    $roomStatus = strtolower((string) $room->status);

                    if ($roomStatus === 'available') {
                        $status = 'Checked Out';
                    } elseif ($roomStatus === 'occupied') {
                        $status = 'Checked In';
                    } elseif ($arrived) {
                        $status = 'Arrived';
                    } else {
                        $status = 'Reserved';
                    }

                    $bookingId = DB::table('hotel_bookings')->insertGetId([
                        'group_name'      => $room->group_name,
                        'faculty_id'      => $room->faculty_id,
                        'group_id'        => $room->group_id ?? null,
                        'hotel_room_id'   => $room->hotel_room_id,
                        'hotel_guest_id'  => $guestId,
                        'status'          => $status,
                        'check_in'        => $this->date($reservation, 'checkIn'),
                        'check_in_time'   => $this->str($reservation, 'checkInTime'),
                        'check_out'       => $this->date($reservation, 'checkOut'),
                        'room_rate'       => (int) ($room->price ?? 0),
                        'reserved_at'     => $this->timestamp($reservation, 'reservedAt') ?? $room->created_at,
                        'arrived_at'      => $this->timestamp($reservation, 'arrivedAt'),
                        'checked_in_at'   => $this->timestamp($reservation, 'checkedInAt'),
                        'checked_out_at'  => $status === 'Checked Out' ? $room->updated_at : null,
                        'cancelled_at'    => null,
                        'booked_by'       => null,
                        'notes'           => null,
                        'created_at'      => $now,
                        'updated_at'      => $now,
                    ], 'hotel_booking_id');

                    $payment = $reservation['payment'] ?? null;
                    if (!is_array($payment)) {
                        continue;
                    }

                    DB::table('hotel_booking_payments')->insert([
                        'group_name'       => $room->group_name,
                        'faculty_id'       => $room->faculty_id,
                        'group_id'         => $room->group_id ?? null,
                        'hotel_booking_id' => $bookingId,
                        'type'             => $this->str($payment, 'type') ?: 'Full',
                        'amount_paid'      => (float) ($payment['amountPaid'] ?? 0),
                        'total_due'        => (float) ($payment['totalDue'] ?? 0),
                        'balance'          => (float) ($payment['balance'] ?? 0),
                        'method'           => $this->str($payment, 'method') ?: 'Cash',
                        'reference'        => $this->str($payment, 'reference'),
                        'payer_name'       => $this->str($payment, 'payerName'),
                        'notes'            => $this->str($payment, 'notes'),
                        'paid_at'          => $this->timestamp($payment, 'paidAt'),
                        'created_at'       => $now,
                        'updated_at'       => $now,
                    ]);
                }
            });
    }

    /** The column is json on Postgres but was longtext on the old MySQL database. */
    private function decodeReservation($raw): ?array
    {
        if (is_array($raw)) {
            return $raw;
        }
        if (!is_string($raw) || trim($raw) === '') {
            return null;
        }

        $decoded = json_decode($raw, true);

        return is_array($decoded) && $decoded !== [] ? $decoded : null;
    }

    private function str(array $source, string $key): ?string
    {
        $value = $source[$key] ?? null;
        if (!is_scalar($value)) {
            return null;
        }
        $value = trim((string) $value);

        return $value === '' ? null : $value;
    }

    private function date(array $source, string $key): ?string
    {
        $value = $this->str($source, $key);

        return $value && preg_match('/^\d{4}-\d{2}-\d{2}/', $value) ? substr($value, 0, 10) : null;
    }

    private function timestamp(array $source, string $key): ?string
    {
        $value = $this->str($source, $key);
        if (!$value) {
            return null;
        }

        try {
            return \Illuminate\Support\Carbon::parse($value)->toDateTimeString();
        } catch (\Throwable $e) {
            return null;
        }
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
