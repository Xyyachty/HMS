<?php

namespace App\Support;

use App\Models\StudentGroup;

/**
 * Authorization for the post-checkout inspection queue.
 *
 * Anyone on the team may read it — the room card and the Front Desk both benefit from
 * seeing where a room stands. Only Housekeeping may inspect, record a finding, report
 * an issue, or complete the pass. Same shape as HotelComplaintAccess.
 */
class HotelHousekeepingAccess
{
    /** Roles that may work the inspection queue. */
    public const INSPECT_ROLES = ['housekeeping', 'administrator'];

    public static function membership(): ?StudentGroup
    {
        $student = auth()->user()?->student;

        return StudentGroupSync::membershipForStudent($student?->user_information_id);
    }

    /**
     * The Simulation-phase roles this member covers — a Room Management seat also
     * runs Housekeeping here — plus whatever role they are signed into the hotel
     * site as. Same rule the complaints and orders queues use.
     */
    public static function roles(StudentGroup $membership): array
    {
        $roles = StudentGroupSync::simulationRoleKeys($membership);

        $sim = HotelSimulationAuth::current();
        if (is_array($sim) && ($sim['type'] ?? null) === 'staff' && is_array($sim['roles'] ?? null)) {
            // $sim['roles'] is the logged-in-as teammate's stored seat, not yet
            // expanded for this phase.
            $roles = array_merge($roles, HotelTemplateBuilder::rolesForPhase($sim['roles'], HotelTemplateBuilder::PHASE_SIMULATION));
        }

        return $roles;
    }

    public static function canInspect(StudentGroup $membership): bool
    {
        return count(array_intersect(self::roles($membership), self::INSPECT_ROLES)) > 0;
    }
}
