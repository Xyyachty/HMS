<?php

namespace App\Support;

use App\Models\HotelAmenity;
use App\Models\HotelAmenityService;
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

    /**
     * Roles that may sign a guest into a facility and back out of it.
     *
     * Deliberately not Housekeeping: they own what the facility *is* — its hours, its
     * photo, whether it is open at all — while letting a guest in is desk work, done by
     * whoever is standing in front of the guest. The same split the add-ons catalogue
     * already makes, where Housekeeping owns the list and Front Desk attaches it to a stay.
     */
    public const REGISTER_ROLES = ['front_desk', 'administrator'];

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
        return count(array_intersect(self::roles($membership), self::MANAGE_ROLES)) > 0;
    }

    /**
     * A member may change the facilities when they hold the role. That is all.
     *
     * This briefly also required the "Customize Hotel Amenities" task to be
     * assigned and to be theirs, which locked the section for every team whose
     * faculty had not handed it out — including teams working ahead of the
     * checklist, which is most of them. The task remains on the checklist as work
     * to be reviewed; it no longer decides who may open the screen.
     *
     * Kept as its own name rather than folded into canManage() so the routes still
     * read as "may this person customize", and so the rule has one place to change
     * if it is ever tightened again.
     */
    public static function canCustomize(StudentGroup $membership, $user = null): bool
    {
        return self::canManage($membership);
    }

    /** A member may register guests into a facility when they are on the front desk. */
    public static function canRegister(StudentGroup $membership): bool
    {
        return count(array_intersect(self::roles($membership), self::REGISTER_ROLES)) > 0;
    }

    /**
     * The member's own team roles, plus whatever they are signed into the hotel site as.
     * Extracted so canManage and canRegister cannot drift apart.
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
            // Teams that were seeded before the Spa had a treatment list still need one,
            // so this runs for them too. It carries its own guard.
            self::seedSpaServices($membership);

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

        self::seedSpaServices($membership);
    }

    /**
     * The Spa is useless without something to book at it, so the treatments arrive with
     * it. Attached to whichever amenity is the seeded Spa rather than created free-
     * standing, because a service belongs to the facility that gives it.
     */
    private static function seedSpaServices(StudentGroup $membership): void
    {
        $spa = HotelAmenity::where('group_name', $membership->group_name)
            ->where('faculty_id', $membership->faculty_id)
            ->where('name', 'Spa')
            ->first();

        if (!$spa) {
            return;
        }

        $alreadyHasServices = HotelAmenityService::where('hotel_amenity_id', $spa->hotel_amenity_id)->exists();
        if ($alreadyHasServices) {
            return;
        }

        $now = now();
        $rows = array_map(fn ($service) => array_merge($service, [
            'group_name'       => $membership->group_name,
            'faculty_id'       => $membership->faculty_id,
            'group_id'         => $membership->group_id,
            'hotel_amenity_id' => $spa->hotel_amenity_id,
            'is_active'        => true,
            'created_at'       => $now,
            'updated_at'       => $now,
        ]), [
            ['name' => 'Swedish Massage', 'description' => 'Full-body relaxation massage with warm oil.', 'duration_minutes' => 60, 'price' => 1200],
            ['name' => 'Foot Spa',        'description' => 'Soak, scrub and foot massage.',              'duration_minutes' => 45, 'price' => 800],
            ['name' => 'Body Scrub',      'description' => 'Exfoliating salt scrub and rinse.',          'duration_minutes' => 60, 'price' => 1000],
            ['name' => 'Facial',          'description' => 'Cleanse, steam and hydrating mask.',         'duration_minutes' => 45, 'price' => 950],
        ]);

        HotelAmenityService::insert($rows);
    }

    /**
     * Hours and locations are starting points, not facts — every team edits them to match
     * the hotel it is designing. Images are the same deterministic stand-in the add-ons,
     * rooms and menu screens use, so a fresh list looks finished before anyone uploads.
     *
     * Each one carries the access type its docblock in HotelAmenity describes, because
     * that is what decides how a guest gets at it: the pool and the gym keep a register,
     * the spa is booked by treatment, the function room by the date, and the playground
     * is walked into. Left unset they all defaulted to open, which is the one type
     * nothing can be booked under.
     */
    private static function defaultAmenities(): array
    {
        return [
            [
                'name'        => 'Swimming Pool',
                'access_type' => 'registered',
                'description' => 'Outdoor infinity pool with sun loungers, poolside towels and a shallow end for younger guests.',
                'location'    => 'Rooftop, 8th Floor',
                'opens_at'    => '06:00',
                'closes_at'   => '20:00',
                'image'       => 'https://picsum.photos/seed/swimmingpool/800/600.jpg',
            ],
            [
                'name'        => 'Function Room',
                'access_type' => 'event',
                'capacity'    => 80,
                'description' => 'Air-conditioned hall for meetings, seminars and receptions, with projector, sound system and flexible seating.',
                'location'    => '2nd Floor, East Wing',
                'opens_at'    => '08:00',
                'closes_at'   => '22:00',
                'image'       => 'https://picsum.photos/seed/functionroom/800/600.jpg',
            ],
            [
                'name'        => 'Gym',
                'access_type' => 'registered',
                'description' => 'Fully equipped fitness centre with cardio machines, free weights and a stretching area.',
                'location'    => 'Ground Floor, Annex',
                'opens_at'    => '05:00',
                'closes_at'   => '22:00',
                'image'       => 'https://picsum.photos/seed/hotelgym/800/600.jpg',
            ],
            [
                'name'        => 'Playground',
                'access_type' => 'open',
                'description' => 'Shaded outdoor play area with swings, slides and soft flooring, in view of the garden seating.',
                'location'    => 'Garden Court',
                'opens_at'    => '07:00',
                'closes_at'   => '18:00',
                'image'       => 'https://picsum.photos/seed/hotelplayground/800/600.jpg',
            ],
            [
                'name'        => 'Spa',
                'access_type' => 'appointment',
                'description' => 'Massage and wellness treatments in private rooms, by appointment at the front desk.',
                'location'    => '3rd Floor',
                'opens_at'    => '10:00',
                'closes_at'   => '21:00',
                'image'       => 'https://picsum.photos/seed/hotelspa/800/600.jpg',
            ],
        ];
    }
}
