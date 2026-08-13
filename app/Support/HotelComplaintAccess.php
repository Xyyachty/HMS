<?php

namespace App\Support;

use App\Models\HotelComplaint;
use App\Models\StudentGroup;

/**
 * Authorization for guest complaints.
 *
 * Front Desk takes the complaint from the guest; Maintenance or Housekeeping works
 * it, and only for the department it was routed to. Everyone else on the team may
 * read the list but not change it — the same shape as HotelOrderAccess.
 */
class HotelComplaintAccess
{
    /** Roles that may record a new complaint. */
    public const FILE_ROLES = ['front_desk', 'administrator'];

    /** Team role that owns each department's queue. */
    public const DEPARTMENT_ROLES = [
        'maintenance'  => 'maintenance',
        'housekeeping' => 'housekeeping',
    ];

    public static function membership(): ?StudentGroup
    {
        $student = auth()->user()?->student;

        return StudentGroupSync::membershipForStudent($student?->student_id);
    }

    /**
     * A member's own team role, plus whatever role they are signed into the hotel
     * site as — the same rule the menu and orders use.
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

    public static function canFile(StudentGroup $membership): bool
    {
        return count(array_intersect(self::roles($membership), self::FILE_ROLES)) > 0;
    }

    /**
     * Departments this member may work. An administrator covers both; everyone else
     * only the queue their own role owns.
     */
    public static function handledDepartments(StudentGroup $membership): array
    {
        $roles = self::roles($membership);

        if (in_array('administrator', $roles, true)) {
            return array_keys(HotelComplaint::DEPARTMENTS);
        }

        $departments = [];
        foreach (self::DEPARTMENT_ROLES as $department => $role) {
            if (in_array($role, $roles, true)) {
                $departments[] = $department;
            }
        }

        return $departments;
    }

    /**
     * Whether this member may move a complaint along. Reassigning is what makes the
     * source department matter: a Housekeeping member handing a complaint to
     * Maintenance is authorised by the department they are handing it *from*.
     */
    public static function canHandle(StudentGroup $membership, string $department): bool
    {
        return in_array($department, self::handledDepartments($membership), true);
    }
}
