<?php

namespace App\Support;

use App\Models\HotelMenuCategory;
use App\Models\HotelMenuItem;
use App\Models\StudentGroup;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Facades\DB;

/**
 * The menu courses a team works in.
 *
 * The five below are only where a team starts. On its first visit they are written
 * into hotel_menu_categories as ordinary rows (ensureCategoriesFor), so from then on
 * that table is the whole list and a default can be renamed like any other. The
 * constants stay as the seed, and as the answer for a caller with no team of its own —
 * a visitor reading a published site before anybody has opened the editor, and any
 * request at all before the migration has landed.
 *
 * This is HotelRoomDefaults' counterpart for the Restaurant page, and it deliberately
 * follows the same shape: a course is a slot, renaming one leaves its slot taken, and
 * a course a team invents takes the next free slot.
 */
class HotelMenuDefaults
{
    /** The courses every team starts with, in the order the tabs read. */
    public const CATEGORIES = [
        'Main Dishes' => 1,
        'Appetizers'  => 2,
        'Soups'       => 3,
        'Desserts'    => 4,
        'Beverages'   => 5,
    ];

    /** The starting list, for a caller with no team — or no table yet. */
    public static function defaultNames(): array
    {
        return array_keys(self::CATEGORIES);
    }

    /**
     * Writes the five starting courses into the team's own list.
     *
     * Filled slot by slot rather than written in one go: a team may already hold a
     * course it invented, and skipping on "the table is not empty" would leave it with
     * only that one and none of the courses its dishes are actually filed under.
     */
    public static function ensureCategoriesFor(StudentGroup $membership): void
    {
        if (!HotelMenuCategory::tableReady() || !filled($membership->group_name)) {
            return;
        }

        $scope = [
            'group_name' => $membership->group_name,
            'faculty_id' => $membership->faculty_id,
        ];

        $existing = HotelMenuCategory::where($scope)->get(['name', 'position']);
        $takenSlots = $existing->pluck('position')->map(fn ($slot) => (int) $slot)->all();
        $takenNames = $existing->pluck('name')->map(fn ($name) => mb_strtolower((string) $name))->all();

        $rows = [];
        $now = now();

        foreach (self::CATEGORIES as $name => $slot) {
            if (in_array($slot, $takenSlots, true) || in_array(mb_strtolower($name), $takenNames, true)) {
                continue;
            }

            $rows[] = $scope + [
                'group_id'   => $membership->group_id,
                'name'       => $name,
                'position'   => $slot,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        if ($rows === []) {
            return;
        }

        try {
            HotelMenuCategory::insert($rows);
        } catch (UniqueConstraintViolationException $e) {
            // Two of the team's tabs opened at once and both seeded. The other one
            // won; the list is the same either way.
        }
    }

    /** The team's courses, in tab order. */
    public static function categoriesFor(StudentGroup $membership): array
    {
        return self::categoriesForTeam($membership->group_name, $membership->faculty_id);
    }

    /**
     * The same list for a team read by name — the published site serves visitors who
     * have no membership of their own, and must not write anything to be looked at.
     */
    public static function categoriesForTeam(?string $groupName, $facultyId): array
    {
        if (!HotelMenuCategory::tableReady() || !filled($groupName)) {
            return self::defaultNames();
        }

        $rows = HotelMenuCategory::where('group_name', $groupName)
            ->where('faculty_id', $facultyId)
            ->orderBy('position')
            ->orderBy('hotel_menu_category_id')
            ->get(HotelMenuCategory::supportsRemoval() ? ['name', 'position', 'removed_at'] : ['name', 'position']);

        if ($rows->isEmpty()) {
            // Nobody has opened the editor for this team yet.
            return self::defaultNames();
        }

        $takenSlots = $rows->pluck('position')->map(fn ($slot) => (int) $slot)->all();
        $takenNames = $rows->pluck('name')->map(fn ($name) => mb_strtolower((string) $name))->all();

        // slot => name, so the whole list can be sorted by slot in one pass at the end.
        $bySlot = [];

        /* A default still stands wherever no row has taken over its slot. That is what
           a renamed default is — the same slot under another name — so this drops
           "Desserts" as soon as a row holds slot 4, and keeps it for a team seeded
           before that slot existed. */
        foreach (self::CATEGORIES as $name => $slot) {
            if (in_array($slot, $takenSlots, true) || in_array(mb_strtolower($name), $takenNames, true)) {
                continue;
            }
            $bySlot[] = ['slot' => $slot, 'name' => $name];
        }

        foreach ($rows as $row) {
            // A removed course still holds its slot above, so a starting one does
            // not reappear in its place, but is not listed.
            if ($row->isRemoved()) {
                continue;
            }
            $bySlot[] = ['slot' => (int) $row->position, 'name' => (string) $row->name];
        }

        usort($bySlot, fn ($a, $b) => $a['slot'] <=> $b['slot']);

        $names = [];
        foreach ($bySlot as $entry) {
            if ($entry['name'] !== '' && !in_array($entry['name'], $names, true)) {
                $names[] = $entry['name'];
            }
        }

        return $names;
    }

    /**
     * The course a dish should be filed under, resolved against the team's own list so
     * a course they invented is not collapsed back into the first default.
     *
     * Falls back to the team's first course, which is what an unrecognised name meant
     * before there were per-team courses at all.
     */
    public static function normalizeCategory(?string $value, ?StudentGroup $membership = null): string
    {
        $raw = mb_strtolower(trim((string) $value));
        $known = $membership
            ? self::categoriesFor($membership)
            : self::defaultNames();

        foreach ($known as $category) {
            if (mb_strtolower($category) === $raw) {
                return $category;
            }
        }

        return $known[0] ?? 'Main Dishes';
    }

    /**
     * Adds a course to the team's menu. Returns the stored row, or null when the name
     * is already taken — the tab bar keeps the dialog open and says so.
     */
    public static function createCategory(StudentGroup $membership, string $name): ?HotelMenuCategory
    {
        if (!HotelMenuCategory::tableReady()) {
            return null;
        }

        $clean = trim($name);
        if ($clean === '') {
            return null;
        }

        self::ensureCategoriesFor($membership);

        $scope = [
            'group_name' => $membership->group_name,
            'faculty_id' => $membership->faculty_id,
        ];

        $taken = HotelMenuCategory::where($scope)->get();

        foreach ($taken as $existing) {
            if (mb_strtolower((string) $existing->name) === mb_strtolower($clean)) {
                // A course removed earlier under this name comes back in its old
                // place rather than tripping the unique name index.
                if ($existing->isRemoved()) {
                    $existing->fill(['removed_at' => null, 'name' => mb_substr($clean, 0, 60)])->save();
                    return $existing;
                }
                return null;
            }
        }

        $nextSlot = ((int) $taken->max('position')) + 1;

        try {
            return HotelMenuCategory::create($scope + [
                'group_id' => $membership->group_id,
                'name'     => mb_substr($clean, 0, 60),
                'position' => max($nextSlot, count(self::CATEGORIES) + 1),
            ]);
        } catch (UniqueConstraintViolationException $e) {
            return null;
        }
    }

    /**
     * Renames one of the team's courses, and moves every dish filed under it with the
     * name — a dish's category is the course's name, so leaving them behind would
     * strand them under a heading that no longer exists.
     *
     * Returns the stored name, or null when the new name is taken or the old one is
     * not this team's.
     */
    public static function renameCategory(StudentGroup $membership, string $from, string $to): ?string
    {
        if (!HotelMenuCategory::tableReady()) {
            return null;
        }

        $fromClean = trim($from);
        $toClean = mb_substr(trim($to), 0, 60);

        if ($fromClean === '' || $toClean === '') {
            return null;
        }

        self::ensureCategoriesFor($membership);

        $scope = [
            'group_name' => $membership->group_name,
            'faculty_id' => $membership->faculty_id,
        ];

        $rows = HotelMenuCategory::where($scope)->get();

        $target = $rows->first(fn ($row) => !$row->isRemoved()
            && mb_strtolower((string) $row->name) === mb_strtolower($fromClean));
        if (!$target) {
            return null;
        }

        // Renaming to the same name, in different case, is a rename rather than a clash.
        $clash = $rows->first(fn ($row) => mb_strtolower((string) $row->name) === mb_strtolower($toClean)
            && (int) $row->hotel_menu_category_id !== (int) $target->hotel_menu_category_id);
        if ($clash && !$clash->isRemoved()) {
            return null;
        }

        return DB::transaction(function () use ($target, $toClean, $scope, $fromClean, $clash) {
            // A removed course gives its name up; it only has to keep its slot.
            if ($clash) {
                $clash->name = '~removed-' . $clash->hotel_menu_category_id;
                $clash->save();
            }
            $target->name = $toClean;
            $target->save();

            HotelMenuItem::where($scope)
                ->whereRaw('LOWER(category) = LOWER(?)', [$fromClean])
                ->update(['category' => $toClean]);

            return $toClean;
        });
    }

    /**
     * Removes one of the team's courses and the dishes filed under it, the way the
     * dish's own delete does. Orders already taken keep their own copy of the dish,
     * so nothing billed is lost. The last course is kept: a dish added afterwards
     * needs one to sit under.
     *
     * Returns null when done, or the reason it was refused.
     */
    public static function removeCategory(StudentGroup $membership, string $name): ?string
    {
        if (!HotelMenuCategory::supportsRemoval()) {
            return 'Removing a category is not available yet. Try again in a few minutes.';
        }

        $clean = trim($name);
        self::ensureCategoriesFor($membership);

        $scope = [
            'group_name' => $membership->group_name,
            'faculty_id' => $membership->faculty_id,
        ];

        $target = HotelMenuCategory::where($scope)->get()
            ->first(fn ($row) => !$row->isRemoved() && mb_strtolower((string) $row->name) === mb_strtolower($clean));
        if (!$target) {
            return 'That category is not one of yours.';
        }

        if (count(self::categoriesFor($membership)) <= 1) {
            return 'Keep at least one menu category.';
        }

        DB::transaction(function () use ($scope, $target) {
            HotelMenuItem::where($scope)
                ->whereRaw('LOWER(category) = LOWER(?)', [(string) $target->name])
                ->delete();
            $target->removed_at = now();
            $target->save();
        });

        return null;
    }
}
