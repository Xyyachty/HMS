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

        return StudentGroupSync::membershipForStudent($student?->user_information_id);
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
            'group_id'   => $membership->group_id,
            'stock'      => 20,
            'created_at' => $now,
            'updated_at' => $now,
        ]), self::defaultMenu());

        HotelMenuItem::insert($rows);
    }

    /**
     * Ten dishes per category, so every tab of the ordering screen is populated.
     *
     * The photos are the real dish from TheMealDB / TheCocktailDB rather than the
     * random placeholder the seed used to carry: whoever is taking a dine-in order
     * picks by sight, and a picture of the wrong food is worse than none.
     */
    public static function defaultMenu(): array
    {
        return [
            // Main Dishes
            ['name' => 'Beef Wellington', 'category' => 'Main Dishes', 'price' => 4200, 'description' => 'mushroom duxelles, puff pastry, red wine jus', 'image' => 'https://www.themealdb.com/images/media/meals/vvpprx1487325699.jpg'],
            ['name' => 'Beef Bourguignon', 'category' => 'Main Dishes', 'price' => 3400, 'description' => 'burgundy braise, pearl onions, smoked bacon', 'image' => 'https://www.themealdb.com/images/media/meals/vtqxtu1511784197.jpg'],
            ['name' => 'Beef Stroganoff', 'category' => 'Main Dishes', 'price' => 2900, 'description' => 'sour cream, mushrooms, buttered pappardelle', 'image' => 'https://www.themealdb.com/images/media/meals/svprys1511176755.jpg'],
            ['name' => 'Beef Rendang', 'category' => 'Main Dishes', 'price' => 2600, 'description' => 'slow-cooked coconut curry, toasted spices', 'image' => 'https://www.themealdb.com/images/media/meals/bc8v651619789840.jpg'],
            ['name' => 'Lamb Tagine', 'category' => 'Main Dishes', 'price' => 3300, 'description' => 'apricots, almonds, warm Moroccan spice', 'image' => 'https://www.themealdb.com/images/media/meals/yuwtuu1511295751.jpg'],
            ['name' => 'Lancashire Hotpot', 'category' => 'Main Dishes', 'price' => 2400, 'description' => 'lamb shoulder, sliced potato crust', 'image' => 'https://www.themealdb.com/images/media/meals/uttrxw1511637813.jpg'],
            ['name' => 'Baked Salmon & Fennel', 'category' => 'Main Dishes', 'price' => 3100, 'description' => 'roast fennel, blistered tomatoes, dill', 'image' => 'https://www.themealdb.com/images/media/meals/1548772327.jpg'],
            ['name' => 'Cayman-Style Lobster', 'category' => 'Main Dishes', 'price' => 4600, 'description' => 'butter-poached tail, island pepper sauce', 'image' => 'https://www.themealdb.com/images/media/meals/wkhwqr1782774765.jpg'],
            ['name' => 'Chicken Alfredo Primavera', 'category' => 'Main Dishes', 'price' => 2200, 'description' => 'parmesan cream, spring vegetables', 'image' => 'https://www.themealdb.com/images/media/meals/syqypv1486981727.jpg'],
            ['name' => 'Chicken Basquaise', 'category' => 'Main Dishes', 'price' => 2300, 'description' => 'peppers, chorizo, basque tomato stew', 'image' => 'https://www.themealdb.com/images/media/meals/wruvqv1511880994.jpg'],

            // Appetizers
            ['name' => 'Baked Brie & Sorrel Jam', 'category' => 'Appetizers', 'price' => 1400, 'description' => 'warm wheel, sorrel preserve, toast points', 'image' => 'https://www.themealdb.com/images/media/meals/fg7d641784666908.jpg'],
            ['name' => 'Prosciutto-Wrapped Brie', 'category' => 'Appetizers', 'price' => 1650, 'description' => 'brioche crust, cured ham, honey', 'image' => 'https://www.themealdb.com/images/media/meals/qqpwsy1511796276.jpg'],
            ['name' => 'Ajo Blanco', 'category' => 'Appetizers', 'price' => 900, 'description' => 'chilled almond and garlic, green grapes', 'image' => 'https://www.themealdb.com/images/media/meals/5jdtie1763289302.jpg'],
            ['name' => 'Andalusian Gazpacho', 'category' => 'Appetizers', 'price' => 780, 'description' => 'chilled tomato, cucumber, sherry vinegar', 'image' => 'https://www.themealdb.com/images/media/meals/h5qmn31763304965.jpg'],
            ['name' => 'Cream Cheese Tart', 'category' => 'Appetizers', 'price' => 1050, 'description' => 'savoury custard, chive, crisp shell', 'image' => 'https://www.themealdb.com/images/media/meals/wurrux1468416624.jpg'],
            ['name' => 'Afghan Mantu Dumplings', 'category' => 'Appetizers', 'price' => 1150, 'description' => 'spiced beef, yoghurt, dried mint', 'image' => 'https://www.themealdb.com/images/media/meals/oemdp11779556068.jpg'],
            ['name' => 'Stuffed Grape Leaves', 'category' => 'Appetizers', 'price' => 850, 'description' => 'lemon rice, olive oil, fresh herbs', 'image' => 'https://www.themealdb.com/images/media/meals/oga0ax1780089591.jpg'],
            ['name' => 'House Hummus', 'category' => 'Appetizers', 'price' => 620, 'description' => 'tahini, smoked paprika, warm flatbread', 'image' => 'https://www.themealdb.com/images/media/meals/gpon5u1763801180.jpg'],
            ['name' => 'Heart of Palm Salad', 'category' => 'Appetizers', 'price' => 950, 'description' => 'heirloom tomato, citrus dressing', 'image' => 'https://www.themealdb.com/images/media/meals/7whfgi1782678933.jpg'],
            ['name' => 'Papas Rellenas', 'category' => 'Appetizers', 'price' => 880, 'description' => 'crisp stuffed potato, aji verde', 'image' => 'https://www.themealdb.com/images/media/meals/zdyurx1783801146.jpg'],

            // Soups
            ['name' => 'French Onion Soup', 'category' => 'Soups', 'price' => 780, 'description' => 'caramelised onion, gruyere crouton', 'image' => 'https://www.themealdb.com/images/media/meals/xvrrux1511783685.jpg'],
            ['name' => 'Tom Yum Goong', 'category' => 'Soups', 'price' => 950, 'description' => 'lemongrass, galangal, tiger prawns', 'image' => 'https://www.themealdb.com/images/media/meals/l50vz41763422681.jpg'],
            ['name' => 'Hot & Sour Soup', 'category' => 'Soups', 'price' => 690, 'description' => 'wood ear mushroom, white pepper, tofu', 'image' => 'https://www.themealdb.com/images/media/meals/1529445893.jpg'],
            ['name' => 'Thai Pumpkin Soup', 'category' => 'Soups', 'price' => 720, 'description' => 'coconut milk, red curry, crisp shallots', 'image' => 'https://www.themealdb.com/images/media/meals/1brbso1763585098.jpg'],
            ['name' => 'New England Clam Chowder', 'category' => 'Soups', 'price' => 1050, 'description' => 'clams, smoked bacon, cream, oyster crackers', 'image' => 'https://www.themealdb.com/images/media/meals/rvtvuw1511190488.jpg'],
            ['name' => 'Creamy Tomato Soup', 'category' => 'Soups', 'price' => 650, 'description' => 'basil oil, grilled sourdough', 'image' => 'https://www.themealdb.com/images/media/meals/stpuws1511191310.jpg'],
            ['name' => 'Egg Drop Soup', 'category' => 'Soups', 'price' => 560, 'description' => 'silken egg ribbons, spring onion', 'image' => 'https://www.themealdb.com/images/media/meals/1529446137.jpg'],
            ['name' => 'Borscht', 'category' => 'Soups', 'price' => 700, 'description' => 'beetroot, dill, sour cream', 'image' => 'https://www.themealdb.com/images/media/meals/zadvgb1699012544.jpg'],
            ['name' => 'Salmon Noodle Soup', 'category' => 'Soups', 'price' => 1150, 'description' => 'miso broth, soba, seared salmon', 'image' => 'https://www.themealdb.com/images/media/meals/ikizdm1763760862.jpg'],
            ['name' => 'Broccoli & Stilton Soup', 'category' => 'Soups', 'price' => 740, 'description' => 'blue cheese, toasted walnut', 'image' => 'https://www.themealdb.com/images/media/meals/tvvxpv1511191952.jpg'],

            // Desserts
            ['name' => 'Chocolate Souffle', 'category' => 'Desserts', 'price' => 1200, 'description' => 'warm centre, creme anglaise', 'image' => 'https://www.themealdb.com/images/media/meals/twspvx1511784937.jpg'],
            ['name' => 'Eton Mess', 'category' => 'Desserts', 'price' => 880, 'description' => 'crushed meringue, berries, chantilly', 'image' => 'https://www.themealdb.com/images/media/meals/uuxwvq1483907861.jpg'],
            ['name' => 'New York Cheesecake', 'category' => 'Desserts', 'price' => 950, 'description' => 'graham crust, sour cherry compote', 'image' => 'https://www.themealdb.com/images/media/meals/swttys1511385853.jpg'],
            ['name' => 'Key Lime Pie', 'category' => 'Desserts', 'price' => 890, 'description' => 'torched meringue, lime zest', 'image' => 'https://www.themealdb.com/images/media/meals/qpqtuu1511386216.jpg'],
            ['name' => 'Bakewell Tart', 'category' => 'Desserts', 'price' => 820, 'description' => 'frangipane, raspberry, flaked almond', 'image' => 'https://www.themealdb.com/images/media/meals/wyrqqq1468233628.jpg'],
            ['name' => 'Bread & Butter Pudding', 'category' => 'Desserts', 'price' => 760, 'description' => 'vanilla custard, sultanas, nutmeg', 'image' => 'https://www.themealdb.com/images/media/meals/xqwwpy1483908697.jpg'],
            ['name' => 'Apple Frangipane Tart', 'category' => 'Desserts', 'price' => 840, 'description' => 'almond cream, thin-sliced apple', 'image' => 'https://www.themealdb.com/images/media/meals/wxywrq1468235067.jpg'],
            ['name' => 'Chocolate Avocado Mousse', 'category' => 'Desserts', 'price' => 700, 'description' => 'dark chocolate, sea salt, olive oil', 'image' => 'https://www.themealdb.com/images/media/meals/uttuxy1511382180.jpg'],
            ['name' => 'Honey Yoghurt Cheesecake', 'category' => 'Desserts', 'price' => 900, 'description' => 'wildflower honey, pistachio', 'image' => 'https://www.themealdb.com/images/media/meals/y2irzl1585563479.jpg'],
            ['name' => 'Carrot Cake', 'category' => 'Desserts', 'price' => 680, 'description' => 'cream cheese frosting, toasted walnut', 'image' => 'https://www.themealdb.com/images/media/meals/vrspxv1511722107.jpg'],

            // Beverages
            ['name' => 'Old Fashioned', 'category' => 'Beverages', 'price' => 1450, 'description' => 'bourbon, demerara, aromatic bitters', 'image' => 'https://www.thecocktaildb.com/images/media/drink/vrwquq1478252802.jpg'],
            ['name' => 'Negroni', 'category' => 'Beverages', 'price' => 1350, 'description' => 'gin, Campari, sweet vermouth', 'image' => 'https://www.thecocktaildb.com/images/media/drink/qgdu971561574065.jpg'],
            ['name' => 'Mojito', 'category' => 'Beverages', 'price' => 980, 'description' => 'white rum, lime, muddled mint', 'image' => 'https://www.thecocktaildb.com/images/media/drink/metwgh1606770327.jpg'],
            ['name' => 'Margarita', 'category' => 'Beverages', 'price' => 1050, 'description' => 'tequila, triple sec, salt rim', 'image' => 'https://www.thecocktaildb.com/images/media/drink/5noda61589575158.jpg'],
            ['name' => 'Daiquiri', 'category' => 'Beverages', 'price' => 980, 'description' => 'white rum, lime, cane sugar', 'image' => 'https://www.thecocktaildb.com/images/media/drink/mrz9091589574515.jpg'],
            ['name' => 'Manhattan', 'category' => 'Beverages', 'price' => 1400, 'description' => 'rye, sweet vermouth, cherry', 'image' => 'https://www.thecocktaildb.com/images/media/drink/yk70e31606771240.jpg'],
            ['name' => 'Aviation', 'category' => 'Beverages', 'price' => 1300, 'description' => 'gin, maraschino, creme de violette', 'image' => 'https://www.thecocktaildb.com/images/media/drink/trbplb1606855233.jpg'],
            ['name' => 'Boulevardier', 'category' => 'Beverages', 'price' => 1400, 'description' => 'bourbon, Campari, sweet vermouth', 'image' => 'https://www.thecocktaildb.com/images/media/drink/km84qi1513705868.jpg'],
            ['name' => 'Aperol Spritz', 'category' => 'Beverages', 'price' => 1100, 'description' => 'Aperol, prosecco, soda, orange', 'image' => 'https://www.thecocktaildb.com/images/media/drink/iloasq1587661955.jpg'],
            ['name' => 'Espresso Martini', 'category' => 'Beverages', 'price' => 1250, 'description' => 'vodka, coffee liqueur, fresh espresso', 'image' => 'https://www.thecocktaildb.com/images/media/drink/n0sx531504372951.jpg'],
        ];
    }
}
