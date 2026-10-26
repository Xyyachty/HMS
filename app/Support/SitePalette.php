<?php

namespace App\Support;

/**
 * The palette a team's chosen background colour implies.
 *
 * The site templates already derive one in JavaScript (sitePalette() in
 * 1defaulttemplate / 2defaulttemplate) so the landing page repaints the moment
 * a colour is picked. The staff pages are plain Blade with no React on them, so
 * they need the same maths on the server — same steps, same thresholds — or a
 * team that recolours its site gets a hotel front that follows and an ops shell
 * that stays the stock near-black.
 */
class SitePalette
{
    public const SITE_COLORS_KEY = '__siteColors';

    /** The gold and the forest green the two templates are built around. */
    private const ACCENTS = ['1' => '#c9a84c', '2' => '#2d6a4f'];

    /**
     * The background the team picked for "Main website", or null when they have
     * not picked one and every page should keep its own stock colours.
     */
    public static function siteBackground(?array $customizations): ?string
    {
        $entry = $customizations[self::SITE_COLORS_KEY] ?? null;
        $items = is_array($entry) && isset($entry['items']) && is_array($entry['items']) ? $entry['items'] : [];

        foreach ($items as $item) {
            if (!is_array($item) || ($item['id'] ?? null) !== 'site') {
                continue;
            }
            $bg = trim((string) ($item['bg'] ?? ''));
            return $bg !== '' ? $bg : null;
        }

        return null;
    }

    /**
     * bg / warm / card / border / fg / muted / accent for one background, or
     * null when the value is not a colour this can read.
     */
    public static function forBackground(?string $bg, ?string $template = null): ?array
    {
        $rgb = self::parseColor($bg);
        if (!$rgb) {
            return null;
        }

        $luminance = self::relativeLuminance($rgb);
        $light = $luminance > 0.4;
        $fg = $light ? '#14110c' : '#f5f0e8';
        $fgRgb = self::parseColor($fg);

        return [
            // Re-emitted from the parsed channels rather than passed through, so
            // only digits ever reach the stylesheet even though the stored value
            // is whatever a client wrote.
            'bg' => self::rgbToCss($rgb),
            'warm' => self::mixToward($rgb, $fgRgb, 0.03),
            'card' => self::mixToward($rgb, $fgRgb, 0.07),
            'border' => self::mixToward($rgb, $fgRgb, 0.18),
            'fg' => $fg,
            'muted' => $light ? 'rgba(20,17,12,0.62)' : 'rgba(245,240,232,0.62)',
            'accent' => self::readableAccent(self::ACCENTS[$template] ?? self::ACCENTS['1'], $luminance, $fgRgb) ?? $fg,
        ];
    }

    /**
     * Keep the template's accent hue on an unusual background instead of
     * dropping to plain text colour: mix it toward the text colour only as far
     * as it takes to clear 3:1. Null when even the fully mixed colour cannot.
     */
    private static function readableAccent(string $accent, float $bgLuminance, array $fgRgb): ?string
    {
        $accentRgb = self::parseColor($accent);
        if (!$accentRgb) {
            return null;
        }

        for ($t = 0.0; $t <= 0.91; $t += 0.13) {
            $candidate = self::mixRgb($accentRgb, $fgRgb, $t);
            if (self::contrastRatio(self::relativeLuminance($candidate), $bgLuminance) >= 3) {
                return self::rgbToCss($candidate);
            }
        }

        return null;
    }

    private static function parseColor(?string $value): ?array
    {
        $v = trim((string) $value);

        if (preg_match('/^#([0-9a-f]{3})$/i', $v, $m)) {
            $h = $m[1];
            return [
                'r' => hexdec($h[0] . $h[0]),
                'g' => hexdec($h[1] . $h[1]),
                'b' => hexdec($h[2] . $h[2]),
            ];
        }

        if (preg_match('/^#([0-9a-f]{6})$/i', $v, $m)) {
            $h = $m[1];
            return [
                'r' => hexdec(substr($h, 0, 2)),
                'g' => hexdec(substr($h, 2, 2)),
                'b' => hexdec(substr($h, 4, 2)),
            ];
        }

        if (preg_match('/^rgba?\(([^)]+)\)$/i', $v, $m)) {
            $parts = array_map('trim', explode(',', $m[1]));
            if (count($parts) >= 3 && count(array_filter(array_slice($parts, 0, 3), 'is_numeric')) === 3) {
                return ['r' => (float) $parts[0], 'g' => (float) $parts[1], 'b' => (float) $parts[2]];
            }
        }

        return null;
    }

    private static function relativeLuminance(array $rgb): float
    {
        $channel = function ($c) {
            $s = $c / 255;
            return $s <= 0.03928 ? $s / 12.92 : pow(($s + 0.055) / 1.055, 2.4);
        };

        return 0.2126 * $channel($rgb['r']) + 0.7152 * $channel($rgb['g']) + 0.0722 * $channel($rgb['b']);
    }

    private static function contrastRatio(float $l1, float $l2): float
    {
        return (max($l1, $l2) + 0.05) / (min($l1, $l2) + 0.05);
    }

    private static function mixRgb(array $rgb, array $target, float $amount): array
    {
        $mix = fn ($a, $b) => (int) round($a + ($b - $a) * $amount);

        return [
            'r' => $mix($rgb['r'], $target['r']),
            'g' => $mix($rgb['g'], $target['g']),
            'b' => $mix($rgb['b'], $target['b']),
        ];
    }

    private static function mixToward(array $rgb, array $target, float $amount): string
    {
        return self::rgbToCss(self::mixRgb($rgb, $target, $amount));
    }

    private static function rgbToCss(array $rgb): string
    {
        return 'rgb(' . $rgb['r'] . ',' . $rgb['g'] . ',' . $rgb['b'] . ')';
    }
}
