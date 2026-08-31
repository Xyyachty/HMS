<?php

namespace App\Support;

use App\Models\ActivityLog;
use App\Models\HotelAmenity;
use App\Models\HotelAmenityVisit;
use App\Models\HotelBooking;
use App\Models\StudentGroup;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Signing a guest into a facility, and signing them back out.
 *
 * Every refusal lives here rather than in the route, because they are the rules of the
 * feature and not the shape of one HTTP call: a guest who has not checked in is not a
 * guest yet, a pool that is Under Maintenance is shut whatever the clock says, and a gym
 * at capacity is full even to somebody standing at the desk.
 *
 * Each one throws a RuntimeException the route turns into a 422 with the message as-is,
 * the same call HotelHousekeepingDesk::complete() makes. The messages name the facility
 * and say what to do about it, because the person reading them is on the desk with a
 * guest in front of them.
 */
class HotelAmenityVisitDesk
{
    /**
     * Guests the desk may sign in — everyone currently in the hotel.
     *
     * One query with the guest and room loaded, because the picker shows "Ana Cruz ·
     * Room 204" for every one of them and doing that per row would be an N+1.
     */
    public static function inHouseGuests(StudentGroup $membership): Collection
    {
        return HotelBooking::with(['guest', 'room'])
            ->where('group_name', $membership->group_name)
            ->where('faculty_id', $membership->faculty_id)
            ->checkedIn()
            ->get();
    }

    /** Visits still open for one team, newest first — the "who is inside" lists. */
    public static function insideFor(StudentGroup $membership): Collection
    {
        return HotelAmenityVisit::where('group_name', $membership->group_name)
            ->where('faculty_id', $membership->faculty_id)
            ->inside()
            ->orderByDesc('hotel_amenity_visit_id')
            ->get();
    }

    /** How many people are in one facility right now — party sizes, not row count. */
    public static function headcountFor(HotelAmenity $amenity): int
    {
        return (int) $amenity->visits()->inside()->sum('party_size');
    }

    /**
     * Front Desk lets a guest in.
     *
     * The checks run in the order the person at the desk would hit them: is this the kind
     * of facility you sign into at all, is it open, is this a real checked-in guest, are
     * they already inside, is there room.
     */
    public static function registerEntry(
        HotelAmenity $amenity,
        HotelBooking $booking,
        array $data,
        ?User $actor
    ): HotelAmenityVisit {
        if ($amenity->access_type !== 'registered') {
            throw new \RuntimeException(
                $amenity->name . ' is ' . mb_strtolower($amenity->accessLabel())
                    . ' — there is nothing to register here.'
            );
        }

        if ($amenity->status !== 'Available') {
            throw new \RuntimeException($amenity->name . ' is ' . mb_strtolower($amenity->status) . '.');
        }

        if (!$amenity->isOpenNow()) {
            $hours = $amenity->hoursLabel();
            throw new \RuntimeException(
                $amenity->name . ' is closed right now.' . ($hours ? ' Open ' . $hours . '.' : '')
            );
        }

        if ($booking->status !== 'Checked In') {
            throw new \RuntimeException('That guest has not checked in yet.');
        }

        $partySize = max(1, (int) ($data['party_size'] ?? 1));

        return DB::transaction(function () use ($amenity, $booking, $data, $partySize, $actor) {
            // Lock the amenity, not the visits. Two desks polling the same screen could
            // otherwise both read a gym with one space left and both let a party in — and
            // locking the visit rows would not stop that, because Postgres takes no lock
            // over rows that do not exist yet. Every registration for this facility
            // queues behind this one row instead.
            HotelAmenity::whereKey($amenity->hotel_amenity_id)->lockForUpdate()->first();

            $alreadyInside = HotelAmenityVisit::where('hotel_amenity_id', $amenity->hotel_amenity_id)
                ->where('hotel_booking_id', $booking->hotel_booking_id)
                ->inside()
                ->exists();

            if ($alreadyInside) {
                throw new \RuntimeException(
                    ($booking->guest?->full_name ?: 'That guest') . ' is already registered in ' . $amenity->name . '.'
                );
            }

            if ($amenity->capacity !== null) {
                $headcount = (int) HotelAmenityVisit::where('hotel_amenity_id', $amenity->hotel_amenity_id)
                    ->inside()
                    ->sum('party_size');

                if ($headcount + $partySize > $amenity->capacity) {
                    $free = max(0, $amenity->capacity - $headcount);
                    throw new \RuntimeException(
                        $amenity->name . ' is at capacity (' . $amenity->capacity . ').'
                            . ($free > 0 ? ' Room for ' . $free . ' more.' : '')
                    );
                }
            }

            $visit = HotelAmenityVisit::create([
                'group_name'       => $amenity->group_name,
                'faculty_id'       => $amenity->faculty_id,
                'group_id'         => $amenity->group_id,
                'hotel_amenity_id' => $amenity->hotel_amenity_id,
                'hotel_booking_id' => $booking->hotel_booking_id,
                // Snapshots: renaming the Gym must not rewrite last month's register.
                'amenity_name'     => $amenity->name,
                'guest_name'       => $booking->guest?->full_name ?: 'Guest',
                'room_name'        => $booking->room?->name,
                'party_size'       => $partySize,
                'entered_at'       => now(),
                'registered_by'    => $actor?->name,
                'notes'            => self::clean($data['notes'] ?? null),
            ]);

            ActivityLog::record(
                $actor,
                ActivityLog::TABLE_ASSIGNED,
                'Registered ' . $visit->guest_name . ' into ' . $amenity->name . '.'
            );

            return $visit;
        });
    }

    /** The guest comes back out. Stamping exited_at is what closes the visit. */
    public static function registerExit(HotelAmenityVisit $visit, ?User $actor): HotelAmenityVisit
    {
        if (!$visit->isInside()) {
            throw new \RuntimeException($visit->guest_name . ' has already been signed out.');
        }

        $visit->exited_at = now();
        $visit->closed_by = $actor?->name;
        $visit->save();

        ActivityLog::record(
            $actor,
            ActivityLog::TABLE_CLOSED,
            'Signed ' . $visit->guest_name . ' out of ' . $visit->amenity_name
                . ' after ' . $visit->minutesSpent() . ' minutes.'
        );

        return $visit;
    }

    private static function clean($value): ?string
    {
        $value = trim((string) $value);

        return $value === '' ? null : $value;
    }
}
