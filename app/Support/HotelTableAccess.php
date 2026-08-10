<?php

namespace App\Support;

use App\Models\StudentGroup;

/**
 * Authorization for dine-in tables.
 *
 * Restaurant Management owns the table itself — adding, editing, removing, closing
 * it out. Front Desk only ever moves a table from Available to Occupied. Everyone
 * else on the team may read the list but not change it — same shape as
 * HotelOrderAccess / HotelComplaintAccess.
 */
class HotelTableAccess
{
    /** Roles that may create/edit/delete a table and close one out. */
    public const MANAGE_ROLES = ['restaurant_management', 'administrator'];

    /** Roles that may seat a guest at an Available table. */
    public const ASSIGN_ROLES = ['front_desk', 'administrator'];

    public static function membership(): ?StudentGroup
    {
        $student = auth()->user()?->student;

        return StudentGroupSync::membershipForStudent($student?->student_id);
    }

    /**
     * A member's own team role, plus whatever role they are signed into the hotel
     * site as — the same rule the menu, orders and complaints use.
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

    public static function canManage(StudentGroup $membership): bool
    {
        return count(array_intersect(self::roles($membership), self::MANAGE_ROLES)) > 0;
    }

    public static function canAssign(StudentGroup $membership): bool
    {
        return count(array_intersect(self::roles($membership), self::ASSIGN_ROLES)) > 0;
    }
}
