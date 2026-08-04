<?php

namespace App\Support;

use App\Models\HotelMenuItem;
use App\Models\StudentGroup;

/**
 * Authorization + bootstrap for the hotel restaurant menu.
 *
 * The menu is shared by the whole hotel team, but only members holding the
 * Restaurant Services role may write to it. Everyone else is read-only.
 */
class HotelMenuAccess
{
    public const MANAGE_ROLE = 'restaurant_management';

    /** Roles that may add / edit / delete menu items. */
    public const MANAGE_ROLES = [self::MANAGE_ROLE, 'administrator'];

    public static function membership(): ?StudentGroup
    {
        $student = auth()->user()?->student;

        return StudentGroupSync::membershipForStudent($student?->id);
    }

    /**
     * A member may manage the menu when their own team role grants it, or when
     * they are signed into the hotel site as Restaurant Services staff.
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
     * First load for a team starts from the house menu so the Restaurant page is
     * never empty. Runs once — after that the DB is the only source of truth.
     */
    public static function seedDefaults(StudentGroup $membership): void
    {
        $exists = HotelMenuItem::where('group_name', $membership->group_name)
            ->where('faculty_id', $membership->faculty_id)
            ->exists();

        if ($exists) {
            return;
        }

        $now = now();
        $rows = array_map(fn ($item) => array_merge($item, [
            'group_name' => $membership->group_name,
            'faculty_id' => $membership->faculty_id,
            'stock'      => 20,
            'created_at' => $now,
            'updated_at' => $now,
        ]), self::defaultMenu());

        HotelMenuItem::insert($rows);
    }

    private static function defaultMenu(): array
    {
        return [
            ['name' => 'Hokkaido Scallop Tartare', 'category' => 'Appetizers',  'price' => 1800, 'description' => 'yuzu, sea urchin, micro herbs',           'image' => 'https://picsum.photos/seed/scalloptartare/800/600.jpg'],
            ['name' => 'Wagyu A5 Carpaccio',       'category' => 'Appetizers',  'price' => 2700, 'description' => 'truffle jus, parmesan crisp, rocket',     'image' => 'https://picsum.photos/seed/wagyucarpaccio/800/600.jpg'],
            ['name' => 'Roasted Tomato Bisque',    'category' => 'Soups',       'price' => 680,  'description' => 'basil oil, grilled sourdough croutons',   'image' => 'https://picsum.photos/seed/tomatobisque/800/600.jpg'],
            ['name' => 'Seafood Chowder',          'category' => 'Soups',       'price' => 950,  'description' => 'prawns, mussels, cream, chives',          'image' => 'https://picsum.photos/seed/seafoodchowder/800/600.jpg'],
            ['name' => 'Pan-Seared Dover Sole',    'category' => 'Main Dishes', 'price' => 3000, 'description' => 'brown butter, capers, lemon beurre blanc','image' => 'https://picsum.photos/seed/doversole/800/600.jpg'],
            ['name' => 'Grilled Angus Ribeye',     'category' => 'Main Dishes', 'price' => 3800, 'description' => 'garlic butter, roasted vegetables, jus',  'image' => 'https://picsum.photos/seed/angusribeye/800/600.jpg'],
            ['name' => 'Herb-Crusted Lamb Rack',   'category' => 'Main Dishes', 'price' => 4200, 'description' => 'mint reduction, potato puree',            'image' => 'https://picsum.photos/seed/lambrack/800/600.jpg'],
            ['name' => 'Roasted Rhubarb Souffle',  'category' => 'Desserts',    'price' => 1200, 'description' => 'vanilla bean creme anglaise, pistachio',  'image' => 'https://picsum.photos/seed/rhubarbsouffle/800/600.jpg'],
            ['name' => 'Dark Chocolate Fondant',   'category' => 'Desserts',    'price' => 1100, 'description' => 'salted caramel ice cream',                'image' => 'https://picsum.photos/seed/chocfondant/800/600.jpg'],
            ['name' => 'The SPC Old Fashioned',    'category' => 'Beverages',   'price' => 1450, 'description' => '25yr bourbon, demerara, aromatic bitters','image' => 'https://picsum.photos/seed/oldfashioned/800/600.jpg'],
            ['name' => 'Gold Leaf Negroni',        'category' => 'Beverages',   'price' => 1550, 'description' => 'gin, Campari, sweet vermouth, gold leaf', 'image' => 'https://picsum.photos/seed/goldnegroni/800/600.jpg'],
            ['name' => 'Fresh Calamansi Iced Tea', 'category' => 'Beverages',   'price' => 280,  'description' => 'house-brewed, lightly sweetened',         'image' => 'https://picsum.photos/seed/calamansitea/800/600.jpg'],
        ];
    }
}
