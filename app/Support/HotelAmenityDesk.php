<?php

namespace App\Support;

use App\Models\ActivityLog;
use App\Models\HotelAmenity;
use App\Models\HotelComplaint;
use App\Models\StudentGroup;
use App\Models\User;
use Illuminate\Support\Facades\DB;

/**
 * Every write that touches both an amenity and its repair goes through here — the same rule
 * HotelHousekeepingDesk applies to an inspection. Requesting a repair writes a complaint and
 * moves the amenity; verifying one reads the complaint before it moves the amenity back.
 * Keeping that in one place is what stops the two from drifting apart.
 *
 * There is deliberately NO hook on the other end. When Maintenance resolves the complaint,
 * nothing here has to run: the amenity stays Under Maintenance either way, and "ready to
 * verify" is worked out on read from the complaint's own status. Adding a write into the
 * complaint PATCH route would only create a second copy of a fact that already has one.
 */
class HotelAmenityDesk
{
    /**
     * The newest repair per amenity for one team, keyed by hotel_amenity_id.
     *
     * One query for the whole list rather than one per row — the Housekeeping table and the
     * site's Amenities page both render every amenity at once.
     *
     * @return array<int, HotelComplaint>
     */
    public static function latestRepairsFor(StudentGroup $membership): array
    {
        return HotelComplaint::where('group_name', $membership->group_name)
            ->where('faculty_id', $membership->faculty_id)
            ->whereNotNull('hotel_amenity_id')
            ->orderBy('hotel_complaint_id')
            ->get()
            // Ascending, so the last write for an amenity is the one left in the map.
            ->keyBy('hotel_amenity_id')
            ->all();
    }

    /** The repair Maintenance is still holding for this amenity, if there is one. */
    public static function openRepairFor(HotelAmenity $amenity): ?HotelComplaint
    {
        return $amenity->repairs()
            ->whereIn('status', HotelAmenity::OPEN_REPAIR_STATUSES)
            ->first();
    }

    /**
     * Housekeeping hands a broken facility to Maintenance.
     *
     * Files it as a maintenance complaint rather than a new table — that queue, its statuses
     * and the Maintenance screen watching it already exist — and parks the amenity at Under
     * Maintenance until a housekeeper verifies the finished work.
     *
     * $issue keys: category (optional, defaults to Furniture / Fixtures), details.
     */
    public static function requestRepair(HotelAmenity $amenity, array $issue, ?User $actor): HotelComplaint
    {
        if (self::openRepairFor($amenity)) {
            throw new \RuntimeException('Maintenance is already working on a repair for this amenity.');
        }

        $details = trim((string) ($issue['details'] ?? ''));
        if ($details === '') {
            throw new \RuntimeException('Describe what needs repairing before sending this to Maintenance.');
        }

        return DB::transaction(function () use ($amenity, $issue, $details, $actor) {
            $complaint = HotelComplaint::create([
                'group_name'       => $amenity->group_name,
                'faculty_id'       => $amenity->faculty_id,
                'group_id'         => $amenity->group_id,
                'hotel_amenity_id' => $amenity->hotel_amenity_id,
                // The complaints board's location column. A facility has no room number, so
                // it carries where the facility is instead — which is what that column is
                // read as anyway.
                'room_number'      => $amenity->location ?: $amenity->name,
                // Nobody's stay. A broken pool is the hotel's problem before it is a guest's.
                'guest_name'       => null,
                'category'         => HotelComplaint::normalizeCategory($issue['category'] ?? 'Furniture / Fixtures'),
                // Always Maintenance: an amenity repair is never Housekeeping's own work to
                // hand back to itself, whatever the category would otherwise route to.
                'department'       => 'maintenance',
                'details'          => $details,
                'status'           => 'Open',
                'filed_by'         => $actor?->name,
            ]);

            $amenity->status = 'Under Maintenance';
            $amenity->save();

            Notifier::complaintFiled($actor, $complaint);
            ActivityLog::record(
                $actor,
                ActivityLog::COMPLAINT_FILED,
                'Requested a repair for ' . $amenity->name . ' and sent it to Maintenance.'
            );

            return $complaint;
        });
    }

    /**
     * The final look after the repair — PROCEDURES.md's "verify before returning to service".
     *
     * Refuses while Maintenance still holds the complaint: Housekeeping cannot reopen a
     * facility nobody has finished fixing. The same guard HotelHousekeepingDesk::complete()
     * puts in front of marking a room ready.
     */
    public static function verifyRepaired(HotelAmenity $amenity, ?User $actor): HotelAmenity
    {
        if (self::openRepairFor($amenity)) {
            throw new \RuntimeException('Maintenance has not finished this repair yet.');
        }

        $amenity->status = 'Available';
        $amenity->save();

        ActivityLog::record(
            $actor,
            ActivityLog::COMPLAINT_RESOLVED,
            'Verified the repair on ' . $amenity->name . ' and reopened it to guests.'
        );

        return $amenity;
    }
}
