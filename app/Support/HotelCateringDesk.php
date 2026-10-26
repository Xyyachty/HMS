<?php

namespace App\Support;

use App\Models\HotelAmenityReservation;
use App\Models\HotelCateringPackage;
use App\Models\HotelFoodOrder;

/**
 * Turning a function room booking into a ticket on Restaurant Services' board.
 *
 * The ticket is a hotel_food_orders row with order_type 'catering', not a new queue: the
 * kitchen already has a board, a status pipeline, a nav badge and a reports page watching
 * that table, and a second queue would be a second thing for them to remember to check.
 *
 * It is written HERE rather than through POST /hotel/orders on purpose. That route runs a
 * stock transaction — lock the menu rows, check every line, decrement — which is exactly
 * right for a dish and exactly wrong for a buffet: a catering package is priced per head
 * and has no shelf to come off. Routing catering through it would fail on the first line
 * with "no longer on the menu", because a package is not a menu item at all.
 *
 * So the line the kitchen sees is one row — the package, priced per head, quantity equal
 * to the guest count — and its price is snapshotted at booking time. Restaurant Services
 * repricing a package next month must not rewrite an event already sold.
 */
class HotelCateringDesk
{
    /**
     * Raise the kitchen's ticket for an event.
     *
     * Called from inside HotelAmenityReservationDesk::book()'s transaction, so an event
     * that promised catering can never exist without the order that delivers it.
     */
    public static function openOrder(
        HotelAmenityReservation $reservation,
        HotelCateringPackage $package,
        ?string $placedBy
    ): HotelFoodOrder {
        $guests = max(1, (int) $reservation->guest_count);

        return HotelFoodOrder::create([
            'group_name'                   => $reservation->group_name,
            'faculty_id'                   => $reservation->faculty_id,
            'group_id'                     => $reservation->group_id,
            'order_type'                   => 'catering',
            // No stay and no table: an event is billed on its own reservation, and the
            // hall is not a restaurant table.
            'hotel_booking_id'             => null,
            'dine_in_table_id'             => null,
            'hotel_amenity_reservation_id' => $reservation->hotel_amenity_reservation_id,
            // The kitchen's "where": a venue rather than a room number.
            'room_number'                  => $reservation->amenity_name,
            'guest_name'                   => $reservation->customer_name,
            // One line, priced per head. menu_item_id is null because a package is not a
            // menu item — which is also why nothing here touches stock.
            'items'                        => [[
                'menu_item_id' => null,
                'name'         => $package->name . ' (per head)',
                'price'        => (int) $package->price_per_head,
                'qty'          => $guests,
            ]],
            'total'                        => $package->totalFor($guests),
            // Pending, not Preparing: the kitchen has not agreed to it yet. See
            // HotelFoodOrder::CATERING_FLOW.
            'status'                       => HotelFoodOrder::initialStatusFor('catering'),
            'placed_by'                    => $placedBy,
        ]);
    }

    /**
     * The event changed after the kitchen was told — a different package, a different
     * headcount. Rewrites the open ticket rather than raising a second one.
     *
     * Refuses once the kitchen has started: past Confirmed they have bought food against
     * these numbers, and silently moving them under a cook is worse than making the desk
     * talk to the kitchen.
     */
    public static function updateOrder(
        HotelFoodOrder $order,
        HotelCateringPackage $package,
        int $guests
    ): HotelFoodOrder {
        if (!in_array($order->status, ['Pending', 'Confirmed'], true)) {
            throw new \RuntimeException(
                'The kitchen has already started on this event. Ask Restaurant Services before changing it.'
            );
        }

        $guests = max(1, $guests);

        $order->items = [[
            'menu_item_id' => null,
            'name'         => $package->name . ' (per head)',
            'price'        => (int) $package->price_per_head,
            'qty'          => $guests,
        ]];
        $order->total = $package->totalFor($guests);
        $order->save();

        return $order;
    }
}
