<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HotelFoodOrder extends Model
{
    /**
     * Restaurant Services carries an order the whole way — they cook it and they walk
     * it up to the room themselves. Front Desk only reads the status.
     *
     *   Preparing -> Ready -> Delivering -> Completed
     *
     * An order starts at Preparing: it lands in the kitchen already accepted, so there
     * is no queue in front of the stove to sit in. Delivering means a runner has left
     * the kitchen with it; Completed is the guest having it in hand and the ticket
     * closed.
     *
     * Cancelled is off to the side of that line and returns the portions to stock, but
     * it is a dine-in exit only — a room-service order is billed to a stay as soon as
     * it is placed, so it runs to Completed. The route enforces that; the flow itself
     * is shared by both order types.
     */
    public const STATUSES = [
        'Preparing',
        'Ready',
        'Delivering',
        'Completed',
        'Cancelled',
        // Catering only — see CATERING_FLOW.
        'Pending',
        'Confirmed',
        'Serving',
    ];

    /** The kitchen pipeline in order, so a screen can offer "the next step". */
    public const FLOW = ['Preparing', 'Ready', 'Delivering', 'Completed'];

    /**
     * Catering runs a longer pipeline than a dish, because it is agreed days ahead and
     * served over hours rather than carried up in one trip. Pending is the booking landing
     * on the kitchen's board before they have agreed to it; Confirmed is them accepting;
     * Serving is the event itself, which replaces a room-service runner's Delivering.
     */
    public const CATERING_FLOW = ['Pending', 'Confirmed', 'Preparing', 'Ready', 'Serving', 'Completed'];

    /** Statuses that still owe the guest food. Anything else is finished. */
    public const OPEN_STATUSES = ['Preparing', 'Ready'];

    /** The catering equivalent: everything before the event is over. */
    public const CATERING_OPEN_STATUSES = ['Pending', 'Confirmed', 'Preparing', 'Ready', 'Serving'];

    protected $primaryKey = 'hotel_food_order_id';

    /**
     * Front Desk places a room-service order; Restaurant Management places one dine-in;
     * a function room booking raises a catering order on their board without either desk
     * typing it, from HotelCateringDesk.
     */
    public const ORDER_TYPES = [
        'room_service',
        'dine_in',
        'catering',
    ];

    protected $fillable = [
        'group_name',
        'faculty_id',
        'group_id',
        'order_type',
        'hotel_booking_id',
        'dine_in_table_id',
        'hotel_amenity_reservation_id',
        'room_number',
        'guest_name',
        'items',
        'total',
        'status',
        'placed_by',
    ];

    protected $casts = [
        'items' => 'array',
        'total' => 'integer',
    ];

    /** The stay this order gets billed to. Null for dine-in. */
    public function booking(): BelongsTo
    {
        return $this->belongsTo(HotelBooking::class, 'hotel_booking_id', 'hotel_booking_id');
    }

    /** Orders that count towards a bill — a cancelled one is not charged for. */
    public function scopeBillable(Builder $query): Builder
    {
        return $query->where('status', '!=', 'Cancelled');
    }

    /**
     * The step after this one, or null at the end of the line. Cancelled has no next
     * step: it is not on the flow.
     */
    public function nextStatus(): ?string
    {
        $flow = self::flowFor($this->order_type);
        $at = array_search($this->status, $flow, true);

        return $at === false || $at === count($flow) - 1 ? null : $flow[$at + 1];
    }

    /** The event this catering order is feeding. Null for room service and dine-in. */
    public function reservation(): BelongsTo
    {
        return $this->belongsTo(
            HotelAmenityReservation::class,
            'hotel_amenity_reservation_id',
            'hotel_amenity_reservation_id'
        );
    }

    public static function normalizeStatus(?string $value, ?string $orderType = null): string
    {
        $raw = strtolower(trim((string) $value));
        // Only the statuses this order type actually runs, so a room-service ticket can
        // never be pushed to 'Serving' and a catering one can never land on 'Delivering'.
        $allowed = array_merge(self::flowFor($orderType), ['Cancelled']);

        foreach ($allowed as $status) {
            if (strtolower($status) === $raw) {
                return $status;
            }
        }

        if ($orderType === 'catering') {
            return 'Pending';
        }

        return 'Preparing';
    }

    public static function normalizeOrderType(?string $value): string
    {
        $raw = strtolower(trim((string) $value));

        return in_array($raw, self::ORDER_TYPES, true) ? $raw : 'room_service';
    }

    /**
     * Whether $to is a legal move from $from: forward through FLOW only, or
     * Cancelled as an exit from anywhere still open. Nothing moves once an order
     * has reached Completed or Cancelled — those are the end of the line, for the
     * kitchen that owns the flow as much as for anyone else.
     */
    public static function isForwardTransition(string $from, string $to, ?string $orderType = null): bool
    {
        if ($from === $to || in_array($from, ['Completed', 'Cancelled'], true)) {
            return false;
        }

        if ($to === 'Cancelled') {
            return true;
        }

        $flow = self::flowFor($orderType);
        $fromIndex = array_search($from, $flow, true);
        $toIndex = array_search($to, $flow, true);

        return $fromIndex !== false && $toIndex !== false && $toIndex > $fromIndex;
    }

    /**
     * The pipeline this order runs. Catering's is longer and starts earlier; everything
     * else uses the kitchen's.
     *
     * $orderType is passed rather than read off $this so the static transition check can
     * answer for an order it does not hold, which is what the routes need.
     */
    public static function flowFor(?string $orderType): array
    {
        return $orderType === 'catering' ? self::CATERING_FLOW : self::FLOW;
    }

    /** The first status an order of this type is created at. */
    public static function initialStatusFor(?string $orderType): string
    {
        return $orderType === 'catering' ? 'Pending' : 'Preparing';
    }

    public function isCatering(): bool
    {
        return $this->order_type === 'catering';
    }

    /**
     * Line items arrive from the browser, so keep only the fields we store and
     * drop anything that cannot be priced.
     */
    public static function sanitizeItems(array $items): array
    {
        $clean = [];

        foreach ($items as $item) {
            if (!is_array($item)) {
                continue;
            }

            $name = trim((string) ($item['name'] ?? ''));
            if ($name === '') {
                continue;
            }

            $qty = (int) ($item['qty'] ?? 1);
            if ($qty < 1) {
                $qty = 1;
            }

            // Keep the menu row id so stock can be reconciled without matching on
            // the name, which breaks the moment an item is renamed or re-cased.
            $menuItemId = (int) ($item['menu_item_id'] ?? $item['dbId'] ?? 0);

            $clean[] = [
                'menu_item_id' => $menuItemId > 0 ? $menuItemId : null,
                'name'         => mb_substr($name, 0, 255),
                'price'        => max(0, (int) ($item['price'] ?? 0)),
                'qty'          => min(99, $qty),
            ];
        }

        return $clean;
    }

    public static function totalFor(array $items): int
    {
        $total = 0;
        foreach ($items as $item) {
            $total += ((int) $item['price']) * ((int) $item['qty']);
        }

        return $total;
    }

    /** Shape sent to the hotel template front-end. */
    public function toTemplateArray(): array
    {
        // "id" here is the front-end's key for an order, not the column name.
        return [
            'id'         => $this->hotel_food_order_id,
            'orderType'  => $this->order_type,
            'bookingId'  => $this->hotel_booking_id,
            'tableId'    => $this->dine_in_table_id,
            'roomNumber' => $this->room_number,
            'guestName'  => $this->guest_name,
            'items'      => $this->items ?? [],
            'total'      => (int) $this->total,
            'status'     => $this->status,
            'nextStatus' => $this->nextStatus(),

            // Catering only — what the kitchen needs to know to cook for an event, rather
            // than a room number. Null on every other order type.
            'reservationId' => $this->hotel_amenity_reservation_id,
            'eventVenue'    => $this->reservation?->amenity_name,
            'eventType'     => $this->reservation?->event_type,
            'eventDate'     => optional($this->reservation?->scheduled_on)->toDateString(),
            'eventTime'     => $this->reservation?->timeLabel(),
            'guestCount'    => $this->reservation?->guest_count,
            'eventRequests' => $this->reservation?->special_requests,
            'placedBy'   => $this->placed_by ?? '',
            'placedAt'   => optional($this->created_at)->toIso8601String(),
            'updatedAt'  => optional($this->updated_at)->toIso8601String(),
        ];
    }
}
