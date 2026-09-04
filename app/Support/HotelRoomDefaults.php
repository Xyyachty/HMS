<?php

namespace App\Support;

use App\Models\HotelRoom;
use App\Models\HotelRoomCategory;
use App\Models\StudentGroup;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Facades\DB;

/**
 * Room numbering, and the inventory a team starts with.
 *
 * Rooms are named "<Category> <number>" where the number sits in that category's own
 * hundreds block — Classic on the first floor, Superior on the second, and so on, the
 * way a real hotel numbers its doors. A team begins with ten rooms per category
 * (Classic 101-110, Superior 201-210, ...) and every room added afterwards takes the
 * next free number in its category, so the naming never has to be typed or guessed.
 *
 * The five categories below are only where a team starts. Room Management can add its
 * own from the Rooms section, and those live in hotel_room_categories; every method
 * here that asks "what categories are there" reads the defaults and the team's own
 * together, so a room in a category somebody invented numbers itself the same way.
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
        $category = self::normalizeCategory($category, $membership);
        $floor = self::floorsFor($membership)[$category] ?? 1;
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

    /**
     * The category name as it is spelled in the team's list, whatever case it arrived in.
     *
     * Without a team only the five defaults are known, so anything else falls back to
     * Classic — the same answer this gave before teams could add categories of their own.
     */
    public static function normalizeCategory(?string $value, ?StudentGroup $membership = null): string
    {
        $raw = mb_strtolower(trim((string) $value));
        $known = $membership ? array_keys(self::floorsFor($membership)) : array_keys(self::CATEGORY_FLOORS);

        foreach ($known as $category) {
            if (mb_strtolower($category) === $raw) {
                return $category;
            }
        }

        return 'Classic';
    }

    /** Every category the team has, defaults first, as name => hundreds block. */
    public static function floorsFor(StudentGroup $membership): array
    {
        $floors = self::CATEGORY_FLOORS;

        foreach (self::customCategories($membership->group_name, $membership->faculty_id) as $category) {
            $floors[$category->name] = $category->floor_number;
        }

        return $floors;
    }

    /**
     * The category list the browser works from: name, opening rate and description, in
     * the order the tabs should appear.
     */
    public static function categoriesFor(StudentGroup $membership): array
    {
        return self::categoriesForTeam($membership->group_name, $membership->faculty_id);
    }

    /**
     * The same list for a team read by name rather than through a membership — the
     * published portfolio serves visitors who have no membership of their own.
     */
    public static function categoriesForTeam(string $groupName, $facultyId): array
    {
        $categories = [];

        foreach (self::CATEGORY_FLOORS as $name => $floor) {
            $categories[] = [
                'name' => $name,
                // The hundreds block goes down with it so the Add Room form can preview
                // the number the room will be given without guessing at it.
                'floor' => $floor,
                'rate' => self::CATEGORY_RATES[$name],
                'description' => self::CATEGORY_DESCRIPTIONS[$name],
            ];
        }

        foreach (self::customCategories($groupName, $facultyId) as $category) {
            $categories[] = [
                'name' => $category->name,
                'floor' => $category->floor_number,
                'rate' => $category->rate,
                'description' => $category->description,
            ];
        }

        return $categories;
    }

    /**
     * Adds a category to the team's list and hands it back.
     *
     * Returns null when the name is already taken — case and surrounding spaces do not
     * make a second category, or the same rooms would end up split across two tabs.
     */
    public static function createCategory(
        StudentGroup $membership,
        string $name,
        ?int $rate = null,
        ?string $description = null
    ): ?HotelRoomCategory {
        $clean = trim($name);
        if ($clean === '') {
            return null;
        }

        $floors = self::floorsFor($membership);

        foreach (array_keys($floors) as $existing) {
            if (mb_strtolower($existing) === mb_strtolower($clean)) {
                return null;
            }
        }

        try {
            return HotelRoomCategory::create([
                'group_name'   => $membership->group_name,
                'faculty_id'   => $membership->faculty_id,
                'group_id'     => $membership->group_id,
                'name'         => $clean,
                // The next free hundreds block, so its rooms number past every category
                // already in use rather than into one of them.
                'floor_number' => (int) max($floors) + 1,
                'rate'         => $rate ?? 0,
                'description'  => $description,
            ]);
        } catch (UniqueConstraintViolationException $e) {
            // Two members adding the same category at once: the check above passed for
            // both, and the index caught the loser. That is a taken name, not a fault.
            return null;
        }
    }

    /** @return \Illuminate\Support\Collection<int, HotelRoomCategory> */
    private static function customCategories(string $groupName, $facultyId)
    {
        return HotelRoomCategory::where('group_name', $groupName)
            ->where('faculty_id', $facultyId)
            ->orderBy('floor_number')
            ->get();
    }
}
