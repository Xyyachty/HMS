<?php

namespace App\Support;

use App\Models\HotelDineInTable;
use App\Models\HotelFoodOrder;

/**
 * The final bill for one dine-in table.
 *
 * Priced here rather than in the browser for the same reason HotelBooking::toBillArray()
 * is: what the customer is charged must not be able to drift from what the database
 * will record when it is settled.
 */
class HotelDineInBill
{
    /**
     * Statuses that mean the kitchen has not finished with the customer yet. Billing
     * comes after serving, so any of these still open blocks a settle.
     */
    public const UNSERVED_STATUSES = ['Preparing', 'Ready', 'Delivering'];

    /**
     * Orders belonging to the table's *current* seating.
     *
     * Scoped by assigned_at, not by table alone: a table is reused all evening, and
     * the party before this one has already paid and left. A Cancelled order never
     * counts — nobody ate it.
     */
    public static function ordersFor(HotelDineInTable $table)
    {
        $query = HotelFoodOrder::where('group_name', $table->group_name)
            ->where('faculty_id', $table->faculty_id)
            ->where('dine_in_table_id', $table->hotel_dine_in_table_id)
            ->where('order_type', 'dine_in')
            ->where('status', '!=', 'Cancelled');

        if ($table->assigned_at) {
            $query->where('created_at', '>=', $table->assigned_at);
        }

        return $query->orderBy('hotel_food_order_id')->get();
    }

    /**
     * The bill as the settle screen renders it: one entry per order line, the orders
     * those lines came from, and what is still cooking.
     */
    public static function for(HotelDineInTable $table): array
    {
        $orders = self::ordersFor($table);

        $items = [];
        $total = 0.0;

        foreach ($orders as $order) {
            foreach (($order->items ?? []) as $line) {
                $qty = (int) ($line['qty'] ?? 0);
                $price = (float) ($line['price'] ?? 0);
                $items[] = [
                    'orderId' => $order->hotel_food_order_id,
                    'name'    => (string) ($line['name'] ?? ''),
                    'qty'     => $qty,
                    'price'   => $price,
                    'line'    => $price * $qty,
                ];
                $total += $price * $qty;
            }
        }

        $unserved = $orders
            ->filter(fn (HotelFoodOrder $order) => in_array($order->status, self::UNSERVED_STATUSES, true))
            ->map(fn (HotelFoodOrder $order) => [
                'orderId' => $order->hotel_food_order_id,
                'status'  => $order->status,
            ])
            ->values()
            ->all();

        return [
            'tableId'   => $table->hotel_dine_in_table_id,
            'tableName' => $table->name,
            'guestName' => $table->guest_name ?? '',
            'partySize' => $table->party_size,
            'reservedAt' => optional($table->assigned_at)->toIso8601String(),
            'reservedBy' => $table->assigned_by ?? '',
            'orders'    => $orders->map(fn (HotelFoodOrder $order) => [
                'orderId' => $order->hotel_food_order_id,
                'status'  => $order->status,
                'total'   => (float) $order->total,
                'placedAt' => optional($order->created_at)->toIso8601String(),
            ])->values()->all(),
            'items'     => $items,
            'orderIds'  => $orders->pluck('hotel_food_order_id')->values()->all(),
            'total'     => $total,
            // Non-empty means the customer has not been served yet, so the bill is
            // premature — the settle route refuses on the same condition.
            'unserved'  => $unserved,
        ];
    }
}
