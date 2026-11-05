<?php

namespace App\Support;

use App\Models\HotelBookingDiscount;
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
    /**
     * The Senior Citizen / PWD guests a bill is discounted for, cleaned and checked:
     * a type, a name and an ID number each, no ID twice, and no more of them than
     * the party has guests.
     *
     * @return list<array{type: string, fullName: string, idNumber: string}>
     * @throws \RuntimeException with a message the cashier can act on
     */
    public static function claims(mixed $raw, int $partySize): array
    {
        $claims = [];
        $seen = [];
        foreach (is_array($raw) ? $raw : [] as $row) {
            $row = is_array($row) ? $row : [];
            $type = HotelBookingDiscount::normalizeType($row['type'] ?? null);
            $name = trim((string) ($row['full_name'] ?? $row['fullName'] ?? ''));
            $id = trim((string) ($row['id_number'] ?? $row['idNumber'] ?? ''));
            if (!$type) {
                throw new \RuntimeException('Pick Senior Citizen or PWD for each discounted guest.');
            }
            if ($name === '' || $id === '') {
                throw new \RuntimeException('Enter the name and ID number of each ' . $type . ' guest.');
            }
            $key = $type . '|' . mb_strtoupper(preg_replace('/[\s-]+/', '', $id));
            if (isset($seen[$key])) {
                throw new \RuntimeException('ID ' . $id . ' is listed twice.');
            }
            $seen[$key] = true;
            $claims[] = ['type' => $type, 'fullName' => mb_substr($name, 0, 120), 'idNumber' => mb_substr($id, 0, 60)];
        }
        if (count($claims) > max(1, $partySize)) {
            throw new \RuntimeException('There are more discounted guests than the ' . $partySize . ' guests at the table.');
        }

        return $claims;
    }

    /**
     * Prices a bill. Each Senior Citizen / PWD guest's share is the food subtotal
     * split evenly over the party; that share loses its VAT and then 20%, the same
     * computation the room bill uses (HotelBookingDiscount::compute). The service
     * charge (config hms.service_charge_rate) is then taken on what is left, as on
     * the guest bill.
     */
    public static function price(float $subtotal, int $partySize, array $claims): array
    {
        $share = $partySize > 0 ? round($subtotal / $partySize, 2) : $subtotal;
        $discounts = array_map(
            fn ($c) => $c + HotelBookingDiscount::compute($share),
            $claims
        );
        $discountTotal = round(array_sum(array_column($discounts, 'less')), 2);
        // Off until the payment can record it, so no receipt shows a total it cannot explain.
        $rate = \App\Models\HotelDineInPayment::pricingSupported() ? max(0, (float) config('hms.service_charge_rate', 0)) : 0.0;
        $afterDiscount = max(0, round($subtotal - $discountTotal, 2));
        $serviceCharge = $rate > 0 ? round($afterDiscount * $rate / 100, 2) : 0.0;

        return [
            'subtotal'          => round($subtotal, 2),
            'discounts'         => $discounts,
            'discountTotal'     => $discountTotal,
            'serviceChargeRate' => $rate,
            'serviceCharge'     => $serviceCharge,
            'total'             => round($afterDiscount + $serviceCharge, 2),
        ];
    }

    public static function for(HotelDineInTable $table, array $claims = []): array
    {
        // Tables reserved together for one guest are billed as one: every table of
        // the party, each line saying which table ordered it.
        $tables = $table->partyTables();
        $orders = collect();
        $tableNames = [];
        foreach ($tables as $partyTable) {
            foreach (self::ordersFor($partyTable) as $order) {
                $orders->push($order);
                $tableNames[$order->hotel_food_order_id] = $partyTable->name;
            }
        }

        $items = [];
        $total = 0.0;

        foreach ($orders as $order) {
            foreach (($order->items ?? []) as $line) {
                $qty = (int) ($line['qty'] ?? 0);
                $price = (float) ($line['price'] ?? 0);
                $items[] = [
                    'orderId'   => $order->hotel_food_order_id,
                    'tableName' => $tableNames[$order->hotel_food_order_id] ?? $table->name,
                    // When it was ordered, for the Date column of the printed bill.
                    'placedAt'  => optional($order->created_at)->toIso8601String(),
                    'name'      => (string) ($line['name'] ?? ''),
                    'qty'       => $qty,
                    'price'     => $price,
                    'line'      => $price * $qty,
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

        $partySize = (int) $tables->sum('party_size');
        $priced = self::price($total, $partySize, $claims);

        return $priced + [
            'tableId'   => $table->hotel_dine_in_table_id,
            'tableName' => HotelDineInTable::joinNames($tables),
            // Every table this bill covers, with where each one stands.
            'tables'    => $tables->map(fn (HotelDineInTable $t) => [
                'id' => $t->hotel_dine_in_table_id, 'name' => $t->name, 'status' => $t->status, 'partySize' => $t->party_size,
            ])->values()->all(),
            'partyRef'  => $table->party_ref ?? null,
            'guestName' => $table->guest_name ?? '',
            'partySize' => $partySize,
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
            // subtotal, discounts, serviceCharge and total come from price() above.
            // Non-empty means the customer has not been served yet, so the bill is
            // premature — the settle route refuses on the same condition.
            'unserved'  => $unserved,
        ];
    }
}
