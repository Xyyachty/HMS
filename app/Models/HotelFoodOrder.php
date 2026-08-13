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
     * closed. Cancelled is off to the side of that line and returns the portions to
     * stock.
     */
    public const STATUSES = [
        'Preparing',
        'Ready',
        'Delivering',
        'Completed',
        'Cancelled',
    ];

    /** The kitchen pipeline in order, so a screen can offer "the next step". */
    public const FLOW = ['Preparing', 'Ready', 'Delivering', 'Completed'];

    /** Statuses that still owe the guest food. Anything else is finished. */
    public const OPEN_STATUSES = ['Preparing', 'Ready'];

    protected $primaryKey = 'hotel_food_order_id';

    /** Front Desk places a room-service order; Restaurant Management places one dine-in. */
    public const ORDER_TYPES = [
        'room_service',
        'dine_in',
    ];

    protected $fillable = [
        'group_name',
        'faculty_id',
        'group_id',
        'order_type',
        'hotel_booking_id',
        'dine_in_table_id',
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
        $at = array_search($this->status, self::FLOW, true);

        return $at === false || $at === count(self::FLOW) - 1 ? null : self::FLOW[$at + 1];
    }

    public static function normalizeStatus(?string $value): string
    {
        $raw = strtolower(trim((string) $value));
        foreach (self::STATUSES as $status) {
            if (strtolower($status) === $raw) {
                return $status;
            }
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
            'placedBy'   => $this->placed_by ?? '',
            'placedAt'   => optional($this->created_at)->toIso8601String(),
            'updatedAt'  => optional($this->updated_at)->toIso8601String(),
        ];
    }
}
