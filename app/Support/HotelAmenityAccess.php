<?php

namespace App\Support;

use App\Models\HotelAmenity;
use App\Models\StudentGroup;

/**
 * Authorization + bootstrap for the hotel's facilities list.
 *
 * Housekeeping looks after the facilities, so only they may write to it. Everyone else —
 * the other departments, and a guest browsing the team's site — reads the same rows,
 * which is the whole point: the Amenities page of the site and the Housekeeping screen
 * are one list, not two copies of one.
 */
class HotelAmenityAccess
{
    public const MANAGE_ROLE = 'housekeeping';

    /** Roles that may add / edit amenities. */
    public const MANAGE_ROLES = [self::MANAGE_ROLE, 'administrator'];

    public static function membership(): ?StudentGroup
    {
        $student = auth()->user()?->student;

        return StudentGroupSync::membershipForStudent($student?->user_information_id);
    }

    /**
     * A member may manage amenities when their own team role grants it, or when they are
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
     * First load for a team starts from the five facilities nearly every hotel has, so
     * neither the Housekeeping table nor the site's Amenities page is ever empty. Runs
     * once — after that the DB is the only source of truth, and a team that renamed,
     * re-photographed or closed one of these is never overwritten.
     */
    public static function seedDefaults(StudentGroup $membership): void
    {
        $exists = HotelAmenity::where('group_name', $membership->group_name)
            ->where('faculty_id', $membership->faculty_id)
            ->exists();

        if ($exists) {
            return;
        }

        $now = now();
        $rows = array_map(fn ($amenity) => array_merge($amenity, [
            'group_name' => $membership->group_name,
            'faculty_id' => $membership->faculty_id,
            'group_id'   => $membership->group_id,
            'status'     => 'Available',
            'created_at' => $now,
            'updated_at' => $now,
        ]), self::defaultAmenities());

        HotelAmenity::insert($rows);
    }

    /**
     * Hours and locations are starting points, not facts — every team edits them to match
     * the hotel it is designing. Images are the same deterministic stand-in the add-ons,
     * rooms and menu screens use, so a fresh list looks finished before anyone uploads.
     */
    private static function defaultAmenities(): array
    {
        return [
            [
                'name'        => 'Swimming Pool',
                'description' => 'Outdoor infinity pool with sun loungers, poolside towels and a shallow end for younger guests.',
                'location'    => 'Rooftop, 8th Floor',
                'opens_at'    => '06:00',
                'closes_at'   => '20:00',
                'image'       => 'https://picsum.photos/seed/swimmingpool/800/600.jpg',
            ],
            [
                'name'        => 'Function Room',
                'description' => 'Air-conditioned hall for meetings, seminars and receptions, with projector, sound system and flexible seating.',
                'location'    => '2nd Floor, East Wing',
                'opens_at'    => '08:00',
                'closes_at'   => '22:00',
                'image'       => 'https://picsum.photos/seed/functionroom/800/600.jpg',
            ],
            [
                'name'        => 'Gym',
                'description' => 'Fully equipped fitness centre with cardio machines, free weights and a stretching area.',
                'location'    => 'Ground Floor, Annex',
                'opens_at'    => '05:00',
                'closes_at'   => '22:00',
                'image'       => 'https://picsum.photos/seed/hotelgym/800/600.jpg',
            ],
            [
                'name'        => 'Playground',
                'description' => 'Shaded outdoor play area with swings, slides and soft flooring, in view of the garden seating.',
                'location'    => 'Garden Court',
                'opens_at'    => '07:00',
                'closes_at'   => '18:00',
                'image'       => 'https://picsum.photos/seed/hotelplayground/800/600.jpg',
            ],
            [
                'name'        => 'Spa',
                'description' => 'Massage and wellness treatments in private rooms, by appointment at the front desk.',
                'location'    => '3rd Floor',
                'opens_at'    => '10:00',
                'closes_at'   => '21:00',
                'image'       => 'https://picsum.photos/seed/hotelspa/800/600.jpg',
            ],
        ];
    }
}
