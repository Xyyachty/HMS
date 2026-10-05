<?php

namespace Tests\Unit;

use App\Support\TemplateDiff;
use PHPUnit\Framework\TestCase;

/**
 * A header rename has to reach the faculty review as an outlined change, and a
 * first save of the five links must not report the untouched ones. Pure arrays —
 * no app, no DB.
 */
class TemplateDiffNavLinksTest extends TestCase
{
    private function diff(array $before, array $after): array
    {
        $method = new \ReflectionMethod(TemplateDiff::class, 'diffCollectionItems');
        $changes = [];
        $method->invokeArgs(null, ['navLinks', $before, $after, &$changes]);

        return $changes;
    }

    private function nav(array $labels): array
    {
        $out = [];
        foreach ($labels as $key => $label) {
            $out[] = ['id' => 'nav-' . $key, 'key' => $key, 'label' => $label];
        }

        return $out;
    }

    public function test_first_save_reports_only_the_renamed_link(): void
    {
        $after = $this->nav([
            'home' => 'Home', 'rooms' => 'Suites', 'restaurant' => 'Restaurant',
            'amenities' => 'Amenities', 'experience' => 'Highlights',
        ]);

        $changes = $this->diff([], $after);

        $this->assertCount(1, $changes);
        $this->assertSame('modified', $changes[0]['type']);
        $this->assertSame('[data-hms-nav-link="rooms"]', $changes[0]['key']);
        $this->assertSame('Rooms', $changes[0]['fields'][0]['from']);
        $this->assertSame('Suites', $changes[0]['fields'][0]['to']);
    }

    public function test_a_later_rename_is_outlined(): void
    {
        $before = $this->nav(['home' => 'Home', 'rooms' => 'Suites']);
        $after = $this->nav(['home' => 'Welcome', 'rooms' => 'Suites']);

        $changes = $this->diff($before, $after);

        $this->assertCount(1, $changes);
        $this->assertSame('[data-hms-nav-link="home"]', $changes[0]['key']);
    }
}
