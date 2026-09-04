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
 * The five categories below are only where a team starts. On a team's first visit they
 * are written into hotel_room_categories as ordinary rows (see ensureCategoriesFor), so
 * from then on that table is the whole list and a default can be renamed like any other.
 * The constants stay as the seed, and as the answer for a caller with no team of its own
 * — a visitor reading a published site before anybody has opened the editor.
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
     * Anything unrecognised falls back to the team's first category rather than to a
     * literal "Classic", which stops being an answer the moment somebody renames it.
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

        return $known[0] ?? 'Classic';
    }

    /**
     * Writes the five starting categories into the team's own list.
     *
     * Until a team has rows of its own the defaults are read off the constants, which
     * cannot be renamed. Materialising them is what makes "Classic" an ordinary row that
     * Room Management can rename from the Rooms tab bar. Rooms are untouched: they
     * already carry these names.
     *
     * Missing slots are filled one at a time rather than the whole set written once,
     * because a team may already hold a category it invented — that row is not the five
     * defaults arriving, and skipping on "the table is not empty" would leave such a team
     * with only its own category and none of the ones its rooms are actually named after.
     *
     * A slot is a hundreds block, not a name: a team that renamed Classic to Standard
     * still occupies floor 1, so nothing re-creates "Classic" underneath it.
     */
    public static function ensureCategoriesFor(StudentGroup $membership): void
    {
        $scope = [
            'group_name' => $membership->group_name,
            'faculty_id' => $membership->faculty_id,
        ];

        $existing = HotelRoomCategory::where($scope)->get(['name', 'floor_number']);
        $takenFloors = $existing->pluck('floor_number')->map(fn ($floor) => (int) $floor)->all();
        $takenNames = $existing->pluck('name')->map(fn ($name) => mb_strtolower((string) $name))->all();

        $rows = [];
        $now = now();

        foreach (self::CATEGORY_FLOORS as $name => $floor) {
            if (in_array($floor, $takenFloors, true) || in_array(mb_strtolower($name), $takenNames, true)) {
                continue;
            }

            $rows[] = $scope + [
                'group_id'     => $membership->group_id,
                'name'         => $name,
                'floor_number' => $floor,
                'rate'         => self::CATEGORY_RATES[$name],
                'description'  => self::CATEGORY_DESCRIPTIONS[$name],
                'created_at'   => $now,
                'updated_at'   => $now,
            ];
        }

        if (empty($rows)) {
            return;
        }

        try {
            HotelRoomCategory::insert($rows);
        } catch (UniqueConstraintViolationException $e) {
            // Two screens opening at once: both found the same slot free, the index caught
            // the loser. The rows exist either way, which is all this promised.
        }
    }

    /** Every category the team has, as name => hundreds block. */
    public static function floorsFor(StudentGroup $membership): array
    {
        return self::floorsForTeam($membership->group_name, $membership->faculty_id);
    }

    /** The same by team name, for the callers that have no membership to read. */
    public static function floorsForTeam(string $groupName, $facultyId): array
    {
        $floors = [];

        foreach (self::categoriesForTeam($groupName, $facultyId) as $category) {
            $floors[$category['name']] = $category['floor'];
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
        $rows = self::categoryRows($groupName, $facultyId);
        $takenFloors = $rows->pluck('floor_number')->map(fn ($floor) => (int) $floor)->all();
        $takenNames = $rows->pluck('name')->map(fn ($name) => mb_strtolower((string) $name))->all();

        $categories = [];

        // A default still stands wherever no row has taken over its hundreds block. That
        // is what a renamed default is — the same slot under another name — so this drops
        // "Classic" as soon as a row occupies floor 1, and keeps it for a team whose rows
        // have never been written (ensureCategoriesFor has not run, or a visitor is
        // reading a published site, which must not write anything to look at it).
        foreach (self::CATEGORY_FLOORS as $name => $floor) {
            if (in_array($floor, $takenFloors, true) || in_array(mb_strtolower($name), $takenNames, true)) {
                continue;
            }

            $categories[] = [
                'name' => $name,
                // The hundreds block goes down with it so the Add Room form can preview
                // the number the room will be given without guessing at it.
                'floor' => $floor,
                'rate' => self::CATEGORY_RATES[$name],
                'description' => self::CATEGORY_DESCRIPTIONS[$name],
            ];
        }

        foreach ($rows as $category) {
            $categories[] = [
                'name' => $category->name,
                'floor' => (int) $category->floor_number,
                'rate' => $category->rate,
                'description' => $category->description,
            ];
        }

        // Tab order is the hundreds block, so a renamed category keeps its place rather
        // than jumping to the end of the bar.
        usort($categories, fn ($a, $b) => $a['floor'] <=> $b['floor']);

        return $categories;
    }

    /**
     * Renames one of the team's categories, and everything named after it.
     *
     * A room is called "<Category> <number>", so leaving the rooms alone would strand a
     * "Standard" tab full of rooms still called "Classic 101". The rooms move with the
     * name; only the ones that follow the convention are rewritten, so a hand-typed name
     * keeps whatever it was given.
     *
     * Returns the stored spelling of the new name, or null when the name is taken or the
     * category being renamed is not one of the team's.
     */
    public static function renameCategory(StudentGroup $membership, string $from, string $to): ?string
    {
        self::ensureCategoriesFor($membership);

        $clean = trim($to);
        if ($clean === '') {
            return null;
        }

        $current = self::normalizeCategory($from, $membership);
        if (mb_strtolower($current) !== mb_strtolower(trim($from))) {
            // normalizeCategory() answers with the team's first category when it does not
            // recognise the name; that is a rename of something this team never had.
            return null;
        }

        foreach (array_keys(self::floorsFor($membership)) as $existing) {
            if (mb_strtolower($existing) === mb_strtolower($clean) && mb_strtolower($existing) !== mb_strtolower($current)) {
                return null;
            }
        }

        // Same name in different clothes — "classic" to "Classic". Nothing to move.
        if ($current === $clean) {
            return $current;
        }

        // Read before the rename: afterwards the old name is not in the list to look up.
        $floor = (int) (self::floorsFor($membership)[$current] ?? 0);

        $scope = [
            'group_name' => $membership->group_name,
            'faculty_id' => $membership->faculty_id,
        ];

        try {
            DB::transaction(function () use ($membership, $scope, $current, $clean, $floor) {
                $updated = HotelRoomCategory::where($scope)
                    ->where('name', $current)
                    ->update(['name' => $clean]);

                // No row to rename: ensureCategoriesFor() lost a race to another request,
                // so this default has never been written down. Write it under its new name
                // rather than leaving the rooms below pointing at a category that is not
                // in the list — they would then belong to no tab at all.
                if ($updated === 0) {
                    HotelRoomCategory::create($scope + [
                        'group_id'     => $membership->group_id,
                        'name'         => $clean,
                        'floor_number' => $floor ?: ((int) max(self::CATEGORY_FLOORS) + 1),
                        'rate'         => self::CATEGORY_RATES[$current] ?? 0,
                        'description'  => self::CATEGORY_DESCRIPTIONS[$current] ?? null,
                    ]);
                }

                $rooms = HotelRoom::where($scope)
                    ->where('category', $current)
                    ->get();

                foreach ($rooms as $room) {
                    $attributes = ['category' => $clean];

                    if (preg_match('/^' . preg_quote($current, '/') . '\s+(\d+)$/i', trim((string) $room->name), $m)) {
                        $attributes['name'] = $clean . ' ' . $m[1];
                    }

                    $room->update($attributes);
                }
            });
        } catch (UniqueConstraintViolationException $e) {
            // Somebody else took the name between the check above and the write.
            return null;
        }

        return $clean;
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

    /**
     * The team's own category rows, in tab order. Empty for a team that has not been
     * through ensureCategoriesFor() yet, which is what every caller here reads as
     * "still on the defaults".
     *
     * @return \Illuminate\Support\Collection<int, HotelRoomCategory>
     */
    private static function categoryRows(string $groupName, $facultyId)
    {
        return HotelRoomCategory::where('group_name', $groupName)
            ->where('faculty_id', $facultyId)
            ->orderBy('floor_number')
            ->get();
    }
}
