<?php

namespace App\Support;

use App\Models\ActivityLog;
use App\Models\HotelAmenity;
use App\Models\HotelAmenityPayment;
use App\Models\HotelAmenityReservation;
use App\Models\HotelAmenityService;
use App\Models\HotelBooking;
use App\Models\HotelBookingCharge;
use App\Models\HotelCateringPackage;
use App\Models\StudentGroup;
use App\Models\User;
use Illuminate\Support\Facades\DB;

/**
 * Every write to a spa appointment or a function room event.
 *
 * Both kinds go through here because both do the same dangerous thing: hold a slot nobody
 * else may have. The conflict check therefore runs INSIDE the transaction, after locking
 * the amenity row — a check made before the transaction opens is a check two Front Desk
 * tabs can both pass, and locking the existing reservations would not help, because
 * Postgres takes no lock over the row that has not been inserted yet.
 *
 * Booking an event also reaches into two other departments — Housekeeping gets a
 * preparation request, Restaurant Services gets a catering ticket — and both happen in
 * that same transaction, so an event that promised catering can never exist without the
 * order that delivers it.
 */
class HotelAmenityReservationDesk
{
    /** Reservations for one team, newest first, with everything the screens render. */
    public static function scopedQuery(StudentGroup $membership)
    {
        return HotelAmenityReservation::with(['service', 'cateringPackage', 'payments', 'cateringOrders'])
            ->where('group_name', $membership->group_name)
            ->where('faculty_id', $membership->faculty_id)
            ->orderByDesc('hotel_amenity_reservation_id');
    }

    /**
     * Whether $start–$end on $date clashes with something already holding the facility.
     *
     * Half-open intervals, the same test the room-booking guard makes: a booking that ends
     * at 14:00 and one that starts at 14:00 do not overlap, so the desk can sell
     * back-to-back slots. Cancelled and Completed reservations are not in the way.
     */
    public static function hasConflict(
        HotelAmenity $amenity,
        string $date,
        string $start,
        string $end,
        ?int $ignoreId = null
    ): bool {
        return HotelAmenityReservation::where('hotel_amenity_id', $amenity->hotel_amenity_id)
            ->whereDate('scheduled_on', $date)
            ->holding()
            ->when($ignoreId, fn ($q) => $q->where('hotel_amenity_reservation_id', '!=', $ignoreId))
            ->where('starts_at', '<', $end)
            ->where('ends_at', '>', $start)
            ->exists();
    }

    /**
     * Everything holding one facility on one day, in order — what the desk's schedule
     * strip and the function room calendar render.
     */
    public static function scheduleFor(HotelAmenity $amenity, string $date)
    {
        return HotelAmenityReservation::with(['service', 'cateringPackage', 'payments', 'cateringOrders'])
            ->where('hotel_amenity_id', $amenity->hotel_amenity_id)
            ->whereDate('scheduled_on', $date)
            ->orderBy('starts_at')
            ->get();
    }

    /**
     * Take a booking.
     *
     * $data keys: kind, customer_name, contact_no, email, scheduled_on, starts_at,
     * ends_at, special_requests, hotel_booking_id, charge_to_room, additional_fee,
     * additional_note; appointment adds hotel_amenity_service_id; event adds event_type,
     * guest_count, package, hotel_catering_package_id.
     */
    public static function book(HotelAmenity $amenity, array $data, ?User $actor): HotelAmenityReservation
    {
        $kind = HotelAmenityReservation::normalizeKind($data['kind'] ?? null);

        // The facility has to be the kind you book, and it has to be usable.
        if ($amenity->access_type !== $kind) {
            throw new \RuntimeException(
                $amenity->name . ' is ' . mb_strtolower($amenity->accessLabel())
                    . ' — it is not booked that way.'
            );
        }
        if ($amenity->status !== 'Available') {
            throw new \RuntimeException($amenity->name . ' is ' . mb_strtolower($amenity->status) . '.');
        }

        $date = trim((string) ($data['scheduled_on'] ?? ''));
        $start = HotelAmenity::normalizeTime($data['starts_at'] ?? null);

        $service = null;
        $package = null;
        $end = HotelAmenity::normalizeTime($data['ends_at'] ?? null);

        if ($kind === 'appointment') {
            $service = self::resolveService($amenity, $data['hotel_amenity_service_id'] ?? null);
            // The end of an appointment is the service's length, never a typed-in figure:
            // a 60 minute massage cannot be sold into a 30 minute gap by editing a box.
            $end = $start ? self::addMinutes($start, (int) $service->duration_minutes) : null;
        }

        if ($start === null || $end === null || $date === '') {
            throw new \RuntimeException('Pick a date and a start time.');
        }
        if ($end <= $start) {
            throw new \RuntimeException('The end time has to be after the start time.');
        }
        if (!self::withinOpeningHours($amenity, $start, $end)) {
            $hours = $amenity->hoursLabel();
            throw new \RuntimeException(
                $amenity->name . ' is not open then.' . ($hours ? ' Open ' . $hours . '.' : '')
            );
        }

        $booking = self::resolveBooking($amenity, $data['hotel_booking_id'] ?? null);
        $chargeToRoom = $booking !== null && !empty($data['charge_to_room']);

        /* ── event-only inputs ──────────────────────────────────────────── */
        $packageName = null;
        $guests = null;
        $venueFee = 0.0;
        $setupFee = 0.0;
        $serviceFee = 0.0;

        if ($kind === 'event') {
            $packageName = HotelAmenityReservation::normalizePackage($data['package'] ?? null);
            $guests = max(1, (int) ($data['guest_count'] ?? 1));

            if ($amenity->capacity !== null && $guests > $amenity->capacity) {
                throw new \RuntimeException(
                    $amenity->name . ' seats ' . $amenity->capacity . ' — that party is too large for it.'
                );
            }

            // Priced off Housekeeping's rate card, not off anything the browser sent.
            $venueFee = (float) $amenity->rate;
            if (in_array($packageName, HotelAmenityReservation::SETUP_PACKAGES, true)) {
                $setupFee = (float) $amenity->setup_fee;
            }
            if (in_array($packageName, HotelAmenityReservation::CATERING_PACKAGES, true)) {
                $package = self::resolvePackage($amenity, $data['hotel_catering_package_id'] ?? null, $guests);
            }
        } else {
            $serviceFee = (float) $service->price;
        }

        return DB::transaction(function () use (
            $amenity, $data, $actor, $kind, $date, $start, $end, $service, $package,
            $packageName, $guests, $venueFee, $setupFee, $serviceFee, $booking, $chargeToRoom
        ) {
            // Lock the facility, then check. Doing it the other way round is the race:
            // two desks both read an empty afternoon and both sell it.
            HotelAmenity::whereKey($amenity->hotel_amenity_id)->lockForUpdate()->first();

            if (self::hasConflict($amenity, $date, $start, $end)) {
                throw new \RuntimeException('That time is already taken. Pick another slot.');
            }

            $reservation = HotelAmenityReservation::create([
                'group_name'               => $amenity->group_name,
                'faculty_id'               => $amenity->faculty_id,
                'group_id'                 => $amenity->group_id,
                'hotel_amenity_id'         => $amenity->hotel_amenity_id,
                'hotel_amenity_service_id' => $service?->hotel_amenity_service_id,
                'hotel_booking_id'         => $booking?->hotel_booking_id,
                'kind'                     => $kind,
                // Snapshot, like hotel_amenity_visits: renaming the hall must not rewrite
                // what last month's contract said.
                'amenity_name'             => $amenity->name,
                'customer_name'            => trim((string) ($data['customer_name'] ?? ''))
                    ?: ($booking?->guest?->full_name ?: 'Guest'),
                'contact_no'               => self::clean($data['contact_no'] ?? null),
                'email'                    => self::clean($data['email'] ?? null),
                'scheduled_on'             => $date,
                'starts_at'                => $start,
                'ends_at'                  => $end,
                'status'                   => 'Pending',
                'special_requests'         => self::clean($data['special_requests'] ?? null),
                'event_type'               => $kind === 'event' ? self::clean($data['event_type'] ?? null) : null,
                'guest_count'              => $guests,
                'package'                  => $packageName,
                'hotel_catering_package_id' => $package?->hotel_catering_package_id,
                'housekeeping_status'      => $kind === 'event' ? 'For Preparation' : null,
                'venue_fee'                => $venueFee,
                'setup_fee'                => $setupFee,
                'service_fee'              => $serviceFee,
                'additional_fee'           => round(max(0, (float) ($data['additional_fee'] ?? 0)), 2),
                'additional_note'          => self::clean($data['additional_note'] ?? null),
                'charge_to_room'           => $chargeToRoom,
                'booked_by'                => $actor?->name,
            ]);

            // A handle the desk can read out over the phone. Written after the insert
            // because it carries the id.
            $reservation->reference = ($kind === 'event' ? 'EVT-' : 'SPA-')
                . str_pad((string) $reservation->hotel_amenity_reservation_id, 6, '0', STR_PAD_LEFT);
            $reservation->save();

            // Restaurant Services find out here, not by being told later.
            if ($package) {
                HotelCateringDesk::openOrder($reservation, $package, $actor?->name);
                Notifier::cateringOrderReceived($actor, $reservation);
            }

            // So does Housekeeping, when the package includes setting the room up.
            if ($setupFee > 0 || $kind === 'event') {
                Notifier::amenityEventBooked($actor, $reservation);
            }

            ActivityLog::record(
                $actor,
                ActivityLog::TABLE_ASSIGNED,
                'Booked ' . $amenity->name . ' for ' . $reservation->customer_name
                    . ' on ' . $date . ' ' . $reservation->timeLabel() . '.'
            );

            return $reservation->fresh(['service', 'cateringPackage', 'payments', 'cateringOrders']);
        });
    }

    /**
     * Take money against a booking.
     *
     * total_due and balance are computed here from the reservation and never trusted from
     * the browser, the same call HotelBookingDesk::addPayment() makes — a receipt is a
     * claim about what was owed, and the client does not get to make it.
     */
    public static function addPayment(
        HotelAmenityReservation $reservation,
        array $payment,
        ?User $actor
    ): HotelAmenityPayment {
        $amount = round(max(0, (float) ($payment['amount_paid'] ?? 0)), 2);
        if ($amount <= 0) {
            throw new \RuntimeException('Enter how much was paid.');
        }

        $totalDue = $reservation->grandTotal();
        $alreadyPaid = $reservation->amountPaid();

        $record = HotelAmenityPayment::create([
            'group_name'                   => $reservation->group_name,
            'faculty_id'                   => $reservation->faculty_id,
            'group_id'                     => $reservation->group_id,
            'hotel_amenity_reservation_id' => $reservation->hotel_amenity_reservation_id,
            'type'                         => HotelAmenityPayment::normalizeType($payment['type'] ?? null),
            'amount_paid'                  => $amount,
            'total_due'                    => $totalDue,
            'balance'                      => round(max(0, $totalDue - ($alreadyPaid + $amount)), 2),
            'method'                       => HotelAmenityPayment::normalizeMethod($payment['method'] ?? null),
            'reference'                    => self::clean($payment['reference'] ?? null),
            'payer_name'                   => self::clean($payment['payer_name'] ?? null) ?: $reservation->customer_name,
            'notes'                        => self::clean($payment['notes'] ?? null),
            'collected_by'                 => $actor?->name,
            'paid_at'                      => now(),
        ]);

        $reservation->load('payments');

        return $record;
    }

    /**
     * Move the whole bill onto a checked-in guest's room account.
     *
     * One hotel_booking_charges row per component, so the guest's final bill itemises
     * rather than showing one lump. Nothing else has to change for this to reach the
     * folio: HotelBooking::grandTotal() already sums those charges, and the check-out
     * route already refuses to release a guest with an outstanding balance.
     */
    public static function chargeToRoom(HotelAmenityReservation $reservation, ?User $actor): HotelAmenityPayment
    {
        if ($reservation->isPostedToFolio()) {
            throw new \RuntimeException('This booking is already on the guest\'s room account.');
        }

        $booking = $reservation->booking;
        if (!$booking) {
            throw new \RuntimeException('This booking is not tied to a hotel guest, so there is no room to charge.');
        }
        if ($booking->status !== 'Checked In') {
            throw new \RuntimeException('That guest is not checked in, so nothing can go on their room account.');
        }

        $outstanding = $reservation->outstanding();
        if ($outstanding <= 0) {
            throw new \RuntimeException('There is nothing left to charge — this booking is settled.');
        }

        return DB::transaction(function () use ($reservation, $booking, $outstanding, $actor) {
            $alreadyPaid = $reservation->amountPaid();

            $post = function (string $description, float $amount) use ($reservation, $booking, $actor) {
                HotelBookingCharge::create([
                    'group_name'       => $reservation->group_name,
                    'faculty_id'       => $reservation->faculty_id,
                    'group_id'         => $reservation->group_id,
                    'hotel_booking_id' => $booking->hotel_booking_id,
                    'description'      => $description,
                    'amount'           => $amount,
                    'added_by'         => $actor?->name,
                ]);
            };

            // The real figures, line by line. Spreading the outstanding balance across
            // them proportionally would balance, but it would print "Function Room =
            // 2,560.98" — a number that is not what the function room costs and that
            // nobody could check against the contract.
            foreach ($reservation->folioLines() as $line) {
                if ($line['amount'] > 0) {
                    $post($line['description'], $line['amount']);
                }
            }

            // What they have already handed over comes off as its own line, the way a
            // folio actually reads. The sum still equals the outstanding balance.
            if ($alreadyPaid > 0) {
                $post(
                    'Less: paid on ' . ($reservation->reference ?: 'booking'),
                    -1 * round($alreadyPaid, 2)
                );
            }

            $reservation->charge_to_room = true;
            $reservation->posted_to_folio_at = now();
            $reservation->save();

            // Recorded as a payment so the reservation reads settled, without pretending
            // cash crossed the desk. See HotelAmenityPayment::TYPES.
            $record = self::addPayment($reservation, [
                'type'        => 'Charged to Room',
                'amount_paid' => $outstanding,
                'method'      => 'Room Account',
                'reference'   => 'Room ' . ($booking->room?->name ?? ''),
                'notes'       => 'Posted to the guest\'s final bill.',
            ], $actor);

            ActivityLog::record(
                $actor,
                ActivityLog::COMPLAINT_RESOLVED,
                'Charged ' . $reservation->reference . ' (PHP ' . number_format($outstanding, 2)
                    . ') to room ' . ($booking->room?->name ?? '?') . '.'
            );

            return $record;
        });
    }

    /** Pending to Confirmed to In Progress to Completed, or Cancelled as the exit. */
    public static function advance(HotelAmenityReservation $reservation, string $next, ?User $actor): HotelAmenityReservation
    {
        $next = HotelAmenityReservation::normalizeStatus($next);

        if (!HotelAmenityReservation::isForwardTransition($reservation->status, $next)) {
            throw new \RuntimeException(
                $reservation->status . ' cannot go back to ' . $next . '. Status only moves forward.'
            );
        }

        $reservation->status = $next;
        $reservation->save();

        if ($next === 'Cancelled') {
            // The kitchen stops cooking for an event nobody is holding any more. Only the
            // tickets they have not started; past that they have bought food.
            foreach ($reservation->cateringOrders as $order) {
                if (in_array($order->status, ['Pending', 'Confirmed'], true)) {
                    $order->status = 'Cancelled';
                    $order->save();
                }
            }
        }

        ActivityLog::record(
            $actor,
            ActivityLog::TABLE_CLOSED,
            $reservation->reference . ' (' . $reservation->amenity_name . ') is now ' . $next . '.'
        );

        return $reservation;
    }

    /** Housekeeping's turnaround for one event. Forward only, like everything else. */
    public static function advanceHousekeeping(
        HotelAmenityReservation $reservation,
        string $next,
        ?User $actor
    ): HotelAmenityReservation {
        if ($reservation->kind !== 'event') {
            throw new \RuntimeException('Only a function room booking has a preparation status.');
        }

        $next = HotelAmenityReservation::normalizeHousekeepingStatus($next);

        if (!HotelAmenityReservation::isForwardHousekeeping($reservation->housekeeping_status, $next)) {
            throw new \RuntimeException(
                $reservation->housekeeping_status . ' cannot go back to ' . $next . '. It only moves forward.'
            );
        }

        $reservation->housekeeping_status = $next;
        $reservation->save();

        ActivityLog::record(
            $actor,
            ActivityLog::ROOM_READY,
            $reservation->amenity_name . ' for ' . $reservation->reference . ' is now ' . $next . '.'
        );

        return $reservation;
    }

    /* ── helpers ────────────────────────────────────────────────────────── */

    private static function resolveService(HotelAmenity $amenity, $serviceId): HotelAmenityService
    {
        $service = HotelAmenityService::where('hotel_amenity_service_id', (int) $serviceId)
            ->where('hotel_amenity_id', $amenity->hotel_amenity_id)
            ->first();

        if (!$service) {
            throw new \RuntimeException('Choose which service the guest is booking.');
        }

        return $service;
    }

    private static function resolvePackage(HotelAmenity $amenity, $packageId, int $guests): HotelCateringPackage
    {
        $package = HotelCateringPackage::where('hotel_catering_package_id', (int) $packageId)
            ->where('group_name', $amenity->group_name)
            ->where('faculty_id', $amenity->faculty_id)
            ->first();

        if (!$package) {
            throw new \RuntimeException('Choose a catering package from Restaurant Services.');
        }
        if ($guests < (int) $package->min_guests) {
            throw new \RuntimeException(
                $package->name . ' is for ' . $package->min_guests . ' guests or more.'
            );
        }

        return $package;
    }

    /**
     * The stay to bill this to, if there is one. A function room is very often booked by
     * somebody who is not staying here at all, so null is a normal answer.
     */
    private static function resolveBooking(HotelAmenity $amenity, $bookingId): ?HotelBooking
    {
        if (!$bookingId) {
            return null;
        }

        $booking = HotelBooking::with(['guest', 'room'])
            ->where('hotel_booking_id', (int) $bookingId)
            ->where('group_name', $amenity->group_name)
            ->where('faculty_id', $amenity->faculty_id)
            ->first();

        if (!$booking) {
            throw new \RuntimeException('That guest could not be found.');
        }
        if ($booking->status !== 'Checked In') {
            throw new \RuntimeException('That guest has not checked in yet.');
        }

        return $booking;
    }

    /**
     * Both ends inside the facility's posted hours. A facility that posts none is treated
     * as always open, the same call HotelAmenity::isOpenNow() makes.
     */
    private static function withinOpeningHours(HotelAmenity $amenity, string $start, string $end): bool
    {
        $opens = HotelAmenity::normalizeTime($amenity->opens_at);
        $closes = HotelAmenity::normalizeTime($amenity->closes_at);

        if ($opens === null && $closes === null) {
            return true;
        }

        // An overnight window is not checkable as a plain string range, and a booking that
        // straddles midnight is booked as two days anyway. Let it through rather than
        // refusing something legitimate on a technicality.
        if ($opens !== null && $closes !== null && $opens > $closes) {
            return true;
        }

        return ($opens === null || $start >= $opens) && ($closes === null || $end <= $closes);
    }

    /** 'HH:MM' plus N minutes, clamped to the end of the day. */
    private static function addMinutes(string $time, int $minutes): string
    {
        [$h, $m] = array_map('intval', explode(':', $time));
        $total = min(23 * 60 + 59, $h * 60 + $m + max(0, $minutes));

        return str_pad((string) intdiv($total, 60), 2, '0', STR_PAD_LEFT)
            . ':' . str_pad((string) ($total % 60), 2, '0', STR_PAD_LEFT);
    }

    private static function clean($value): ?string
    {
        $value = trim((string) $value);

        return $value === '' ? null : $value;
    }
}
