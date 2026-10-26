<?php

/*
 * What a shared content entry has to look like for the merge to treat it as empty.
 *
 * Section background colours reverted to the template default because a role that
 * had given __siteColors up still held a half-deleted copy of it, which reads back
 * as ['page' => 'home', 'items' => []]. That entry has to count as empty so it
 * neither claims the key nor overwrites the row that holds the team's colours,
 * while a real entry — or a list with anything in it — must not.
 *
 * Plain PHP on purpose: run it with `php tools/check-shared-key-merge.php`.
 * Do not move it under tests/, which `php artisan test` would pick up (that command
 * runs against the live database here).
 */

require __DIR__ . '/../vendor/autoload.php';

use App\Support\HotelTemplateBuilder;

$method = new ReflectionMethod(HotelTemplateBuilder::class, 'isEmptySharedValue');
$method->setAccessible(true);
$isEmpty = fn (mixed $value) => $method->invoke(null, $value);

$cases = [
    // [value, expected empty?, what it is]
    [['page' => 'home', 'items' => []], true,  'the leftover a half-deleted collection reads back as'],
    [['page' => 'home'], true,  'a meta row with nothing under it'],
    [[], true,  'nothing at all'],
    [null, true,  'no entry'],
    [['page' => 'home', 'items' => [['id' => 'site', 'bg' => '#101010']]], false, 'one saved section colour'],
    [['page' => 'home', 'items' => [], 'map' => ['brand:logo' => 'a.png']], false, 'a keyed map with an entry'],
    [['page' => 'home', 'items' => [], 'label' => 'SPC HOTEL'], false, 'a meta field of its own'],
    [[['id' => 'nav-1']], false, 'a plain list with an entry'],
];

$failed = 0;
foreach ($cases as [$value, $expected, $label]) {
    $actual = $isEmpty($value);
    if ($actual !== $expected) {
        $failed++;
        printf(
            "FAIL  %s: expected %s, got %s\n      %s\n",
            $label,
            $expected ? 'empty' : 'not empty',
            $actual ? 'empty' : 'not empty',
            json_encode($value)
        );
    }
}

if ($failed > 0) {
    fwrite(STDERR, "{$failed} of " . count($cases) . " cases failed\n");
    exit(1);
}

echo 'shared-key emptiness: all ' . count($cases) . " cases pass\n";
