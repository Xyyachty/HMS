<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Carbon;

/**
 * One guest's stay in one room.
 *
 * This is the row that used to be the hotel_rooms.reservation JSON blob. Because it is
 * now its own table a room can have a whole history of stays, past bookings survive the
 * next guest, and Verify Guest can search across every booking instead of walking rooms.
 *
 * Lifecycle, and who moves it:
 *   Booked      Front Desk / the public site took the booking and the payment
 *   Arrived     Front Desk confirmed the guest at the desk (Verify Guest)
 *   Checked In  Room Management handed the room over; the room goes Occupied
 *   Pending Room Inspection
 *               Front Desk started the check-out; Housekeeping inspects the room, and
 *               Front Desk reviews any damage, before the final bill can close it
 *   Checked Out the stay ended; the room goes to Housekeeping for cleaning
 *   Cancelled   the booking was called off before anyone arrived
 */
class HotelBooking extends Model
{
    public const STATUSES = [
        'Booked',
        'Arrived',
        'Checked In',
        'Pending Room Inspection',
        'Checked Out',
        'Cancelled',
    ];

    /** Statuses that still hold the room. Anything else is history. */
    public const OPEN_STATUSES = ['Booked', 'Arrived', 'Checked In', 'Pending Room Inspection'];

    /** Registered but not handed a room yet — what the Guest Details badge counts. */
    public const AWAITING_CHECK_IN_STATUSES = ['Booked', 'Arrived'];

    /** Length of one charged stay block, in hours — matches the front-end's BLOCK_HOURS. */
    public const BLOCK_HOURS = 12;

    protected $primaryKey = 'hotel_booking_id';

    protected $fillable = [
        'group_name',
        'faculty_id',
        'group_id',
        'hotel_room_id',
        'hotel_guest_id',
        'status',
        'check_in',
        'check_in_time',
        'check_out',
        'room_rate',
        'guest_count',
        'reserved_at',
        'arrived_at',
        'checked_in_at',
        'checked_out_at',
        'cancelled_at',
        'booked_by',
        'notes',
        'promo_name',
        'promo_price',
        'promo_blocks',
    ];

    protected $casts = [
        'check_in'       => 'date',
        'check_out'      => 'date',
        'room_rate'      => 'integer',
        'promo_price'    => 'integer',
        'promo_blocks'   => 'integer',
        'guest_count'    => 'integer',
        'reserved_at'    => 'datetime',
        'arrived_at'     => 'datetime',
        'checked_in_at'  => 'datetime',
        'checked_out_at' => 'datetime',
        'cancelled_at'   => 'datetime',
    ];

    public function room(): BelongsTo
    {
        return $this->belongsTo(HotelRoom::class, 'hotel_room_id', 'hotel_room_id');
    }

    public function guest(): BelongsTo
    {
        return $this->belongsTo(HotelGuest::class, 'hotel_guest_id', 'hotel_guest_id');
    }

    public function payments(): HasMany
    {
        return $this->hasMany(HotelBookingPayment::class, 'hotel_booking_id', 'hotel_booking_id');
    }

    /** The room inspection Front Desk asked for when it started this guest's check-out. */
    public function inspection(): HasOne
    {
        return $this->hasOne(HotelRoomInspection::class, 'hotel_booking_id', 'hotel_booking_id')
            ->latestOfMany('hotel_room_inspection_id');
    }

    /** Room-service orders charged to this stay — cancelled ones are not billed. */
    public function foodOrders(): HasMany
    {
        return $this->hasMany(HotelFoodOrder::class, 'hotel_booking_id', 'hotel_booking_id')
            ->billable();
    }

    /** Extras Front Desk added by hand — minibar, laundry, late checkout. */
    public function charges(): HasMany
    {
        return $this->hasMany(HotelBookingCharge::class, 'hotel_booking_id', 'hotel_booking_id')
            ->orderBy('hotel_booking_charge_id');
    }

    /** Housekeeping's add-ons lent to this stay — folding bed, extra towel. */
    public function addons(): HasMany
    {
        return $this->hasMany(HotelBookingAddon::class, 'hotel_booking_id', 'hotel_booking_id')
            ->orderBy('hotel_booking_addon_id');
    }

    /** Bookings that still hold their room. */
    public function scopeOpen(Builder $query): Builder
    {
        return $query->whereIn('status', self::OPEN_STATUSES);
    }

    /** Bookings Room Management has yet to act on. */
    public function scopeAwaitingCheckIn(Builder $query): Builder
    {
        return $query->whereIn('status', self::AWAITING_CHECK_IN_STATUSES);
    }

    /**
     * Guests physically in the hotel right now.
     *
     * Narrower than open(): a Booked stay holds a room but its guest is not here yet, and
     * only someone who has actually checked in may be let into an amenity or billed for
     * one. The room-service route makes the same test inline; this is that rule named.
     */
    public function scopeCheckedIn(Builder $query): Builder
    {
        return $query->where('status', 'Checked In');
    }

    public static function normalizeStatus(?string $value): string
    {
        $raw = mb_strtolower(trim((string) $value));

        // 'Reserved' was this status' name before it was renamed to 'Booked'. The
        // backfill migration rewrote the stored rows, but a stale browser payload
        // or a half-open page can still send the old word, so it stays understood.
        if ($raw === 'reserved') {
            return 'Booked';
        }

        foreach (self::STATUSES as $status) {
            if (mb_strtolower($status) === $raw) {
                return $status;
            }
        }

        return 'Booked';
    }

    public function isOpen(): bool
    {
        return in_array($this->status, self::OPEN_STATUSES, true);
    }

    /**
     * Whether $to is a legal move from $from: forward through the stay, or
     * Cancelled as an exit from anywhere still open. Nothing moves once a booking
     * has reached Checked Out or Cancelled — and 'arrive'/'check_in' called a
     * second time may not demote a guest already further along than that.
     */
    public static function isForwardTransition(string $from, string $to): bool
    {
        if ($from === $to || in_array($from, ['Checked Out', 'Cancelled'], true)) {
            return false;
        }

        if ($to === 'Cancelled') {
            return true;
        }

        $flow = ['Booked', 'Arrived', 'Checked In', 'Pending Room Inspection', 'Checked Out'];
        $fromIndex = array_search($from, $flow, true);
        $toIndex = array_search($to, $flow, true);

        return $fromIndex !== false && $toIndex !== false && $toIndex > $fromIndex;
    }

    /**
     * Booked -> Arrived is the only distinction the desk screens draw, and both
     * Checked In and Checked Out imply the guest turned up.
     */
    public function arrivalStatus(): string
    {
        return $this->arrived_at || in_array($this->status, ['Arrived', 'Checked In', 'Pending Room Inspection', 'Checked Out'], true)
            ? 'Arrived'
            : 'Booked';
    }

    /** Charged 12-hour blocks between check-in and check-out; at least one. */
    public function stayBlocks(): int
    {
        if (!$this->check_in || !$this->check_out) {
            return 1;
        }

        $clock = preg_match('/^\d{1,2}:\d{2}/', (string) $this->check_in_time)
            ? substr((string) $this->check_in_time, 0, 5)
            : '00:00';

        try {
            $start = Carbon::parse($this->check_in->toDateString() . ' ' . $clock);
            $end   = Carbon::parse($this->check_out->toDateString() . ' ' . $clock);
        } catch (\Throwable $e) {
            return 1;
        }

        $hours = $start->diffInMinutes($end, false) / 60;

        return $hours <= 0 ? 1 : max(1, (int) ceil($hours / self::BLOCK_HOURS));
    }

    /** Nights in the stay: two 12-hour blocks each, at least one. Extra beds are billed by it. */
    public function nights(): int
    {
        return max(1, (int) ceil($this->stayBlocks() / 2));
    }

    /**
     * The room charge alone. Deliberately excludes room service: the payments taken at
     * booking time recorded their balance against this figure, and moving it would
     * rewrite what those receipts said the guest still owed.
     *
     * A stay booked under a promo pays the package price for the blocks it was booked
     * for; any block added after that is charged at the room rate.
     */
    public function totalDue(): float
    {
        if ($this->promo_price !== null) {
            return $this->promo_price
                + max(0, $this->stayBlocks() - (int) $this->promo_blocks) * (float) $this->room_rate;
        }

        return $this->stayBlocks() * (float) $this->room_rate;
    }

    public function amountPaid(): float
    {
        return (float) $this->payments->sum('amount_paid');
    }

    /** What this stay ordered from the kitchen, to settle at check-out. */
    public function roomServiceTotal(): float
    {
        return (float) $this->foodOrders->sum('total');
    }

    /** Hand-added extras — minibar, laundry, late checkout. */
    public function otherChargesTotal(): float
    {
        return (float) $this->charges->sum('amount');
    }

    /** Housekeeping add-ons on this stay, at the price they were lent for. */
    public function addonsTotal(): float
    {
        return (float) $this->addons->sum(fn (HotelBookingAddon $addon) => $addon->lineTotal($this->nights()));
    }

    /** Room + room service + add-ons + extras — the figure the guest actually settles. */
    public function grandTotal(): float
    {
        return $this->totalDue()
            + $this->roomServiceTotal()
            + $this->addonsTotal()
            + $this->otherChargesTotal();
    }

    /** Still owed once everything paid so far is counted against the full bill. */
    public function outstanding(): float
    {
        return max(0, $this->grandTotal() - $this->amountPaid());
    }

    /**
     * The shape the room grid, Verify Guest and Guest Details all read as
     * `room.reservation`. Key names are the ones the old JSON blob used, so the
     * front-end kept working unchanged when the blob became these tables.
     */
    public function toReservationArray(): array
    {
        $guest   = $this->guest;
        $payment = $this->payments->sortByDesc('paid_at')->first();

        return [
            'bookingId'     => $this->hotel_booking_id,
            'guestId'       => $this->hotel_guest_id,
            'status'        => $this->status,
            'fullName'      => $guest?->full_name ?? 'Guest',
            'contactNo'     => $guest?->contact_no ?? '',
            'email'         => $guest?->email ?? '',
            'idNumber'      => $guest?->id_number ?? '',
            'checkIn'       => optional($this->check_in)->toDateString(),
            'checkInTime'   => $this->check_in_time ?? '',
            'checkOut'      => optional($this->check_out)->toDateString(),
            'guestCount'    => $this->guest_count,
            'roomRate'      => (int) $this->room_rate,
            'promoName'     => $this->promo_name ?? '',
            'promoPrice'    => $this->promo_price,
            'totalDue'      => $this->totalDue(),
            'amountPaid'    => $this->amountPaid(),
            'balance'       => max(0, $this->totalDue() - $this->amountPaid()),
            // The final bill: room charge + room service + add-ons + hand-added extras.
            'roomServiceTotal' => $this->roomServiceTotal(),
            'roomServiceCount' => $this->foodOrders->count(),
            'addonsTotal'      => $this->addonsTotal(),
            'addonsCount'      => $this->addons->count(),
            'otherCharges'     => $this->otherChargesTotal(),
            'grandTotal'       => $this->grandTotal(),
            'outstanding'      => $this->outstanding(),
            'reservedAt'    => optional($this->reserved_at)->toIso8601String(),
            'arrivalStatus' => $this->arrivalStatus(),
            'arrivedAt'     => optional($this->arrived_at)->toIso8601String(),
            'checkedInAt'   => optional($this->checked_in_at)->toIso8601String(),
            'checkedOutAt'  => optional($this->checked_out_at)->toIso8601String(),
            'bookedBy'      => $this->booked_by ?? '',
            'notes'         => $this->notes ?? '',
            'payment'       => $payment?->toTemplateArray(),
            'payments'      => $this->payments->map->toTemplateArray()->values()->all(),
            // Only a stay being checked out has one to show; asked for no other, so the
            // room list costs no extra query per room.
            'inspection'    => $this->status === 'Pending Room Inspection'
                ? $this->inspection?->load('damages')->toTemplateArray()
                : null,
        ];
    }

    /**
     * Why Front Desk cannot finish this check-out yet, or null when it can. The bill
     * being settled is checked separately, by the check-out route.
     */
    public function checkOutBlocker(): ?string
    {
        if ($this->status !== 'Pending Room Inspection') {
            return 'Start the check-out first, so Housekeeping can inspect the room.';
        }

        $inspection = $this->inspection;
        if (!$inspection || $inspection->status !== 'Reported') {
            return 'Housekeeping has not finished inspecting the room yet.';
        }

        if ($inspection->damages()->where('status', 'Pending Review')->exists()) {
            return 'Review the damage Housekeeping reported before the final bill.';
        }

        return null;
    }

    /**
     * One stay as its guest sees it on My Bookings. Narrower than
     * toReservationArray(): no ID number, no staff notes, no who-booked-it.
     */
    public function toGuestArray(): array
    {
        return [
            'reference'   => $this->hotel_booking_id,
            'status'      => $this->status,
            'roomName'    => $this->room?->name ?? '',
            'roomCategory'=> $this->room?->category ?? '',
            'checkIn'     => optional($this->check_in)->toDateString(),
            'checkInTime' => $this->check_in_time ?? '',
            'checkOut'    => optional($this->check_out)->toDateString(),
            'guestCount'  => $this->guest_count,
            'promoName'   => $this->promo_name ?? '',
            'total'       => $this->grandTotal(),
            'amountPaid'  => $this->amountPaid(),
            'balance'     => $this->outstanding(),
            'charges'     => $this->guestChargeLines(),
            'payments'    => $this->payments->sortBy('paid_at')->map(fn (HotelBookingPayment $p) => [
                'type'   => $p->type,
                'method' => $p->method,
                'amount' => (float) $p->amount_paid,
                'paidAt' => optional($p->paid_at)->toIso8601String(),
            ])->values()->all(),
        ];
    }

    /**
     * Everything on the stay's bill, one line each, for the guest's My Bookings: the
     * room, every dish ordered to the room, the add-ons and the charges Front Desk added.
     * Read off toBillArray() so the guest sees the same figures the desk settles.
     *
     * Each line carries its unit price in `detail` ("3 × ₱1,300"), so the guest can
     * check the sum.
     *
     * @return list<array{kind: string, label: string, detail: string, amount: float}>
     */
    private function guestChargeLines(): array
    {
        $bill = $this->toBillArray();
        $room = $bill['room'];
        $peso = fn ($n) => '₱' . number_format((float) $n, 0);

        $lines = [[
            'kind'   => 'room',
            'label'  => 'Room ' . ($bill['roomName'] ?: ''),
            'detail' => $room['promo']
                ? $room['promo'] . ' package'
                : $room['blocks'] . ' × ' . $peso($room['rate']) . ' per ' . $room['blockHours'] . ' hrs'
                    . ' · ' . $this->nights() . ' night' . ($this->nights() === 1 ? '' : 's'),
            'amount' => (float) $room['subtotal'],
        ]];
        foreach ($bill['roomService']['orders'] as $order) {
            foreach ($order['items'] as $item) {
                $lines[] = [
                    'kind'   => 'food',
                    'label'  => $item['name'],
                    'detail' => $item['qty'] . ' × ' . $peso($item['price']) . ' · ' . $order['status'],
                    'amount' => (float) $item['line'],
                ];
            }
        }
        foreach ($bill['addons']['items'] as $addon) {
            $lines[] = [
                'kind'   => 'addon',
                'label'  => $addon['name'],
                'detail' => $addon['qty'] . ' × ' . $peso($addon['unitPrice'])
                    . ($addon['perNight'] ? ' × ' . $addon['nights'] . ' night' . ($addon['nights'] === 1 ? '' : 's') : ''),
                'amount' => (float) $addon['line'],
            ];
        }
        foreach ($bill['otherCharges']['items'] as $charge) {
            $lines[] = ['kind' => 'charge', 'label' => $charge['description'], 'detail' => '', 'amount' => (float) $charge['amount']];
        }

        return $lines;
    }

    /**
     * The final bill Front Desk settles at check-out: every charge on the stay, itemised,
     * with what has already been paid against it.
     *
     * Built as explicit sections rather than one flat list so the screen can render the
     * subtotals the guest expects to see, and priced from the same methods the rest of
     * the app bills from — nothing here recomputes a total its own way.
     */
    public function toBillArray(): array
    {
        $guest = $this->guest;

        return [
            'bookingId'    => $this->hotel_booking_id,
            'guestName'    => $guest?->full_name ?? 'Guest',
            'contactNo'    => $guest?->contact_no ?? '',
            'email'        => $guest?->email ?? '',
            'idNumber'     => $guest?->id_number ?? '',
            'roomName'     => $this->room?->name ?? '',
            'roomCategory' => $this->room?->category ?? '',
            'status'       => $this->status,

            'checkIn'      => optional($this->check_in)->toDateString(),
            'checkInTime'  => $this->check_in_time ?? '',
            'checkOut'     => optional($this->check_out)->toDateString(),
            // Set only once the stay has actually closed; until then the bill shows the
            // booked departure date instead.
            'checkedOutAt' => optional($this->checked_out_at)->toIso8601String(),

            'room' => [
                // "2 blocks × ₱2,500" — the same 12-hour blocks stayBlocks() charges by.
                'blocks'    => $this->stayBlocks(),
                'blockHours'=> self::BLOCK_HOURS,
                'rate'      => (int) $this->room_rate,
                // Set when the stay was booked under a promo; the subtotal is then its price.
                'promo'     => $this->promo_name,
                'subtotal'  => $this->totalDue(),
            ],

            // One line per dish across every billable order, so the guest sees what they
            // ordered rather than an opaque per-order total.
            'roomService' => [
                'orders'   => $this->foodOrders->map(fn (HotelFoodOrder $order) => [
                    'orderId' => $order->hotel_food_order_id,
                    'status'  => $order->status,
                    'placedAt'=> optional($order->created_at)->toIso8601String(),
                    'total'   => (int) $order->total,
                    'items'   => array_map(fn ($item) => [
                        'name'  => $item['name'] ?? '',
                        'qty'   => (int) ($item['qty'] ?? 0),
                        'price' => (int) ($item['price'] ?? 0),
                        'line'  => (int) ($item['price'] ?? 0) * (int) ($item['qty'] ?? 0),
                    ], $order->items ?? []),
                ])->values()->all(),
                'subtotal' => $this->roomServiceTotal(),
            ],

            // What Housekeeping lent the guest, priced as it was on the day it went out.
            'addons' => [
                'items'    => $this->addons->map(fn (HotelBookingAddon $addon) => $addon->toTemplateArray($this->nights()))->values()->all(),
                'subtotal' => $this->addonsTotal(),
            ],

            'otherCharges' => [
                'items'    => $this->charges->map->toTemplateArray()->values()->all(),
                'subtotal' => $this->otherChargesTotal(),
            ],

            'total'       => $this->grandTotal(),
            'payments'    => $this->payments->sortBy('paid_at')->map->toTemplateArray()->values()->all(),
            'amountPaid'  => $this->amountPaid(),
            'balance'     => $this->outstanding(),
        ];
    }
}
