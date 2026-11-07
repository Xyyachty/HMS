<?php

namespace App\Support;

use App\Exceptions\BookingOverlapException;
use App\Models\HotelAddon;
use App\Models\HotelBooking;
use App\Models\HotelBookingCharge;
use App\Models\HotelBookingDiscount;
use App\Models\HotelBookingPayment;
use App\Models\HotelChildPolicy;
use App\Models\HotelGuest;
use App\Models\HotelInspectionDamage;
use App\Models\HotelPromo;
use App\Models\HotelRoom;
use App\Models\HotelRoomCategory;
use App\Models\StudentGroup;
use Illuminate\Support\Facades\DB;

/**
 * Every write to a booking goes through here.
 *
 * A stay touches three tables at once — booking one writes a guest, a booking and a
 * payment — and checking out moves the booking and the room's housekeeping status
 * together. Keeping that in one place is what stops the room status and the booking
 * lifecycle from drifting apart, which is exactly what happened while the booking
 * lived in a JSON blob on the room.
 */
class HotelBookingDesk
{
    /**
     * Room status each booking transition leaves behind. Reserving and checking in are
     * deliberately absent — occupancy is the booking's own status now (see
     * HotelBooking::status / HotelRoom::activeBooking()), not hotel_rooms.status, so
     * neither of those touches the room at all.
     */
    private const ROOM_STATUS_ON = [
        // Housekeeping takes the room from here; it is not sellable until they clear it.
        'check_out' => 'Cleaning',
        'cancel'    => 'Available',
    ];

    public static function membership(): ?StudentGroup
    {
        $student = auth()->user()?->student;

        return StudentGroupSync::membershipForStudent($student?->user_information_id);
    }

    /** Bookings a team may see, newest first, with everything the screens render. */
    public static function scopedQuery(StudentGroup $membership)
    {
        return HotelBooking::with(['guest', 'payments', 'foodOrders', 'charges', 'addons', 'room'])
            ->where('group_name', $membership->group_name)
            ->where('faculty_id', $membership->faculty_id)
            ->orderByDesc('hotel_booking_id');
    }

    public static function findBooking(StudentGroup $membership, $bookingId): ?HotelBooking
    {
        return self::scopedQuery($membership)
            ->where('hotel_booking_id', $bookingId)
            ->first();
    }

    /**
     * Takes a booking: guest record, stay, and the payment collected up front.
     *
     * $guest keys: full_name, contact_no, email, id_number.
     * $stay  keys: check_in, check_in_time, check_out, guest_count, booked_by, notes,
     *        promo_id (optional: books the stay at that promo's price),
     *        adults + child_ages (optional: the party split; replaces guest_count and
     *        prices each child by the team's HotelChildPolicy).
     * $payment (optional) keys: type, amount_paid, method, reference, payer_name, notes.
     * $addons (optional) lines: [['addon_id' => int, 'qty' => int], ...]. Attaching them
     * here rather than in a second request means a folding bed that ran out takes the
     * whole booking down with it instead of leaving a stay half-equipped.
     */
    public static function reserve(
        StudentGroup $membership,
        HotelRoom $room,
        array $guest,
        array $stay,
        ?array $payment = null,
        array $addons = []
    ): HotelBooking {
        $scope = self::scope($membership);
        [$guestCount, $adults, $ages] = self::party($stay);
        $capacity = self::capacityOf($room);

        // Each guest past what the room sleeps gets a folding bed, added here when the
        // booking did not pick enough of them. attachTo() still checks the stock.
        if ($guestCount !== null && $capacity) {
            $addons = self::withNeededBeds($room, $addons, $guestCount - $capacity);
        }
        // Adults past the room's limit also pay EXTRA_ADULT_RATE a night.
        $extraAdults = self::extraAdults($adults, $capacity);

        // The room's own limit, else its category's "Sleeps N", plus one per extra bed
        // taken with the stay. Neither set takes any party, the same as before the
        // fields existed. Children count here even when they stay free.
        if ($guestCount !== null) {
            $beds = $capacity ? HotelAddon::extraBedsIn($addons) : 0;

            if ($capacity && $guestCount > $capacity + $beds) {
                throw new \RuntimeException(
                    $room->name . ' sleeps ' . $capacity . ' guest' . ($capacity === 1 ? '' : 's')
                    . ($beds ? ' and ' . $beds . ' on extra beds' : '')
                    . ' — that party is too large for it. Add an extra bed or another room.'
                );
            }
        }

        $children = $ages
            ? HotelChildPolicy::forTeam($room->group_name, (int) $room->faculty_id)->price($ages, $adults, $capacity)
            : null;

        $promo = empty($stay['promo_id']) ? null : HotelPromo::bookable(
            $membership->group_name,
            (int) $membership->faculty_id,
            (int) $stay['promo_id'],
            (string) ($stay['check_in'] ?? '')
        );

        $claims = self::checkDiscounts(
            $stay['discounts'] ?? [],
            (int) ($adults ?? 1),
            $ages,
            (string) ($stay['check_in'] ?? ''),
            $promo !== null,
            (string) ($guest['full_name'] ?? '')
        );

        return DB::transaction(function () use ($scope, $room, $guest, $stay, $payment, $addons, $guestCount, $children, $promo, $claims, $extraAdults) {
            $guestModel = HotelGuest::findOrCreateFor($scope, $guest);

            $booking = new HotelBooking($scope + [
                'hotel_room_id'  => $room->hotel_room_id,
                'hotel_guest_id' => $guestModel->hotel_guest_id,
                'status'         => 'Booked',
                'check_in'       => $stay['check_in'] ?? null,
                'check_in_time'  => $stay['check_in_time'] ?? null,
                'check_out'      => $stay['check_out'] ?? null,
                'guest_count'    => $guestCount,
                // Snapshot, like room_rate: a policy changed later must not move a sold stay.
                'children'       => $children,
                'extra_adults'     => $extraAdults ?: null,
                'extra_adult_rate' => $extraAdults ? HotelBooking::EXTRA_ADULT_RATE : null,
                // Snapshot: repricing the room later must not move a sold stay.
                'room_rate'      => (int) $room->price,
                'reserved_at'    => now(),
                'booked_by'      => $stay['booked_by'] ?? null,
                'notes'          => $stay['notes'] ?? null,
            ]);
            if ($promo) {
                // Snapshot, like room_rate. The package covers the stay as booked.
                $booking->fill([
                    'promo_name'   => $promo->name,
                    'promo_price'  => $promo->promo_price,
                    'promo_blocks' => $booking->stayBlocks(),
                ]);
            }
            $booking->save();

            if ($addons) {
                HotelAddonDesk::attachTo($booking, $addons);
            }

            // Before any payment, so a deposit taken now is recorded against the
            // discounted figure the desk quoted.
            if ($claims) {
                $verifiedBy = auth()->user()?->name;
                foreach ($claims as $claim) {
                    HotelBookingDiscount::create($scope + [
                        'hotel_booking_id' => $booking->hotel_booking_id,
                        'verified_at'      => $claim['verified'] ? now() : null,
                        'verified_by'      => $claim['verified'] ? $verifiedBy : null,
                    ] + array_diff_key($claim, ['verified' => true]));
                }
                $booking->recordDiscountAmounts();
            }

            // After the add-ons, and deliberately: addPayment() prices the receipt off
            // totalDue(), which is the room and the children. Add-ons settle at
            // check-out the same way room service does.
            if ($payment !== null) {
                self::addPayment($booking, $payment);
            }

            // The room's own status is housekeeping only — reserving one never touches
            // it. Whether the room is booked, and for which dates, is the calendar's
            // job (HotelRoom::toTemplateArray()'s bookedRanges), not hotel_rooms.status.

            return $booking->fresh(['guest', 'payments', 'addons']);
        });
    }

    /**
     * The Senior Citizen / PWD claims sent with a stay, checked and cleaned, or a
     * RuntimeException naming the first problem. Run on the server whatever the
     * browser already checked.
     *
     * Each claim: type, occupant (adult|child), child_index (child), is_primary,
     * full_name, birth_date (Senior Citizen), id_number, verified.
     *
     * @return list<array<string, mixed>>
     */
    public static function checkDiscounts(array $claims, int $adults, array $ages, string $checkIn, bool $hasPromo, string $bookerName): array
    {
        if (!$claims) {
            return [];
        }
        if (!HotelBookingDiscount::supported()) {
            throw new \RuntimeException('Senior Citizen and PWD discounts are not set up on this server yet. Ask your instructor.');
        }
        // RA 9994 Sec. 4: the guest takes the promo or the statutory discount, whichever
        // is higher, never both.
        if ($hasPromo) {
            throw new \RuntimeException('A promo and a Senior Citizen / PWD discount cannot be combined. Pick the one that saves the guest more.');
        }

        $stayDate = $checkIn !== '' ? \Carbon\Carbon::parse($checkIn) : now('Asia/Manila');
        $clean = [];
        $hashes = [];
        $children = [];
        $adultClaims = 0;
        $primary = 0;

        foreach (array_values($claims) as $n => $claim) {
            $who = 'Eligible guest ' . ($n + 1);
            $type = HotelBookingDiscount::normalizeType($claim['type'] ?? null);
            if (!$type) {
                throw new \RuntimeException($who . ': choose Senior Citizen or PWD.');
            }
            $isPrimary = !empty($claim['is_primary']);
            $name = trim((string) ($isPrimary ? $bookerName : ($claim['full_name'] ?? '')));
            $idNumber = trim((string) ($claim['id_number'] ?? ''));
            if ($name === '') {
                throw new \RuntimeException($who . ': enter their full name.');
            }
            $who = $name;
            if ($idNumber === '') {
                throw new \RuntimeException($who . ': enter the ' . $type . ' ID number.');
            }

            $occupant = ($claim['occupant'] ?? 'adult') === 'child' ? 'child' : 'adult';
            $childIndex = null;
            if ($occupant === 'child') {
                $childIndex = (int) ($claim['child_index'] ?? -1);
                if ($type === 'Senior Citizen') {
                    throw new \RuntimeException($who . ': a child cannot hold a Senior Citizen ID.');
                }
                if ($isPrimary) {
                    throw new \RuntimeException($who . ': the guest making the booking is an adult.');
                }
                if (!array_key_exists($childIndex, $ages)) {
                    throw new \RuntimeException($who . ': pick which child in this room.');
                }
                if (isset($children[$childIndex])) {
                    throw new \RuntimeException('The same child is listed twice for a discount.');
                }
                $children[$childIndex] = true;
            } else {
                $adultClaims++;
            }
            $primary += $isPrimary ? 1 : 0;

            $birthDate = null;
            if ($type === 'Senior Citizen') {
                try {
                    $birthDate = \Carbon\Carbon::parse((string) ($claim['birth_date'] ?? ''));
                } catch (\Throwable $e) {
                    $birthDate = null;
                }
                if (empty($claim['birth_date']) || !$birthDate) {
                    throw new \RuntimeException($who . ': enter their date of birth.');
                }
                $age = $birthDate->diff($stayDate)->y;
                if ($birthDate->greaterThan($stayDate) || $age < HotelBookingDiscount::SENIOR_AGE) {
                    throw new \RuntimeException($who . ' is ' . max(0, $age) . '. The Senior Citizen discount starts at '
                        . HotelBookingDiscount::SENIOR_AGE . '.');
                }
            }

            $hash = HotelBookingDiscount::hashId($type, $idNumber);
            if (isset($hashes[$hash])) {
                throw new \RuntimeException('The same ' . $type . ' ID is listed twice. Each person claims once.');
            }
            $hashes[$hash] = true;

            $clean[] = [
                'type'        => $type,
                'occupant'    => $occupant,
                'child_index' => $childIndex,
                'is_primary'  => $isPrimary,
                'full_name'   => mb_substr($name, 0, 255),
                'birth_date'  => $birthDate?->toDateString(),
                'id_number'   => mb_substr($idNumber, 0, 100),
                'id_hash'     => $hash,
                'verified'    => !empty($claim['verified']),
            ];
        }

        if ($adultClaims > $adults) {
            throw new \RuntimeException('More adults are claiming a discount (' . $adultClaims . ') than are staying in the room (' . $adults . ').');
        }
        if ($primary > 1) {
            throw new \RuntimeException('Only one person is the primary guest.');
        }

        return $clean;
    }

    /**
     * "With Discount" on the Guest Bill: Senior Citizens / PWDs added to a stay after it
     * was booked (a website booking, or one the desk forgot to mark). Front Desk is
     * holding the ID, so each is verified on the spot. Checked by the same rules as at
     * booking, and against the claims already on the stay.
     *
     * @return int how many were added
     */
    public static function addDiscounts(HotelBooking $booking, array $claims, ?string $by): int
    {
        return DB::transaction(function () use ($booking, $claims, $by) {
            HotelBooking::whereKey($booking->hotel_booking_id)->lockForUpdate()->first();
            if (!$booking->isOpen()) {
                throw new \RuntimeException('This stay is closed. Its bill can no longer change.');
            }
            $existing = $booking->discountRows();
            $adults = $booking->adultCount() ?? 1;
            $adultsLeft = max(0, $adults - $existing->where('occupant', 'adult')->count());
            $adultClaims = count(array_filter($claims, fn ($c) => ($c['occupant'] ?? 'adult') !== 'child'));
            if ($adultClaims > $adultsLeft) {
                throw new \RuntimeException($adultsLeft === 0
                    ? 'Every adult on this stay already has a discount.'
                    : 'Only ' . $adultsLeft . ' more adult' . ($adultsLeft === 1 ? '' : 's') . ' on this stay can claim a discount.');
            }

            $clean = self::checkDiscounts(
                $claims,
                $adultsLeft,
                array_column($booking->childLines(), 'age'),
                optional($booking->check_in)->toDateString() ?? '',
                (bool) $booking->promo_name,
                (string) ($booking->guest?->full_name ?? '')
            );
            foreach ($clean as $claim) {
                if ($existing->contains('id_hash', $claim['id_hash'])) {
                    throw new \RuntimeException($claim['full_name'] . ': that ' . $claim['type'] . ' ID is already on this stay.');
                }
                if ($claim['is_primary'] && $existing->contains('is_primary', true)) {
                    throw new \RuntimeException('The guest who booked already has a discount on this stay.');
                }
                if ($claim['occupant'] === 'child' && $existing->where('occupant', 'child')->contains('child_index', $claim['child_index'])) {
                    throw new \RuntimeException($claim['full_name'] . ': that child already has a discount.');
                }
            }

            foreach ($clean as $claim) {
                HotelBookingDiscount::create([
                    'group_name'       => $booking->group_name,
                    'faculty_id'       => $booking->faculty_id,
                    'group_id'         => $booking->group_id,
                    'hotel_booking_id' => $booking->hotel_booking_id,
                    'verified_at'      => now(),
                    'verified_by'      => $by,
                ] + array_diff_key($claim, ['verified' => true]));
            }
            $booking->unsetRelation('discounts');
            $booking->recordDiscountAmounts();

            return count($clean);
        });
    }

    /**
     * Front Desk saw the ID of a guest whose discount was pending. From now on the
     * discount comes off the bill; anything already paid over that is a refund due,
     * recorded as its own payment line, never by rewriting the earlier payment.
     *
     * @throws \RuntimeException when already verified or the stay is closed
     */
    public static function verifyDiscount(HotelBooking $booking, int $discountId, ?string $by): HotelBookingDiscount
    {
        return DB::transaction(function () use ($booking, $discountId, $by) {
            HotelBooking::whereKey($booking->hotel_booking_id)->lockForUpdate()->first();
            $discount = HotelBookingDiscount::where('hotel_booking_id', $booking->hotel_booking_id)
                ->whereKey($discountId)
                ->lockForUpdate()
                ->first();
            if (!$discount) {
                throw new \RuntimeException('That discount is not on this booking.');
            }
            if ($discount->isVerified()) {
                throw new \RuntimeException($discount->full_name . '’s ID was already verified.');
            }
            if (!$booking->isOpen()) {
                throw new \RuntimeException('This stay is closed. Its bill can no longer change.');
            }

            $discount->forceFill(['verified_at' => now(), 'verified_by' => $by])->save();
            $booking->recordDiscountAmounts();

            return $discount;
        });
    }

    /**
     * Records money against a booking. total_due and balance are computed from the
     * booking rather than trusted from the caller, and balance counts every earlier
     * payment so a second partial settles correctly.
     *
     * $payment['transaction_ref'] ties together the rows one payment was split into
     * across several rooms; left out, the payment is its own transaction.
     */
    public static function addPayment(HotelBooking $booking, array $payment): HotelBookingPayment
    {
        return DB::transaction(function () use ($booking, $payment) {
            // Locked so two copies of the same request cannot both pass the check below.
            HotelBooking::whereKey($booking->hotel_booking_id)->lockForUpdate()->first();

            $totalDue = $booking->totalDue();
            $amount   = round(max(0, (float) ($payment['amount_paid'] ?? 0)), 2);
            $type     = HotelBookingPayment::normalizeType($payment['type'] ?? null);

            // Money handed back: a discount verified after the guest paid full price.
            // Its own row with a negative amount, so the earlier payment stays as it was
            // taken and the refund shows on the bill and in the day's takings.
            if ($type === 'Refund') {
                $booking->load(['payments', 'foodOrders', 'charges', 'addons']);
                $due = $booking->refundDue();
                if ($amount <= 0 || $amount > $due + 0.005) {
                    throw new \RuntimeException($due > 0
                        ? 'The refund can be at most PHP ' . number_format($due, 2) . '.'
                        : 'Nothing is owed back to this guest.');
                }
                $amount = -$amount;
            }
            $method   = trim((string) ($payment['method'] ?? '')) ?: 'Cash';
            $reference = self::clean($payment['reference'] ?? null);

            // A double-clicked Pay, or a retried request, sends the same payment twice a
            // moment apart. The second is the first one again, not more money.
            $repeat = $booking->payments()
                ->where('amount_paid', $amount)
                ->where('method', $method)
                ->where('reference', $reference)
                ->where('paid_at', '>=', now()->subSeconds(15))
                ->first();
            if ($repeat) {
                return $repeat;
            }

            $alreadyPaid = $booking->payments()->sum('amount_paid');

            $record = HotelBookingPayment::create([
                'group_name'       => $booking->group_name,
                'faculty_id'       => $booking->faculty_id,
                'group_id'         => $booking->group_id,
                'hotel_booking_id' => $booking->hotel_booking_id,
                'transaction_ref'  => $payment['transaction_ref'] ?? (string) \Illuminate\Support\Str::uuid(),
                'type'             => $type,
                'amount_paid'      => $amount,
                'total_due'        => $totalDue,
                'balance'          => round(max(0, $totalDue - ($alreadyPaid + $amount)), 2),
                'method'           => $method,
                'reference'        => $reference,
                'payer_name'       => self::clean($payment['payer_name'] ?? null),
                'notes'            => self::clean($payment['notes'] ?? null),
                'paid_at'          => now(),
            ]);

            $booking->load('payments');

            return $record;
        });
    }

    /** Front Desk confirmed the guest at the desk. The room does not move yet. */
    public static function markArrived(HotelBooking $booking): HotelBooking
    {
        if ($booking->arrived_at === null) {
            $booking->arrived_at = now();
        }
        $booking->status = 'Arrived';
        $booking->save();

        return $booking;
    }

    /** Room Management handed the room over. */
    public static function checkIn(HotelBooking $booking): HotelBooking
    {
        return DB::transaction(function () use ($booking) {
            // Checking in without a desk confirmation still means the guest turned up.
            if ($booking->arrived_at === null) {
                $booking->arrived_at = now();
            }
            $booking->checked_in_at = $booking->checked_in_at ?? now();
            $booking->status = 'Checked In';
            $booking->save();

            // The room's status does not move here — it is housekeeping-only now.
            // A guest being in the room is read off this booking's own status, not off
            // hotel_rooms.status (see HotelRoom::activeBooking()).

            return $booking;
        });
    }

    /**
     * Front Desk starts the guest's check-out. The stay stays open: Housekeeping
     * inspects the room first, so damage can still go on the final bill.
     */
    public static function requestInspection(HotelBooking $booking): HotelBooking
    {
        return DB::transaction(function () use ($booking) {
            $booking->status = 'Pending Room Inspection';
            // Restaurant Services confirms this check-out's food orders afresh.
            if (HotelBooking::ordersConfirmSupported()) {
                $booking->orders_confirmed_at = null;
                $booking->orders_confirmed_by = null;
            }
            $booking->save();

            HotelHousekeepingDesk::openForCheckout($booking);
            if (HotelBooking::ordersConfirmSupported()) {
                Notifier::checkoutOrdersToConfirm($booking);
            }

            return $booking;
        });
    }

    /**
     * Restaurant Services: every food order on this check-out is delivered and on the
     * bill. Refused while an order is still with the kitchen or on its way.
     */
    public static function confirmOrders(HotelBooking $booking, ?string $by, ?\App\Models\User $actor): HotelBooking
    {
        if (!HotelBooking::ordersConfirmSupported()) {
            throw new \RuntimeException('This step is not set up on the server yet. Try again in a minute.');
        }
        if ($booking->status !== 'Pending Room Inspection') {
            throw new \RuntimeException('This guest is not checking out.');
        }
        $open = $booking->foodOrders()->where('status', '!=', 'Completed')->count();
        if ($open) {
            throw new \RuntimeException($open . ' order' . ($open === 1 ? ' is' : 's are')
                . ' not delivered yet. Deliver and complete ' . ($open === 1 ? 'it' : 'them') . ' first.');
        }
        $booking->forceFill(['orders_confirmed_at' => now(), 'orders_confirmed_by' => $by])->save();
        Notifier::checkoutOrdersConfirmed($actor, $booking);

        return $booking;
    }

    /**
     * Front Desk's decision on one damaged or missing item. Charged adds it to the bill
     * at $amount; Waived adds nothing. An item already decided is refused, so a double
     * click or a second desk cannot bill it twice.
     *
     * @throws \RuntimeException when the item was already reviewed or the stay is closed
     */
    public static function reviewDamage(HotelInspectionDamage $damage, bool $charge, ?float $amount, ?string $by): HotelInspectionDamage
    {
        return DB::transaction(function () use ($damage, $charge, $amount, $by) {
            $damage = HotelInspectionDamage::whereKey($damage->getKey())->lockForUpdate()->first();
            if ($damage->status !== 'Pending Review') {
                throw new \RuntimeException('This item was already reviewed (' . $damage->status . ').');
            }
            if ($damage->booking?->status !== 'Pending Room Inspection') {
                throw new \RuntimeException('This guest is not being checked out any more.');
            }

            if ($charge) {
                $line = HotelBookingCharge::create([
                    'group_name'       => $damage->group_name,
                    'faculty_id'       => $damage->faculty_id,
                    'group_id'         => $damage->group_id,
                    'hotel_booking_id' => $damage->hotel_booking_id,
                    'description'      => 'Damage: ' . $damage->item,
                    'amount'           => round((float) $amount, 2),
                    'added_by'         => $by,
                ]);
                $damage->hotel_booking_charge_id = $line->hotel_booking_charge_id;
                $damage->amount = round((float) $amount, 2);
            }

            $damage->status = $charge ? 'Charged' : 'Waived';
            $damage->reviewed_by = $by;
            $damage->reviewed_at = now();
            $damage->save();

            return $damage;
        });
    }

    /**
     * Ends the stay. This is what the old JSON blob could never do: the booking becomes
     * history instead of lingering on the room, so a checked-out guest stops showing up
     * as orderable in room service.
     */
    public static function checkOut(HotelBooking $booking, ?string $roomStatus = null): HotelBooking
    {
        return DB::transaction(function () use ($booking, $roomStatus) {
            $booking->checked_out_at = $booking->checked_out_at ?? now();
            $booking->status = 'Checked Out';
            $booking->save();

            self::setRoomStatus($booking->room, $roomStatus ?? self::ROOM_STATUS_ON['check_out']);

            // The room was inspected before the bill was settled: that same pass now
            // turns into cleaning. A stay closed without one (a guest who never took the
            // key, released from the room status picker) gets the old after-checkout
            // pass, in the same transaction, so a freed room always has a queue entry.
            // openInspection() no-ops if one is already open for this room.
            $inspection = $booking->inspection()->first();
            if ($inspection && $inspection->status === 'Reported') {
                HotelHousekeepingDesk::readyToClean($inspection, $booking);
            } else {
                HotelHousekeepingDesk::openInspection($booking);
            }

            return $booking;
        });
    }

    public static function cancel(HotelBooking $booking): HotelBooking
    {
        return DB::transaction(function () use ($booking) {
            $booking->cancelled_at = $booking->cancelled_at ?? now();
            $booking->status = 'Cancelled';
            $booking->save();

            self::setRoomStatus($booking->room, self::ROOM_STATUS_ON['cancel']);

            return $booking;
        });
    }

    /**
     * Moves a stay that has not been handed a key yet to another room: an upgrade at
     * the desk, say. The stay is repriced at the new room's rate, and a promo comes off
     * because its package was for the room it was booked in. Payments stay as they are,
     * so an upgrade leaves the difference owing.
     *
     * @throws BookingOverlapException when the new room is taken for those dates
     * @throws \RuntimeException when the stay or the room cannot take the move
     */
    public static function changeRoom(HotelBooking $booking, HotelRoom $room): HotelBooking
    {
        if (!in_array($booking->status, HotelBooking::AWAITING_CHECK_IN_STATUSES, true)) {
            throw new \RuntimeException('Only a guest who is not checked in yet can change rooms.');
        }
        if ($room->hotel_room_id === $booking->hotel_room_id) {
            throw new \RuntimeException('The guest is already booked in ' . $room->name . '.');
        }
        // A guest at the desk walks into the new room now; one still on the way only
        // needs a room that is not out of order.
        if ($room->status === 'Maintenance' || ($booking->status === 'Arrived' && $room->status !== 'Available')) {
            throw new \RuntimeException($room->name . ' is not ready (' . $room->status . '). Pick another room.');
        }

        $capacity = self::capacityOf($room);
        $beds = $booking->addons->filter->isPerNight()->sum('qty');
        if ($capacity && $booking->guest_count && $booking->guest_count > $capacity + $beds) {
            throw new \RuntimeException($room->name . ' sleeps ' . $capacity . ' — too small for '
                . $booking->guest_count . ' guests.');
        }

        // Priced again for the new room, like the room rate: which children fit in its
        // own beds can change with it.
        $ages = array_column($booking->childLines(), 'age');
        $children = $ages
            ? HotelChildPolicy::forTeam($room->group_name, (int) $room->faculty_id)->price($ages, (int) $booking->adultCount(), $capacity)
            : $booking->children;

        $extraAdults = self::extraAdults($booking->adultCount(), $capacity);

        return DB::transaction(function () use ($booking, $room, $children, $extraAdults) {
            // The same lock and overlap test a new booking takes.
            HotelRoom::whereKey($room->hotel_room_id)->lockForUpdate()->first();
            $taken = HotelBooking::open()
                ->where('hotel_room_id', $room->hotel_room_id)
                ->where('check_in', '<', $booking->check_out)
                ->where('check_out', '>', $booking->check_in)
                ->exists();
            if ($taken) {
                throw new BookingOverlapException($room->name);
            }

            $booking->forceFill([
                'hotel_room_id' => $room->hotel_room_id,
                'room_rate'     => (int) $room->price,
                'children'      => $children,
                'extra_adults'     => $extraAdults ?: null,
                'extra_adult_rate' => $extraAdults ? HotelBooking::EXTRA_ADULT_RATE : null,
                'promo_name'    => null,
                'promo_price'   => null,
                'promo_blocks'  => null,
            ])->save();

            return $booking->setRelation('room', $room);
        });
    }

    /**
     * Front Desk correcting who is in the party: a child who was not mentioned at
     * booking, an age given wrong. The children are priced again under the team's
     * policy as it stands now. Payments are left as they were, so the balance is
     * simply the new total less what was already paid.
     *
     * @throws \RuntimeException when the stay is closed or the room is too small
     */
    public static function updateOccupancy(HotelBooking $booking, int $adults, array $ages): HotelBooking
    {
        if (!in_array($booking->status, ['Booked', 'Arrived', 'Checked In'], true)) {
            throw new \RuntimeException('The party can only be changed before check-out starts.');
        }

        $room = $booking->room;
        $capacity = self::capacityOf($room);
        $beds = $booking->addons->filter->isPerNight()->sum('qty');
        [$guestCount, $adults, $ages] = self::party(['adults' => $adults, 'child_ages' => $ages]);
        // A guest added past the beds gets a folding bed, as at booking.
        $newBeds = $capacity ? self::withNeededBeds($room, [], $guestCount - $capacity - $beds) : [];
        $beds += array_sum(array_column($newBeds, 'qty'));
        if ($capacity && $guestCount > $capacity + $beds) {
            throw new \RuntimeException($room->name . ' sleeps ' . $capacity
                . ($beds ? ' and ' . $beds . ' on extra beds' : '') . ' — too small for ' . $guestCount . ' guests.');
        }

        $extraAdults = self::extraAdults($adults, $capacity);
        DB::transaction(function () use ($booking, $room, $capacity, $guestCount, $adults, $ages, $newBeds, $extraAdults) {
            HotelAddonDesk::attachTo($booking, $newBeds);
            $booking->forceFill([
                'guest_count' => $guestCount,
                'children'    => $ages
                    ? HotelChildPolicy::forTeam($room->group_name, (int) $room->faculty_id)->price($ages, $adults, $capacity)
                    : null,
                'extra_adults'     => $extraAdults ?: null,
                'extra_adult_rate' => $extraAdults ? HotelBooking::EXTRA_ADULT_RATE : null,
            ])->save();
        });
        $booking->load('addons');

        return $booking;
    }

    /**
     * The party from a stay's keys: [guest_count, adults, child ages]. adults and
     * child_ages win when sent; a caller sending only guest_count (an older screen)
     * books that many adults, as before.
     */
    private static function party(array $stay): array
    {
        if (isset($stay['adults']) || !empty($stay['child_ages'])) {
            $ages = array_map(
                fn ($age) => max(0, min(HotelChildPolicy::MAX_CHILD_AGE, (int) $age)),
                array_values((array) ($stay['child_ages'] ?? []))
            );
            $adults = max(1, (int) ($stay['adults'] ?? 1));

            return [$adults + count($ages), $adults, $ages];
        }

        $count = isset($stay['guest_count']) ? max(1, (int) $stay['guest_count']) : null;

        return [$count, $count, []];
    }

    /** Adults past what the room sleeps; 0 when it has no limit or the party was never asked. */
    private static function extraAdults(?int $adults, int $capacity): int
    {
        return $capacity && $adults ? max(0, $adults - $capacity) : 0;
    }

    /**
     * $lines plus the team's folding bed, enough of them for $overflow guests past the
     * room's limit. Unchanged when the lines already carry enough beds or the team
     * lends none; the capacity check then says so.
     */
    private static function withNeededBeds(HotelRoom $room, array $lines, int $overflow): array
    {
        $need = $overflow - HotelAddon::extraBedsIn($lines);
        if ($need <= 0) {
            return $lines;
        }

        $bed = HotelAddon::where('group_name', $room->group_name)
            ->where('faculty_id', $room->faculty_id)
            ->orderBy('hotel_addon_id')
            ->get()
            ->first(fn (HotelAddon $addon) => $addon->isExtraBed());

        return $bed ? array_merge($lines, [['addon_id' => $bed->hotel_addon_id, 'qty' => $need]]) : $lines;
    }

    /** What the room sleeps without extra beds: its own limit, else its category's. 0 = no limit. */
    private static function capacityOf(HotelRoom $room): int
    {
        return (int) ($room->capacity ?: HotelRoomCategory::where('group_name', $room->group_name)
            ->where('faculty_id', $room->faculty_id)
            ->where('name', $room->category)
            ->value('capacity'));
    }

    /**
     * Applies a room status change coming from the status picker, closing the open
     * booking when the room is released. Room Management resetting a room to Available
     * used to leave the reservation blob behind for the next guest to inherit.
     */
    public static function applyRoomStatus(HotelRoom $room, string $status): HotelRoom
    {
        $status = HotelRoom::normalizeStatus($status);
        $booking = $room->activeBooking()->with(['guest', 'payments'])->first();

        if ($booking && in_array($status, ['Available', 'Cleaning', 'Maintenance'], true)) {
            // A guest who has the key leaves through Front Desk's check-out, so the room
            // is inspected and the damage billed before the stay closes.
            if (in_array($booking->status, ['Checked In', 'Pending Room Inspection'], true)) {
                throw new \RuntimeException(
                    ($booking->guest?->full_name ?? 'A guest') . ' is still in ' . $room->name
                    . '. Front Desk checks them out after Housekeeping inspects the room.'
                );
            }

            // Nobody ever arrived: the booking was called off, not completed.
            if ($booking->arrivalStatus() === 'Booked') {
                self::cancel($booking);
            } else {
                self::checkOut($booking, $status);
            }
        }

        self::setRoomStatus($room, $status);

        return $room;
    }

    private static function setRoomStatus(?HotelRoom $room, string $status): void
    {
        if (!$room) {
            return;
        }

        $room->status = HotelRoom::normalizeStatus($status);
        $room->save();
    }

    /** group_name / faculty_id / group_id, the scoping every hotel_* table carries. */
    private static function scope(StudentGroup $membership): array
    {
        return [
            'group_name' => $membership->group_name,
            'faculty_id' => $membership->faculty_id,
            'group_id'   => $membership->group_id,
        ];
    }

    private static function clean($value): ?string
    {
        $value = trim((string) $value);

        return $value === '' ? null : $value;
    }
}
