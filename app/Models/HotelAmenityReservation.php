<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * A facility held for someone at a time — a spa appointment, a function room event.
 *
 * One class for both kinds, discriminated by `kind`, because everything that matters is
 * shared: a slot that must not overlap another, a customer who may or may not be staying
 * here, a status that only moves forward, and a bill.
 *
 * MONEY IS DERIVED. grandTotal() adds up the fee columns plus the linked catering order's
 * own total; nothing stores a grand total, and nothing stores what catering costs. A
 * stored copy could disagree with the order Restaurant Services is actually cooking, which
 * is the bug HotelAddon::toTemplateArray() calls out about availability. The one thing
 * that IS snapshotted is a payment's total_due/balance, and only because a receipt has to
 * keep saying what it said on the day.
 */
class HotelAmenityReservation extends Model
{
    /** A spa treatment or a function room event. Drives which columns are meaningful. */
    public const KINDS = ['appointment', 'event'];

    /**
     * Pending is taken but not settled; Confirmed is committed; In Progress is happening
     * now; Completed is done. Cancelled is the exit, available until it is finished.
     */
    public const STATUSES = ['Pending', 'Confirmed', 'In Progress', 'Completed', 'Cancelled'];

    /** The working pipeline, in order. Cancelled sits off to the side as an exit. */
    public const FLOW = ['Pending', 'Confirmed', 'In Progress', 'Completed'];

    /** Statuses that still hold the slot. Anything else frees the room for someone else. */
    public const OPEN_STATUSES = ['Pending', 'Confirmed', 'In Progress'];

    /**
     * What Front Desk can sell with the hall. Hall Only bills the venue alone; Setup adds
     * the setup fee and puts the room on Housekeeping's list; Catering adds an order on
     * Restaurant Services' board; Custom is the desk deciding for itself.
     */
    public const PACKAGES = ['Hall Only', 'Hall + Setup', 'Hall + Catering', 'Custom Package'];

    /** Packages whose booking raises a Housekeeping preparation request. */
    public const SETUP_PACKAGES = ['Hall + Setup', 'Custom Package'];

    /** Packages whose booking raises a catering order on Restaurant Services' board. */
    public const CATERING_PACKAGES = ['Hall + Catering', 'Custom Package'];

    /**
     * Housekeeping's turnaround for one event. Deliberately separate from the amenity's
     * own Available / Temporarily Closed / Under Maintenance: a hall being cleaned after a
     * wedding is not a broken hall, and a hall that IS broken still goes to Maintenance
     * through the existing repair loop.
     */
    public const HOUSEKEEPING_FLOW = [
        'For Preparation', 'Preparing', 'Ready', 'In Use',
        'Needs Cleaning', 'Cleaning', 'Inspected',
    ];

    protected $primaryKey = 'hotel_amenity_reservation_id';

    protected $fillable = [
        'group_name', 'faculty_id', 'group_id',
        'hotel_amenity_id', 'hotel_amenity_service_id', 'hotel_booking_id',
        'kind', 'reference', 'amenity_name',
        'customer_name', 'contact_no', 'email',
        'scheduled_on', 'starts_at', 'ends_at', 'status', 'special_requests',
        'event_type', 'guest_count', 'package', 'hotel_catering_package_id', 'housekeeping_status',
        'venue_fee', 'setup_fee', 'service_fee', 'additional_fee', 'additional_note',
        'charge_to_room', 'posted_to_folio_at', 'booked_by',
    ];

    protected $casts = [
        'scheduled_on'       => 'date',
        'guest_count'        => 'integer',
        'venue_fee'          => 'float',
        'setup_fee'          => 'float',
        'service_fee'        => 'float',
        'additional_fee'     => 'float',
        'charge_to_room'     => 'boolean',
        'posted_to_folio_at' => 'datetime',
    ];

    /* ── Relationships ──────────────────────────────────────────────────── */

    public function amenity(): BelongsTo
    {
        return $this->belongsTo(HotelAmenity::class, 'hotel_amenity_id', 'hotel_amenity_id');
    }

    public function service(): BelongsTo
    {
        return $this->belongsTo(HotelAmenityService::class, 'hotel_amenity_service_id', 'hotel_amenity_service_id');
    }

    public function cateringPackage(): BelongsTo
    {
        return $this->belongsTo(HotelCateringPackage::class, 'hotel_catering_package_id', 'hotel_catering_package_id');
    }

    public function booking(): BelongsTo
    {
        return $this->belongsTo(HotelBooking::class, 'hotel_booking_id', 'hotel_booking_id');
    }

    public function payments(): HasMany
    {
        return $this->hasMany(HotelAmenityPayment::class, 'hotel_amenity_reservation_id', 'hotel_amenity_reservation_id')
            ->orderBy('hotel_amenity_payment_id');
    }

    /**
     * The kitchen tickets feeding this event. Billable only — a cancelled catering order
     * is not charged for, the same call HotelBooking::foodOrders() makes.
     */
    public function cateringOrders(): HasMany
    {
        return $this->hasMany(HotelFoodOrder::class, 'hotel_amenity_reservation_id', 'hotel_amenity_reservation_id')
            ->where('status', '!=', 'Cancelled');
    }

    /* ── Scopes ─────────────────────────────────────────────────────────── */

    /** Reservations that still hold their slot. */
    public function scopeHolding(Builder $query): Builder
    {
        return $query->whereIn('status', self::OPEN_STATUSES);
    }

    /* ── Normalisation — PHP-side enums, the repo convention ────────────── */

    public static function normalizeKind(?string $value): string
    {
        $raw = mb_strtolower(trim((string) $value));

        return in_array($raw, self::KINDS, true) ? $raw : 'appointment';
    }

    public static function normalizeStatus(?string $value): string
    {
        return self::matchOne(self::STATUSES, $value, 'Pending');
    }

    public static function normalizePackage(?string $value): string
    {
        return self::matchOne(self::PACKAGES, $value, 'Hall Only');
    }

    public static function normalizeHousekeepingStatus(?string $value): string
    {
        return self::matchOne(self::HOUSEKEEPING_FLOW, $value, 'For Preparation');
    }

    private static function matchOne(array $allowed, ?string $value, string $fallback): string
    {
        $raw = mb_strtolower(trim((string) $value));

        foreach ($allowed as $candidate) {
            if (mb_strtolower($candidate) === $raw) {
                return $candidate;
            }
        }

        return $fallback;
    }

    /**
     * Whether $to is a legal move from $from: forward through FLOW only, or Cancelled as
     * an exit from anywhere still open. Nothing moves once a reservation has finished.
     * The same shape HotelComplaint::isForwardTransition() uses.
     */
    public static function isForwardTransition(string $from, string $to): bool
    {
        if ($from === $to || in_array($from, ['Completed', 'Cancelled'], true)) {
            return false;
        }

        if ($to === 'Cancelled') {
            return true;
        }

        $fromIndex = array_search($from, self::FLOW, true);
        $toIndex = array_search($to, self::FLOW, true);

        return $fromIndex !== false && $toIndex !== false && $toIndex > $fromIndex;
    }

    /** Housekeeping's turnaround only ever moves forward too. */
    public static function isForwardHousekeeping(?string $from, string $to): bool
    {
        $fromIndex = array_search((string) $from, self::HOUSEKEEPING_FLOW, true);
        $toIndex = array_search($to, self::HOUSEKEEPING_FLOW, true);

        if ($toIndex === false) {
            return false;
        }

        return $fromIndex === false ? true : $toIndex > $fromIndex;
    }

    /* ── Money. Everything here is computed; nothing is stored. ─────────── */

    /**
     * What the kitchen is charging for this event.
     *
     * Read off the linked orders rather than copied onto this row: a stored figure could
     * disagree with the order Restaurant Services is actually cooking after they adjust it.
     */
    public function cateringTotal(): float
    {
        return (float) $this->cateringOrders->sum('total');
    }

    /** Everything the customer owes for this booking. */
    public function grandTotal(): float
    {
        return round(
            (float) $this->venue_fee
            + (float) $this->setup_fee
            + (float) $this->service_fee
            + (float) $this->additional_fee
            + $this->cateringTotal(),
            2
        );
    }

    public function amountPaid(): float
    {
        return round((float) $this->payments->sum('amount_paid'), 2);
    }

    public function outstanding(): float
    {
        return round(max(0, $this->grandTotal() - $this->amountPaid()), 2);
    }

    public function isHolding(): bool
    {
        return in_array($this->status, self::OPEN_STATUSES, true);
    }

    /** Already pushed onto a guest's folio — the guard against charging them twice. */
    public function isPostedToFolio(): bool
    {
        return $this->posted_to_folio_at !== null;
    }

    /* ── Presentation ───────────────────────────────────────────────────── */

    public function timeLabel(): string
    {
        $from = HotelAmenity::formatTime($this->starts_at);
        $to = HotelAmenity::formatTime($this->ends_at);

        return $from && $to ? $from . ' – ' . $to : ($from ?: '');
    }

    /**
     * The charge lines this reservation puts on a folio, one per component, so the guest's
     * final bill itemises rather than showing one opaque lump.
     *
     * @return array<int, array{description: string, amount: float}>
     */
    public function folioLines(): array
    {
        $lines = [];
        $label = $this->amenity_name ?: 'Amenity';

        if ((float) $this->service_fee > 0) {
            $service = $this->service?->name;
            $lines[] = [
                'description' => $label . ($service ? ' · ' . $service : ''),
                'amount'      => round((float) $this->service_fee, 2),
            ];
        }
        if ((float) $this->venue_fee > 0) {
            $lines[] = ['description' => $label, 'amount' => round((float) $this->venue_fee, 2)];
        }
        if ((float) $this->setup_fee > 0) {
            $lines[] = ['description' => $label . ' Setup', 'amount' => round((float) $this->setup_fee, 2)];
        }

        $catering = $this->cateringTotal();
        if ($catering > 0) {
            $package = $this->cateringPackage?->name;
            $lines[] = [
                'description' => 'Catering' . ($package ? ' · ' . $package : '')
                    . ($this->guest_count ? ' ×' . $this->guest_count : ''),
                'amount'      => round($catering, 2),
            ];
        }

        if ((float) $this->additional_fee > 0) {
            $lines[] = [
                'description' => trim((string) $this->additional_note) ?: ($label . ' · Additional services'),
                'amount'      => round((float) $this->additional_fee, 2),
            ];
        }

        return $lines;
    }

    /** Shape sent to the Front Desk, Housekeeping and Restaurant screens. */
    public function toTemplateArray(): array
    {
        $catering = $this->cateringTotal();
        $order = $this->cateringOrders->first();

        // "id" here is the front-end's key for a reservation, not the column name.
        return [
            'id'            => $this->hotel_amenity_reservation_id,
            'kind'          => $this->kind,
            'reference'     => $this->reference ?? '',
            'amenityId'     => $this->hotel_amenity_id,
            'amenityName'   => $this->amenity_name,
            'bookingId'     => $this->hotel_booking_id,
            'customerName'  => $this->customer_name,
            'contactNo'     => $this->contact_no ?? '',
            'email'         => $this->email ?? '',
            'scheduledOn'   => optional($this->scheduled_on)->toDateString(),
            'startsAt'      => $this->starts_at,
            'endsAt'        => $this->ends_at,
            'timeLabel'     => $this->timeLabel(),
            'status'        => $this->status,
            'specialRequests' => $this->special_requests ?? '',

            'serviceId'     => $this->hotel_amenity_service_id,
            'serviceName'   => $this->service?->name ?? '',

            'eventType'     => $this->event_type ?? '',
            'guestCount'    => $this->guest_count,
            'package'       => $this->package ?? '',
            'cateringPackageId'   => $this->hotel_catering_package_id,
            'cateringPackageName' => $this->cateringPackage?->name ?? '',
            'housekeepingStatus'  => $this->housekeeping_status,
            'cateringOrderId'     => $order?->hotel_food_order_id,
            'cateringOrderStatus' => $order?->status,

            'venueFee'      => (float) $this->venue_fee,
            'setupFee'      => (float) $this->setup_fee,
            'serviceFee'    => (float) $this->service_fee,
            'additionalFee' => (float) $this->additional_fee,
            'additionalNote' => $this->additional_note ?? '',
            // Derived, never stored — see the class docblock.
            'cateringTotal' => $catering,
            'total'         => $this->grandTotal(),
            'amountPaid'    => $this->amountPaid(),
            'balance'       => $this->outstanding(),
            'payments'      => $this->payments->map->toTemplateArray()->values()->all(),

            'chargeToRoom'  => (bool) $this->charge_to_room,
            'postedToFolio' => $this->isPostedToFolio(),
            'postedAt'      => optional($this->posted_to_folio_at)->toIso8601String(),
            'bookedBy'      => $this->booked_by ?? '',
            'createdAt'     => optional($this->created_at)->toIso8601String(),
        ];
    }
}
