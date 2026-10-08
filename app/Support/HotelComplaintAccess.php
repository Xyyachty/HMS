<?php

namespace App\Support;

use App\Models\HotelComplaint;
use App\Models\StudentGroup;

/**
 * Authorization for guest complaints.
 *
 * Front Desk takes the complaint from the guest and closes it once the guest
 * confirms the fix; Housekeeping, Maintenance, Room Management or Restaurant
 * Services works it, and only for the department it was routed to. Everyone else
 * on the team may read the list but not change it — the same shape as
 * HotelOrderAccess.
 */
class HotelComplaintAccess
{
    /** Roles that may record a new complaint. */
    public const FILE_ROLES = ['front_desk', 'administrator'];

    /** Team role that owns each department's queue. */
    public const DEPARTMENT_ROLES = [
        'housekeeping'          => 'housekeeping',
        'maintenance'           => 'maintenance',
        'room_management'       => 'room_management',
        'restaurant_management' => 'restaurant_management',
    ];

    public static function membership(): ?StudentGroup
    {
        $student = auth()->user()?->student;

        return StudentGroupSync::membershipForStudent($student?->user_information_id);
    }

    /**
     * The Simulation-phase roles this member covers — the Room & Maintenance seat
     * runs both — plus whatever role they are signed into the hotel site as.
     */
    public static function roles(StudentGroup $membership): array
    {
        $roles = StudentGroupSync::simulationRoleKeys($membership);

        $sim = HotelSimulationAuth::current();
        if (is_array($sim) && ($sim['type'] ?? null) === 'staff' && is_array($sim['roles'] ?? null)) {
            // The logged-in-as teammate's Simulation seat (their Customization seat on a
            // session older than the split), not yet expanded for this phase.
            $roles = array_merge($roles, HotelTemplateBuilder::rolesForPhase($sim['simulation_roles'] ?? $sim['roles'], HotelTemplateBuilder::PHASE_SIMULATION));
        }

        return $roles;
    }

    public static function canFile(StudentGroup $membership): bool
    {
        return count(array_intersect(self::roles($membership), self::FILE_ROLES)) > 0;
    }

    /**
     * Departments this member may work. An administrator covers all four; everyone else
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
