<?php

namespace App\Support;

use App\Models\HotelCateringPackage;
use App\Models\StudentGroup;

/**
 * Authorization + bootstrap for the catering packages catalogue.
 *
 * Restaurant Services own it, because it is food and they are the ones who cook it. Front
 * Desk reads the same rows when booking a function room and cannot write to them — which
 * is the whole point of the requirement that catering "comes from the existing Restaurant
 * Management module" rather than being typed into an event by whoever took the booking.
 *
 * The same split HotelAddonAccess makes between Housekeeping and Front Desk.
 */
class HotelCateringAccess
{
    public const MANAGE_ROLE = 'restaurant_management';

    /** Roles that may add / edit catering packages. */
    public const MANAGE_ROLES = [self::MANAGE_ROLE, 'administrator'];

    public static function membership(): ?StudentGroup
    {
        $student = auth()->user()?->student;

        return StudentGroupSync::membershipForStudent($student?->user_information_id);
    }

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
     * A team's first look at the catering tab starts from three packages rather than an
     * empty table, so Front Desk can book a catered event on day one and Restaurant
     * Services have something to edit instead of something to invent. Runs once.
     */
    public static function seedDefaults(StudentGroup $membership): void
    {
        $exists = HotelCateringPackage::where('group_name', $membership->group_name)
            ->where('faculty_id', $membership->faculty_id)
            ->exists();

        if ($exists) {
            return;
        }

        $now = now();
        $rows = array_map(fn ($package) => array_merge($package, [
            'group_name' => $membership->group_name,
            'faculty_id' => $membership->faculty_id,
            'group_id'   => $membership->group_id,
            'is_active'  => true,
            'created_at' => $now,
            'updated_at' => $now,
        ]), self::defaultPackages());

        HotelCateringPackage::insert($rows);
    }

    private static function defaultPackages(): array
    {
        return [
            [
                'name'           => 'Standard Buffet',
                'description'    => 'Everyday buffet spread for meetings and family gatherings.',
                'inclusions'     => "Rice · 2 main dishes · 1 vegetable · Soup · Dessert · Iced tea",
                'price_per_head' => 450,
                'min_guests'     => 30,
                'image'          => 'https://picsum.photos/seed/cateringstandard/800/600.jpg',
            ],
            [
                'name'           => 'Premium Buffet',
                'description'    => 'Wider spread with a carving station, for receptions and launches.',
                'inclusions'     => "Rice · 3 main dishes · Carving station · Salad bar · Soup · 2 desserts · Drinks",
                'price_per_head' => 750,
                'min_guests'     => 50,
                'image'          => 'https://picsum.photos/seed/cateringpremium/800/600.jpg',
            ],
            [
                'name'           => 'Plated Set Menu',
                'description'    => 'Served to the table, course by course. For weddings and formal dinners.',
                'inclusions'     => "Appetizer · Soup · Main course · Dessert · Wine service",
                'price_per_head' => 950,
                'min_guests'     => 20,
                'image'          => 'https://picsum.photos/seed/cateringplated/800/600.jpg',
            ],
        ];
    }
}
