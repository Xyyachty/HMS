<?php

namespace App\Support;

use App\Models\HotelAddon;
use App\Models\HotelAmenity;
use App\Models\HotelBooking;
use App\Models\HotelComplaint;
use App\Models\HotelFoodOrder;
use App\Models\HotelMenuItem;
use App\Models\HotelRoom;
use App\Models\HotelRoomInspection;
use App\Models\StudentGroup;

/**
 * Counts for the badges on the Staff Tools nav.
 *
 * A badge answers one question: how many things are sitting on this desk waiting for
 * the person reading the sidebar? So the counts are always work this role can act on,
 * never a total. A nav item with nothing to queue — Manage Tables, Reports, Dine-in
 * Tables — has no badge at all rather than a permanent zero.
 *
 * Keys are per role, so the same key means different things to different desks:
 * "complaints" is unresolved work to Housekeeping and Maintenance, but to Front Desk
 * it is complaints the department has finished, which is news to pass to the guest.
 */
class HotelNavBadges
{
    /** @return array<string, int> badge key => count, empty when the role has no badges */
    public static function forRole(?StudentGroup $membership, ?string $role): array
    {
        if (!$membership) {
            return [];
        }

        return match ($role) {
            'front_desk' => [
                'verify-guest' => self::awaitingCheckIn($membership),
                'complaints'   => self::complaints($membership, null, 'Resolved'),
            ],
            'room_management' => [
                'guest-details' => self::awaitingCheckIn($membership),
                'manage-room'   => self::roomsOutOfService($membership),
            ],
            'restaurant_management' => [
                'orders'      => self::openOrders($membership),
                'manage-menu' => self::soldOutMenuItems($membership),
            ],
            'housekeeping' => [
                'inspections' => self::inspectionsOnHousekeeping($membership),
                'complaints'  => self::complaints($membership, 'housekeeping', 'Open'),
                'addons'      => self::addonsFullyLentOut($membership),
                'amenities'   => self::amenitiesOnHousekeeping($membership),
            ],
            'maintenance' => [
                'complaints' => self::complaints($membership, 'maintenance', 'Open'),
            ],
            default => [],
        };
    }

    /** Guests who have a booking but are not in their room yet. */
    private static function awaitingCheckIn(StudentGroup $membership): int
    {
        return HotelBooking::where('group_name', $membership->group_name)
            ->where('faculty_id', $membership->faculty_id)
            ->awaitingCheckIn()
            ->count();
    }

    /**
     * Complaints at one status, optionally for one department. Front Desk passes a null
     * department because it filed all of them regardless of who ended up working it.
     */
    private static function complaints(StudentGroup $membership, ?string $department, string $status): int
    {
        return HotelComplaint::where('group_name', $membership->group_name)
            ->where('faculty_id', $membership->faculty_id)
            ->when($department, fn ($query) => $query->where('department', $department))
            ->where('status', $status)
            ->count();
    }

    /** Rooms Room Management cannot sell until someone finishes with them. */
    private static function roomsOutOfService(StudentGroup $membership): int
    {
        return HotelRoom::where('group_name', $membership->group_name)
            ->where('faculty_id', $membership->faculty_id)
            ->whereIn('status', ['Cleaning', 'Maintenance'])
            ->count();
    }

    /**
     * Orders the kitchen still owes someone. Wider than HotelFoodOrder::OPEN_STATUSES:
     * that stops at Ready because the food is cooked, but Restaurant Services carries
     * it to the room now, so a Delivering ticket is still their job to close.
     */
    private static function openOrders(StudentGroup $membership): int
    {
        return HotelFoodOrder::where('group_name', $membership->group_name)
            ->where('faculty_id', $membership->faculty_id)
            ->whereIn('status', ['Preparing', 'Ready', 'Delivering'])
            ->count();
    }

    /** Dishes that cannot be ordered until someone restocks them. */
    private static function soldOutMenuItems(StudentGroup $membership): int
    {
        return HotelMenuItem::where('group_name', $membership->group_name)
            ->where('faculty_id', $membership->faculty_id)
            ->where('stock', '<=', 0)
            ->count();
    }

    /**
     * Rooms whose inspection is Housekeeping's move. "Awaiting Repair" is not counted:
     * that ball is with Maintenance, and it comes back as Awaiting Re-inspection.
     */
    private static function inspectionsOnHousekeeping(StudentGroup $membership): int
    {
        return HotelRoomInspection::where('group_name', $membership->group_name)
            ->where('faculty_id', $membership->faculty_id)
            ->whereIn('status', ['Pending', 'Awaiting Re-inspection'])
            ->count();
    }

    /**
     * Broken facilities that are Housekeeping's move: either nobody has told Maintenance
     * yet, or Maintenance is done and the repair still needs verifying.
     *
     * One under an open repair is not counted, for the same reason inspectionsOnHousekeeping
     * skips "Awaiting Repair" — that ball is with Maintenance, and it comes back on its own
     * when they close the complaint.
     */
    private static function amenitiesOnHousekeeping(StudentGroup $membership): int
    {
        $repairs = HotelAmenityDesk::latestRepairsFor($membership);

        return HotelAmenity::where('group_name', $membership->group_name)
            ->where('faculty_id', $membership->faculty_id)
            ->where('status', 'Under Maintenance')
            ->get()
            ->reject(function ($amenity) use ($repairs) {
                $repair = $repairs[$amenity->hotel_amenity_id] ?? null;

                return $repair && in_array($repair->status, HotelAmenity::OPEN_REPAIR_STATUSES, true);
            })
            ->count();
    }

    /**
     * Add-ons with nothing left to lend. Availability is the quantity owned minus what
     * is out on open stays, which is why this cannot be a plain column comparison.
     */
    private static function addonsFullyLentOut(StudentGroup $membership): int
    {
        $reserved = HotelAddonDesk::reservedFor($membership);

        return HotelAddon::where('group_name', $membership->group_name)
            ->where('faculty_id', $membership->faculty_id)
            ->get()
            ->filter(fn ($addon) => (int) $addon->quantity - (int) ($reserved[$addon->hotel_addon_id] ?? 0) <= 0)
            ->count();
    }
}
