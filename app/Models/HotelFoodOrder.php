<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HotelFoodOrder extends Model
{
    /** Front Desk places an order as Pending; Restaurant Services moves it along. */
    public const STATUSES = [
        'Pending',
        'Preparing',
        'Delivered',
        'Cancelled',
    ];

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

    public static function normalizeStatus(?string $value): string
    {
        $raw = strtolower(trim((string) $value));
        foreach (self::STATUSES as $status) {
            if (strtolower($status) === $raw) {
                return $status;
            }
        }

        return 'Pending';
    }

    public static function normalizeOrderType(?string $value): string
    {
        $raw = strtolower(trim((string) $value));

        return in_array($raw, self::ORDER_TYPES, true) ? $raw : 'room_service';
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
            'tableId'    => $this->dine_in_table_id,
            'roomNumber' => $this->room_number,
            'guestName'  => $this->guest_name,
            'items'      => $this->items ?? [],
            'total'      => (int) $this->total,
            'status'     => $this->status,
            'placedBy'   => $this->placed_by ?? '',
            'placedAt'   => optional($this->created_at)->toIso8601String(),
            'updatedAt'  => optional($this->updated_at)->toIso8601String(),
        ];
    }
}
