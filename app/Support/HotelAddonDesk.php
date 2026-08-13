<?php

namespace App\Support;

use App\Models\HotelAddon;
use App\Models\HotelBooking;
use App\Models\HotelBookingAddon;
use App\Models\StudentGroup;
use RuntimeException;

/**
 * Availability and assignment for room add-ons.
 *
 * Nothing here decrements a counter. What is out on loan is the sum of the assignment rows
 * whose booking is still open, so a stay ending returns the folding bed by itself — see the
 * hotel_addon tables migration for why that beats a stored count.
 */
class HotelAddonDesk
{
    /**
     * How many of each add-on are currently lent out, keyed by hotel_addon_id.
     *
     * One grouped query for the whole team rather than a subquery per row: both the
     * Housekeeping table and the Front Desk picker render every add-on at once.
     */
    public static function reservedFor(StudentGroup $membership): array
    {
        return self::reservedIn($membership->group_name, $membership->faculty_id);
    }

    /**
     * Attaches add-ons to a stay, refusing the whole set if any line exceeds what is free.
     *
     * Call this inside the caller's transaction — HotelBookingDesk::reserve() does — so a
     * refusal takes the booking down with it rather than leaving a stay half-equipped.
     * The shape mirrors the menu-stock block in POST /students/hotel/orders: lock, fold
     * duplicate lines, check every line, only then write.
     *
     * @param  array  $lines  [['addon_id' => int, 'qty' => int], ...]
     * @throws RuntimeException when a line asks for more than is available
     */
    public static function attachTo(HotelBooking $booking, array $lines): void
    {
        $wanted = self::foldLines($lines);
        if (!$wanted) {
            return;
        }

        // Held until the transaction closes, so two desks registering the last folding bed
        // at the same moment queue up instead of both being told yes.
        $addons = HotelAddon::query()
            ->where('group_name', $booking->group_name)
            ->where('faculty_id', $booking->faculty_id)
            ->whereIn('hotel_addon_id', array_keys($wanted))
            ->lockForUpdate()
            ->get()
            ->keyBy('hotel_addon_id');

        // Recomputed inside the lock: another stay may have taken the last one between the
        // browser rendering the picker and this request arriving.
        $reserved = self::reservedIn($booking->group_name, $booking->faculty_id);

        // Every line is checked before any is written, so a rejected second line cannot
        // leave the first one attached.
        $rows = [];
        $now = now();

        foreach ($wanted as $addonId => $qty) {
            $addon = $addons->get($addonId);
            if (!$addon) {
                throw new RuntimeException('That add-on is no longer available.');
            }

            $available = max(0, (int) $addon->quantity - (int) ($reserved[$addonId] ?? 0));
            if ($qty > $available) {
                throw new RuntimeException('Only ' . $available . ' left of "' . $addon->name . '".');
            }

            $rows[] = [
                'group_name'       => $booking->group_name,
                'faculty_id'       => $booking->faculty_id,
                'group_id'         => $booking->group_id,
                'hotel_booking_id' => $booking->hotel_booking_id,
                'hotel_addon_id'   => $addon->hotel_addon_id,
                // Snapshots: repricing or renaming later must not rewrite this stay.
                'name'             => $addon->name,
                'unit_price'       => (int) $addon->price,
                'qty'              => $qty,
                'created_at'       => $now,
                'updated_at'       => $now,
            ];
        }

        HotelBookingAddon::insert($rows);
    }

    /**
     * The sum itself, taken on the team's scope columns so both the read path (which has a
     * membership) and the assignment path (which has a booking) can share it.
     */
    private static function reservedIn(string $groupName, $facultyId): array
    {
        return HotelBookingAddon::query()
            ->join(
                'hotel_bookings',
                'hotel_booking_addons.hotel_booking_id',
                '=',
                'hotel_bookings.hotel_booking_id'
            )
            ->where('hotel_booking_addons.group_name', $groupName)
            ->where('hotel_booking_addons.faculty_id', $facultyId)
            ->whereIn('hotel_bookings.status', HotelBooking::OPEN_STATUSES)
            ->whereNotNull('hotel_booking_addons.hotel_addon_id')
            ->groupBy('hotel_booking_addons.hotel_addon_id')
            ->selectRaw('hotel_booking_addons.hotel_addon_id as addon_id, SUM(hotel_booking_addons.qty) as out_qty')
            ->pluck('out_qty', 'addon_id')
            ->map(fn ($qty) => (int) $qty)
            ->all();
    }

    /**
     * [addon_id => qty], with the same add-on sent on two lines added together and
     * anything unusable dropped. Quantities are clamped the way order lines are.
     */
    private static function foldLines(array $lines): array
    {
        $wanted = [];

        foreach ($lines as $line) {
            $addonId = (int) ($line['addon_id'] ?? 0);
            $qty     = (int) ($line['qty'] ?? 0);

            if ($addonId <= 0 || $qty <= 0) {
                continue;
            }

            $wanted[$addonId] = ($wanted[$addonId] ?? 0) + min(99, $qty);
        }

        return $wanted;
    }
}
