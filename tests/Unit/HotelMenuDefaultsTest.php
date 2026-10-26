<?php

namespace Tests\Unit;

use App\Models\HotelMenuItem;
use App\Support\HotelMenuDefaults;
use PHPUnit\Framework\TestCase;

/**
 * The menu's starting courses, and what happens to a name that is not one of them.
 * Pure logic only — the seeding, renaming and dish-migration paths need a team row
 * and are exercised against the database rather than here.
 */
class HotelMenuDefaultsTest extends TestCase
{
    public function test_the_five_starting_courses_are_in_menu_order(): void
    {
        $this->assertSame(
            ['Main Dishes', 'Appetizers', 'Soups', 'Desserts', 'Beverages'],
            HotelMenuDefaults::defaultNames()
        );
    }

    /** The seed and the model's own constant must not drift apart. */
    public function test_the_seed_matches_the_model_constant(): void
    {
        $this->assertSame(HotelMenuItem::CATEGORIES, HotelMenuDefaults::defaultNames());
    }

    public function test_every_default_holds_its_own_slot(): void
    {
        $slots = array_values(HotelMenuDefaults::CATEGORIES);

        $this->assertSame($slots, array_unique($slots), 'Two courses share a slot, so seeding would skip one.');
        $this->assertSame([1, 2, 3, 4, 5], $slots);
    }

    public function test_a_known_course_comes_back_in_its_stored_spelling(): void
    {
        $this->assertSame('Desserts', HotelMenuDefaults::normalizeCategory('desserts'));
        $this->assertSame('Main Dishes', HotelMenuDefaults::normalizeCategory('  MAIN DISHES '));
    }

    /** With no team to ask, an unknown name falls back to the first course. */
    public function test_an_unknown_course_falls_back_to_the_first(): void
    {
        $this->assertSame('Main Dishes', HotelMenuDefaults::normalizeCategory('Chef Specials'));
        $this->assertSame('Main Dishes', HotelMenuDefaults::normalizeCategory(''));
        $this->assertSame('Main Dishes', HotelMenuDefaults::normalizeCategory(null));
    }
}
