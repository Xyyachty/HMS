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

    /** Nightly fee per adult past what the room sleeps; the folding bed is billed on its own. */
    public const EXTRA_ADULT_RATE = 500;

    /** Standard check-in and check-out clock times. Every new stay is booked at these. */
    public const CHECK_IN_TIME = '14:00';
    public const CHECK_OUT_TIME = '12:00';

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
        'children',
        'extra_adults',
        'extra_adult_rate',
        'service_charge_rate',
        'group_ref',
    ];

    /** False until the group_ref migration has run. */
    public static function groupSupported(): bool
    {
        static $has = null;

        return $has ??= \Illuminate\Support\Facades\Schema::hasColumn('hotel_bookings', 'group_ref');
    }

    /**
     * Every room booked together with this one for the same guest (its group_ref),
     * this one included, cancelled ones left out. Just this booking when alone.
     *
     * @return \Illuminate\Support\Collection<int, self>
     */
    public function groupBookings(): \Illuminate\Support\Collection
    {
        if (!self::groupSupported() || !$this->group_ref) {
            return collect([$this]);
        }

        return self::where('group_name', $this->group_name)
            ->where('faculty_id', $this->faculty_id)
            ->where('group_ref', $this->group_ref)
            ->where('status', '!=', 'Cancelled')
            ->orderBy('hotel_booking_id')
            ->get();
    }

    protected $casts = [
        'check_in'       => 'date',
        'check_out'      => 'date',
        'room_rate'      => 'integer',
        'promo_price'    => 'integer',
        'promo_blocks'   => 'integer',
        'guest_count'    => 'integer',
        // [{age, rate, label}] per child, priced by HotelChildPolicy when booked.
        'children'       => 'array',
        'extra_adults'   => 'integer',
        'extra_adult_rate' => 'integer',
        'service_charge_rate' => 'float',
        'reserved_at'    => 'datetime',
        'arrived_at'     => 'datetime',
        'checked_in_at'  => 'datetime',
        'checked_out_at' => 'datetime',
        'cancelled_at'   => 'datetime',
    ];

    protected static function booted(): void
    {
        // Every screen that prices a stay needs its Senior Citizen / PWD rows, so they
        // ride along with each booking query rather than costing a query per stay.
        static::addGlobalScope('discounts', function (Builder $query) {
            if (HotelBookingDiscount::supported()) {
                $query->with('discounts');
            }
        });

        // Extend Stay, Change Room and a corrected party all move a person's share;
        // the stored amounts follow, so the record matches the bill.
        static::saved(fn (HotelBooking $booking) => $booking->recordDiscountAmounts());

        // A new stay is billed at today's service charge rate, and keeps it.
        static::creating(function (HotelBooking $booking) {
            if ($booking->service_charge_rate === null && self::serviceChargeSupported()) {
                $booking->service_charge_rate = (float) config('hms.service_charge_rate', 0);
            }
        });
    }

    /** False until the service_charge_rate migration has run. */
    public static function serviceChargeSupported(): bool
    {
        static $has = null;

        return $has ??= \Illuminate\Support\Facades\Schema::hasColumn('hotel_bookings', 'service_charge_rate');
    }

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

    /** Senior Citizens and PWDs on this stay, booker and companions alike. */
    public function discounts(): HasMany
    {
        return $this->hasMany(HotelBookingDiscount::class, 'hotel_booking_id', 'hotel_booking_id')
            ->orderBy('hotel_booking_discount_id');
    }

    /** The discount rows, or none on a database the migration has not reached yet. */
    public function discountRows(): \Illuminate\Support\Collection
    {
        return HotelBookingDiscount::supported() ? $this->discounts : collect();
    }

    /**
     * One eligible person's VAT-inclusive share of the accommodation.
     *
     * A child is discounted on their own child charge, which the child policy already
     * prices per child. An adult's share is the room charge split evenly between the
     * adults: the child policy bills children separately, so the room rate is the
     * adults' price.
     * ponytail: even split, because RA 9994, RA 10754 and RR 7-2010 set no shared-room
     * formula, only "the portion exclusively enjoyed"; swap here if the faculty adopts one.
     */
    public function discountShare(HotelBookingDiscount $discount): float
    {
        if ($discount->occupant === 'child') {
            $line = $this->childLines()[(int) $discount->child_index] ?? null;

            return $line ? round((float) $line['rate'] * $this->nights(), 2) : 0.0;
        }

        return round($this->roomCharge() / max(1, (int) ($this->adultCount() ?: 1)), 2);
    }

    /** What the verified Senior Citizen / PWD discounts take off: VAT removed + 20%. */
    public function statutoryDiscountTotal(): float
    {
        return round((float) $this->discountRows()
            ->filter->isVerified()
            ->sum(fn (HotelBookingDiscount $d) => HotelBookingDiscount::compute($this->discountShare($d))['less']), 2);
    }

    /** Keeps each row's stored amounts equal to what the bill takes off. */
    public function recordDiscountAmounts(): void
    {
        if (!HotelBookingDiscount::supported()) {
            return;
        }
        $this->unsetRelation('discounts');
        foreach ($this->discounts as $discount) {
            $money = HotelBookingDiscount::compute($this->discountShare($discount));
            $discount->forceFill([
                'eligible_amount'   => $money['eligible'],
                'vat_exempt_amount' => $money['vat'],
                'discount_amount'   => $money['discount'],
            ]);
            if ($discount->isDirty()) {
                $discount->save();
            }
        }
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
     * The stay's accommodation: the room plus the children's charges. Deliberately
     * excludes room service: the payments taken at booking time recorded their balance
     * against this figure, and moving it would rewrite what those receipts said the
     * guest still owed. A stay booked before child pricing has no children, so its
     * figure is unchanged.
     *
     * A stay booked under a promo pays the package price for the blocks it was booked
     * for; any block added after that is charged at the room rate.
     */
    public function totalDue(): float
    {
        // Less the verified Senior Citizen / PWD discounts: they are taken at booking,
        // so the payment taken then is already against the discounted figure, and the
        // final bill reads the same number instead of applying them a second time.
        return round($this->roomCharge() + $this->childChargesTotal() + $this->extraAdultTotal() - $this->statutoryDiscountTotal(), 2);
    }

    /** The room at its rate or package price, without the children's charges. */
    public function roomCharge(): float
    {
        if ($this->promo_price !== null) {
            return $this->promo_price
                + max(0, $this->stayBlocks() - (int) $this->promo_blocks) * (float) $this->room_rate;
        }

        return $this->stayBlocks() * (float) $this->room_rate;
    }

    /** The children on the stay, as priced when it was booked: [{age, rate, label}]. */
    public function childLines(): array
    {
        return is_array($this->children) ? array_values($this->children) : [];
    }

    /** Adults in the party: everyone who is not on the children list. */
    public function adultCount(): ?int
    {
        return $this->guest_count === null ? null : max(0, $this->guest_count - count($this->childLines()));
    }

    /** Child rates are per night, so Extend Stay moves this with the stay. */
    public function childChargesTotal(): float
    {
        return (float) array_sum(array_column($this->childLines(), 'rate')) * $this->nights();
    }

    /** Adults past the room's limit, at the rate snapshotted when booked, every night. */
    public function extraAdultTotal(): float
    {
        return (float) ((int) $this->extra_adults * (int) $this->extra_adult_rate * $this->nights());
    }

    /** "1 Adult + 2 Children", or "" when the party size was never asked. */
    public function occupancyLabel(): string
    {
        $adults = $this->adultCount();
        if ($adults === null) {
            return '';
        }
        $kids = count($this->childLines());

        return $adults . ' Adult' . ($adults === 1 ? '' : 's')
            . ($kids ? ' + ' . $kids . ' ' . ($kids === 1 ? 'Child' : 'Children') : '');
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

    /** Room + room service + add-ons + extras, before the service charge. */
    public function chargesBeforeServiceCharge(): float
    {
        return $this->totalDue()
            + $this->roomServiceTotal()
            + $this->addonsTotal()
            + $this->otherChargesTotal();
    }

    /**
     * Service charge on every charge, after the Senior Citizen / PWD discounts (they
     * are already out of totalDue()). Collected with the final bill. A stay booked
     * before there was a service charge has no rate and pays none.
     */
    public function serviceCharge(): float
    {
        $rate = (float) ($this->service_charge_rate ?? 0);

        return $rate > 0 ? round($this->chargesBeforeServiceCharge() * $rate / 100, 2) : 0.0;
    }

    /** Everything above plus the service charge — the figure the guest actually settles. */
    public function grandTotal(): float
    {
        return round($this->chargesBeforeServiceCharge() + $this->serviceCharge(), 2);
    }

    /** Still owed once everything paid so far is counted against the full bill. */
    public function outstanding(): float
    {
        return max(0, $this->grandTotal() - $this->amountPaid());
    }

    /**
     * Paid more than the bill: a discount verified after the guest had already paid
     * the full price. Front Desk records the refund; nothing is changed silently.
     */
    public function refundDue(): float
    {
        return round(max(0, $this->amountPaid() - $this->grandTotal()), 2);
    }

    private function discountArrays(): array
    {
        return $this->discountRows()->map(fn (HotelBookingDiscount $d) => $d->toTemplateArray($this))->values()->all();
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
            // Shared by rooms booked together for one guest; null when alone.
            'groupRef'      => $this->group_ref ?? null,
            'status'        => $this->status,
            'fullName'      => $guest?->full_name ?? 'Guest',
            'contactNo'     => $guest?->contact_no ?? '',
            'email'         => $guest?->email ?? '',
            'idNumber'      => $guest?->id_number ?? '',
            'checkIn'       => optional($this->check_in)->toDateString(),
            'checkInTime'   => $this->check_in_time ?? '',
            'checkOut'      => optional($this->check_out)->toDateString(),
            'guestCount'    => $this->guest_count,
            'adults'        => $this->adultCount(),
            'children'      => $this->childLines(),
            'occupancy'     => $this->occupancyLabel(),
            'childCharges'  => $this->childChargesTotal(),
            'extraAdults'   => (int) $this->extra_adults,
            'extraAdultRate'=> (int) $this->extra_adult_rate,
            'extraAdultCharges' => $this->extraAdultTotal(),
            'roomRate'      => (int) $this->room_rate,
            'promoName'     => $this->promo_name ?? '',
            'promoPrice'    => $this->promo_price,
            'totalDue'      => $this->totalDue(),
            // Senior Citizen / PWD: every claim, verified or pending, and what the
            // verified ones take off totalDue. ID numbers arrive masked.
            'discounts'         => $this->discountArrays(),
            'statutoryDiscount' => $this->statutoryDiscountTotal(),
            'refundDue'         => $this->refundDue(),
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
            'finalChecks'   => $this->status === 'Pending Room Inspection' ? $this->finalChecks() : null,
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

        foreach ($this->finalChecks() as $check) {
            if (!$check['ok']) {
                return $check['detail'];
            }
        }

        return null;
    }

    /**
     * The "Verify final charges" step: each department has nothing left open on this
     * stay before the final bill closes it. Housekeeping inspected the room (and the
     * Front Desk reviewed any damage), Restaurant and Room Service delivered every
     * order, and Housekeeping finished or cancelled every request.
     *
     * @return array<int, array{key: string, label: string, ok: bool, detail: string}>
     */
    public function finalChecks(): array
    {
        $inspection = $this->inspection;
        $inspected = $inspection && $inspection->status === 'Reported';
        $damageLeft = $inspected ? $inspection->damages()->where('status', 'Pending Review')->count() : 0;

        // foodOrders() already leaves out Cancelled ones.
        $ordersLeft = $this->foodOrders()->where('status', '!=', 'Completed')->count();

        $requestsLeft = HotelServiceRequest::supported()
            ? HotelServiceRequest::where('hotel_booking_id', $this->hotel_booking_id)
                ->whereIn('status', HotelServiceRequest::OPEN_STATUSES)->count()
            : 0;

        $plural = fn (int $n, string $word) => $n . ' ' . $word . ($n === 1 ? '' : 's');

        return [
            [
                'key'    => 'inspection',
                'label'  => 'Room inspected by Housekeeping',
                'ok'     => $inspected && $damageLeft === 0,
                'detail' => !$inspected
                    ? 'Housekeeping has not finished inspecting the room yet.'
                    : ($damageLeft
                        ? 'Review the damage Housekeeping reported before the final bill.'
                        : 'Inspected. Damage, if any, is reviewed.'),
            ],
            [
                'key'    => 'orders',
                'label'  => 'Food orders delivered (Restaurant / Room Service)',
                'ok'     => $ordersLeft === 0,
                'detail' => $ordersLeft
                    ? 'Room Service has ' . $plural($ordersLeft, 'order') . ' not delivered yet. Restaurant must deliver and complete '
                        . ($ordersLeft === 1 ? 'it' : 'them') . ' before check-out.'
                    : 'Every order is delivered and on the bill.',
            ],
            [
                'key'    => 'requests',
                'label'  => 'Housekeeping requests closed',
                'ok'     => $requestsLeft === 0,
                'detail' => $requestsLeft
                    ? 'Housekeeping has ' . $plural($requestsLeft, 'open request') . ' for this stay. Let Housekeeping finish, '
                        . 'or cancel a Pending or Accepted one in Details.'
                    : 'No open requests.',
            ],
        ];
    }

    /**
     * One stay as its guest sees it on My Bookings. Narrower than
     * toReservationArray(): no ID number, no staff notes, no who-booked-it.
     */
    public function toGuestArray(): array
    {
        return [
            'reference'   => $this->hotel_booking_id,
            // Rooms booked together share it, so My Bookings can show them as one.
            'groupRef'    => $this->group_ref ?? null,
            'status'      => $this->status,
            'roomName'    => $this->room?->name ?? '',
            'roomCategory'=> $this->room?->category ?? '',
            'checkIn'     => optional($this->check_in)->toDateString(),
            'checkInTime' => $this->check_in_time ?? '',
            'checkOut'    => optional($this->check_out)->toDateString(),
            'guestCount'  => $this->guest_count,
            'occupancy'   => $this->occupancyLabel(),
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
        foreach ($bill['children']['items'] as $child) {
            $lines[] = [
                'kind'   => 'child',
                'label'  => 'Child, age ' . $child['age'],
                'detail' => $child['rate']
                    ? $peso($child['rate']) . ' × ' . $child['nights'] . ' night' . ($child['nights'] === 1 ? '' : 's')
                    : $child['label'],
                'amount' => (float) $child['line'],
            ];
        }
        if ($bill['extraAdults']['count']) {
            $x = $bill['extraAdults'];
            $lines[] = [
                'kind'   => 'extra-adult',
                'label'  => 'Extra adult' . ($x['count'] === 1 ? '' : 's') . ' × ' . $x['count'],
                'detail' => $peso($x['rate']) . ' × ' . $x['nights'] . ' night' . ($x['nights'] === 1 ? '' : 's'),
                'amount' => (float) $x['subtotal'],
            ];
        }
        foreach ($bill['discounts']['items'] as $discount) {
            if ($discount['verified']) {
                $lines[] = [
                    'kind'   => 'discount',
                    'label'  => $discount['type'] . ' discount · ' . $discount['fullName'],
                    'detail' => 'VAT exemption ₱' . number_format($discount['vat'], 2)
                        . ' + 20% discount ₱' . number_format($discount['discount'], 2),
                    'amount' => -(float) $discount['less'],
                ];
            }
        }
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
        if ($bill['serviceCharge']['amount'] > 0) {
            $lines[] = [
                'kind'   => 'service',
                'label'  => 'Service charge (' . rtrim(rtrim(number_format($bill['serviceCharge']['rate'], 2), '0'), '.') . '%)',
                'detail' => 'On ₱' . number_format($bill['serviceCharge']['base'], 2) . ', after discounts',
                'amount' => (float) $bill['serviceCharge']['amount'],
            ];
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
            'groupRef'     => $this->group_ref ?? null,
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
                'subtotal'  => $this->roomCharge(),
            ],

            // One line per child, free ones too, so the bill says why a child cost nothing.
            'occupancy' => $this->occupancyLabel(),
            'children' => [
                'items'    => array_map(fn ($child) => $child + [
                    'nights' => $this->nights(),
                    'line'   => (int) $child['rate'] * $this->nights(),
                ], $this->childLines()),
                'subtotal' => $this->childChargesTotal(),
            ],

            // Adults past what the room sleeps, each on a folding bed (billed under addons).
            'extraAdults' => [
                'count'    => (int) $this->extra_adults,
                'rate'     => (int) $this->extra_adult_rate,
                'nights'   => $this->nights(),
                'subtotal' => $this->extraAdultTotal(),
            ],

            // Senior Citizen / PWD, applied at booking. The room and child lines above
            // stay at full price; these are the separate adjustment the law asks the
            // receipt to show. Pending claims are listed but take nothing off.
            'discounts' => [
                'items'    => $this->discountArrays(),
                'subtotal' => $this->statutoryDiscountTotal(),
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

            // On every charge above, after the Senior Citizen / PWD discounts.
            'serviceCharge' => [
                'rate'   => (float) ($this->service_charge_rate ?? 0),
                'base'   => round($this->chargesBeforeServiceCharge(), 2),
                'amount' => $this->serviceCharge(),
            ],

            'total'       => $this->grandTotal(),
            'payments'    => $this->payments->sortBy('paid_at')->map->toTemplateArray()->values()->all(),
            'amountPaid'  => $this->amountPaid(),
            'balance'     => $this->outstanding(),
            'refundDue'   => $this->refundDue(),
        ];
    }
}
