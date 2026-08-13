<?php

namespace App\Support;

use App\Models\HotelAddon;
use App\Models\StudentGroup;

/**
 * Authorization + bootstrap for the room add-ons catalogue.
 *
 * Housekeeping owns the inventory, so only they may write to it. Front Desk reads the
 * same rows when attaching an add-on to a stay, which is the whole point of the feature —
 * the two modules are looking at one list, not two copies of one.
 */
class HotelAddonAccess
{
    public const MANAGE_ROLE = 'housekeeping';

    /** Roles that may add / edit add-ons. */
    public const MANAGE_ROLES = [self::MANAGE_ROLE, 'administrator'];

    public static function membership(): ?StudentGroup
    {
        $student = auth()->user()?->student;

        return StudentGroupSync::membershipForStudent($student?->student_id);
    }

    /**
     * A member may manage add-ons when their own team role grants it, or when they are
     * signed into the hotel site as Housekeeping staff.
     */
    public static function canManage(StudentGroup $membership): bool
    {
        $roles = StudentGroupSync::roleKeys($membership);

        $sim = HotelSimulationAuth::current();
        if (is_array($sim) && ($sim['type'] ?? null) === 'staff' && is_array($sim['roles'] ?? null)) {
            $roles = array_merge($roles, $sim['roles']);
        }

        return count(array_intersect($roles, self::MANAGE_ROLES)) > 0;
    }

    /**
     * First load for a team starts from the two add-ons every hotel keeps, so neither the
     * Housekeeping table nor the Front Desk picker is ever empty. Runs once — after that
     * the DB is the only source of truth.
     */
    public static function seedDefaults(StudentGroup $membership): void
    {
        $exists = HotelAddon::where('group_name', $membership->group_name)
            ->where('faculty_id', $membership->faculty_id)
            ->exists();

        if ($exists) {
            return;
        }

        $now = now();
        $rows = array_map(fn ($addon) => array_merge($addon, [
            'group_name' => $membership->group_name,
            'faculty_id' => $membership->faculty_id,
            'group_id'   => $membership->group_id,
            'created_at' => $now,
            'updated_at' => $now,
        ]), self::defaultAddons());

        HotelAddon::insert($rows);
    }

    private static function defaultAddons(): array
    {
        return [
            ['name' => 'Folding Bed', 'price' => 350, 'quantity' => 10, 'image' => 'https://picsum.photos/seed/foldingbed/800/600.jpg'],
            ['name' => 'Extra Towel', 'price' => 80,  'quantity' => 40, 'image' => 'https://picsum.photos/seed/extratowel/800/600.jpg'],
        ];
    }
}
