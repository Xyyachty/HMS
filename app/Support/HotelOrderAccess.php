<?php

namespace App\Support;

use App\Models\StudentGroup;

/**
 * Authorization for room-service food orders.
 *
 * Front Desk takes the order from the guest and then only watches it; Restaurant
 * Services owns every step after that, cooking it and carrying it to the room.
 * Everyone else on the team may read the list but not change it.
 */
class HotelOrderAccess
{
    /**
     * Roles that may place a new order. Front Desk takes it from the guest;
     * Restaurant Services may also log one they received directly.
     */
    public const PLACE_ROLES = ['front_desk', 'restaurant_management', 'administrator'];

    /**
     * Roles that may move an order along the flow, or cancel it. That is the whole
     * flow — Restaurant Services delivers the order as well as cooks it, so there is
     * no step anyone else owns.
     */
    public const FULFILL_ROLES = ['restaurant_management', 'administrator'];

    public static function membership(): ?StudentGroup
    {
        $student = auth()->user()?->student;

        return StudentGroupSync::membershipForStudent($student?->user_information_id);
    }

    /**
     * Every menu row an order's lines could refer to, locked for the duration of
     * the transaction. Matched on id where the browser sent one and on name for
     * older orders placed before line items carried an id.
     */
    public static function lockMenuItemsFor(StudentGroup $membership, array $lines): \Illuminate\Support\Collection
    {
        $ids = array_values(array_filter(array_column($lines, 'menu_item_id')));
        $names = array_values(array_filter(array_column($lines, 'name')));

        return \App\Models\HotelMenuItem::query()
            ->where('group_name', $membership->group_name)
            ->where('faculty_id', $membership->faculty_id)
            ->where(function ($query) use ($ids, $names) {
                if ($ids) {
                    $query->whereIn('hotel_menu_item_id', $ids);
                }
                if ($names) {
                    $query->orWhereIn('name', $names);
                }
            })
            ->lockForUpdate()
            ->get();
    }

    /**
     * Resolve one order line to its menu row. The id wins; the name is only a
     * fallback and is compared case-insensitively, because MySQL matched names
     * that way and PostgreSQL does not.
     */
    public static function matchMenuItem(\Illuminate\Support\Collection $menuItems, array $line): ?\App\Models\HotelMenuItem
    {
        if (!empty($line['menu_item_id'])) {
            $byId = $menuItems->firstWhere('hotel_menu_item_id', (int) $line['menu_item_id']);
            if ($byId) {
                return $byId;
            }
        }

        $name = mb_strtolower(trim((string) ($line['name'] ?? '')));
        if ($name === '') {
            return null;
        }

        return $menuItems->first(fn ($item) => mb_strtolower(trim((string) $item->name)) === $name);
    }

    /**
     * A member's own team role, plus whatever role they are signed into the
     * hotel site as — the same rule the menu uses.
     */
    public static function roles(StudentGroup $membership): array
    {
        $roles = StudentGroupSync::roleKeys($membership);

        $sim = HotelSimulationAuth::current();
        if (is_array($sim) && ($sim['type'] ?? null) === 'staff' && is_array($sim['roles'] ?? null)) {
            $roles = array_merge($roles, $sim['roles']);
        }

        return $roles;
    }

    public static function canPlace(StudentGroup $membership): bool
    {
        return count(array_intersect(self::roles($membership), self::PLACE_ROLES)) > 0;
    }

    /**
     * A dine-in order is taken tableside by Restaurant Management, not handed off by
     * Front Desk the way a room-service order is — Front Desk's part ends at seating
     * the guest. Same role set as canFulfill(), kept as its own method because the
     * two checks answer different questions and may yet diverge.
     */
    public static function canPlaceDineIn(StudentGroup $membership): bool
    {
        return self::canFulfill($membership);
    }

    public static function canFulfill(StudentGroup $membership): bool
    {
        return count(array_intersect(self::roles($membership), self::FULFILL_ROLES)) > 0;
    }
}
