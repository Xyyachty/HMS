<?php

namespace App\Support;

use App\Exceptions\BookingOverlapException;
use App\Models\HotelAddon;
use App\Models\HotelBooking;
use App\Models\HotelBookingPayment;
use App\Models\HotelGuest;
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
     *        promo_id (optional: books the stay at that promo's price).
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
        $guestCount = isset($stay['guest_count']) ? max(1, (int) $stay['guest_count']) : null;

        // The room's own limit, else its category's "Sleeps N", plus one per extra bed
        // taken with the stay. Neither set takes any party, the same as before the
        // fields existed.
        if ($guestCount !== null) {
            $capacity = (int) ($room->capacity ?: HotelRoomCategory::where('group_name', $room->group_name)
                ->where('faculty_id', $room->faculty_id)
                ->where('name', $room->category)
                ->value('capacity'));
            $beds = $capacity ? HotelAddon::extraBedsIn($addons) : 0;

            if ($capacity && $guestCount > $capacity + $beds) {
                throw new \RuntimeException(
                    $room->name . ' sleeps ' . $capacity . ' guest' . ($capacity === 1 ? '' : 's')
                    . ($beds ? ' and ' . $beds . ' on extra beds' : '')
                    . ' — that party is too large for it. Add an extra bed or another room.'
                );
            }
        }

        $promo = empty($stay['promo_id']) ? null : HotelPromo::bookable(
            $membership->group_name,
            (int) $membership->faculty_id,
            (int) $stay['promo_id'],
            (string) ($stay['check_in'] ?? '')
        );

        return DB::transaction(function () use ($scope, $room, $guest, $stay, $payment, $addons, $guestCount, $promo) {
            $guestModel = HotelGuest::findOrCreateFor($scope, $guest);

            $booking = new HotelBooking($scope + [
                'hotel_room_id'  => $room->hotel_room_id,
                'hotel_guest_id' => $guestModel->hotel_guest_id,
                'status'         => 'Booked',
                'check_in'       => $stay['check_in'] ?? null,
                'check_in_time'  => $stay['check_in_time'] ?? null,
                'check_out'      => $stay['check_out'] ?? null,
                'guest_count'    => $guestCount,
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

            // After the add-ons, and deliberately: addPayment() prices the receipt off
            // totalDue(), which is the room alone. Add-ons settle at check-out the same
            // way room service does.
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
     * Records money against a booking. total_due and balance are computed from the
     * booking rather than trusted from the caller, and balance counts every earlier
     * payment so a second partial settles correctly.
     */
    public static function addPayment(HotelBooking $booking, array $payment): HotelBookingPayment
    {
        $totalDue = $booking->totalDue();
        $amount   = round(max(0, (float) ($payment['amount_paid'] ?? 0)), 2);
        $alreadyPaid = $booking->payments()->sum('amount_paid');

        $record = HotelBookingPayment::create([
            'group_name'       => $booking->group_name,
            'faculty_id'       => $booking->faculty_id,
            'group_id'         => $booking->group_id,
            'hotel_booking_id' => $booking->hotel_booking_id,
            'type'             => HotelBookingPayment::normalizeType($payment['type'] ?? null),
            'amount_paid'      => $amount,
            'total_due'        => $totalDue,
            'balance'          => round(max(0, $totalDue - ($alreadyPaid + $amount)), 2),
            'method'           => trim((string) ($payment['method'] ?? '')) ?: 'Cash',
            'reference'        => self::clean($payment['reference'] ?? null),
            'payer_name'       => self::clean($payment['payer_name'] ?? null),
            'notes'            => self::clean($payment['notes'] ?? null),
            'paid_at'          => now(),
        ]);

        $booking->load('payments');

        return $record;
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

            // Housekeeping's pass starts the moment the stay ends, in the same
            // transaction, so a freed room can never exist without a queue entry for
            // it. openInspection() no-ops if one is already open for this room.
            HotelHousekeepingDesk::openInspection($booking);

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

        $capacity = (int) ($room->capacity ?: HotelRoomCategory::where('group_name', $room->group_name)
            ->where('faculty_id', $room->faculty_id)
            ->where('name', $room->category)
            ->value('capacity'));
        $beds = $booking->addons->filter->isPerNight()->sum('qty');
        if ($capacity && $booking->guest_count && $booking->guest_count > $capacity + $beds) {
            throw new \RuntimeException($room->name . ' sleeps ' . $capacity . ' — too small for '
                . $booking->guest_count . ' guests.');
        }

        return DB::transaction(function () use ($booking, $room) {
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
                'promo_name'    => null,
                'promo_price'   => null,
                'promo_blocks'  => null,
            ])->save();

            return $booking->setRelation('room', $room);
        });
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
