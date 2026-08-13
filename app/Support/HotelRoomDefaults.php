<?php

namespace App\Support;

use App\Models\HotelRoom;
use App\Models\StudentGroup;
use Illuminate\Support\Facades\DB;

/**
 * Room numbering, and the inventory a team starts with.
 *
 * Rooms are named "<Category> <number>" where the number sits in that category's own
 * hundreds block — Classic on the first floor, Superior on the second, and so on, the
 * way a real hotel numbers its doors. A team begins with ten rooms per category
 * (Classic 101-110, Superior 201-210, ...) and every room added afterwards takes the
 * next free number in its category, so the naming never has to be typed or guessed.
 */
class HotelRoomDefaults
{
    /** The hundreds block each category numbers from. */
    public const CATEGORY_FLOORS = [
        'Classic'  => 1,
        'Superior' => 2,
        'Deluxe'   => 3,
        'Premium'  => 4,
        'Family'   => 5,
    ];

    /** Opening rate per 12-hour block, in whole pesos. */
    public const CATEGORY_RATES = [
        'Classic'  => 1500,
        'Superior' => 2500,
        'Deluxe'   => 3500,
        'Premium'  => 5000,
        'Family'   => 6000,
    ];

    public const CATEGORY_DESCRIPTIONS = [
        'Classic'  => 'A comfortable room with the essentials done well.',
        'Superior' => 'More space and a better view than the Classic.',
        'Deluxe'   => 'A generous room with an upgraded bath and seating area.',
        'Premium'  => 'Our finest single room, with the best outlook in the house.',
        'Family'   => 'Room for the whole family, with extra beds and space to spread out.',
    ];

    /** How many rooms per category a new team starts with. */
    public const PER_CATEGORY = 10;

    /**
     * The name the next room in this category should take: the highest number already
     * used there plus one, or the category's own first number when it has none yet.
     *
     * Only names matching "<Category> <number>" count towards the maximum, so a
     * hand-renamed room cannot drag the sequence somewhere unexpected.
     */
    public static function nextNameFor(StudentGroup $membership, string $category): string
    {
        $category = self::normalizeCategory($category);
        $floor = self::CATEGORY_FLOORS[$category] ?? 1;
        $highest = $floor * 100;

        $names = HotelRoom::where('group_name', $membership->group_name)
            ->where('faculty_id', $membership->faculty_id)
            ->where('category', $category)
            ->pluck('name');

        foreach ($names as $name) {
            if (preg_match('/^' . preg_quote($category, '/') . '\s+(\d+)$/i', trim((string) $name), $m)) {
                $highest = max($highest, (int) $m[1]);
            }
        }

        return $category . ' ' . ($highest + 1);
    }

    /**
     * Gives a team its starting inventory, but only if it has none — a team that has
     * already built its own rooms keeps exactly what it has.
     *
     * Called from the rooms index, so a team has its rooms the first time anybody
     * opens a screen that lists them rather than having to be seeded by hand.
     */
    public static function ensureFor(StudentGroup $membership): void
    {
        $scope = [
            'group_name' => $membership->group_name,
            'faculty_id' => $membership->faculty_id,
        ];

        if (HotelRoom::where($scope)->exists()) {
            return;
        }

        DB::transaction(function () use ($membership, $scope) {
            // Re-checked inside the transaction: two screens loading at once would
            // otherwise both find the team empty and seed it twice.
            if (HotelRoom::where($scope)->exists()) {
                return;
            }

            $rows = [];
            $now = now();

            foreach (self::CATEGORY_FLOORS as $category => $floor) {
                for ($i = 1; $i <= self::PER_CATEGORY; $i++) {
                    $rows[] = $scope + [
                        'group_id'    => $membership->group_id,
                        'name'        => $category . ' ' . ($floor * 100 + $i),
                        'category'    => $category,
                        'status'      => 'Available',
                        'price'       => self::CATEGORY_RATES[$category],
                        'description' => self::CATEGORY_DESCRIPTIONS[$category],
                        'image'       => null,
                        'created_at'  => $now,
                        'updated_at'  => $now,
                    ];
                }
            }

            HotelRoom::insert($rows);
        });
    }

    public static function normalizeCategory(?string $value): string
    {
        $raw = mb_strtolower(trim((string) $value));

        foreach (array_keys(self::CATEGORY_FLOORS) as $category) {
            if (mb_strtolower($category) === $raw) {
                return $category;
            }
        }

        return 'Classic';
    }
}
