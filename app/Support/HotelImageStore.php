<?php

namespace App\Support;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Room and menu photos used to live in the database as base64 data-URLs, which put
 * megabyte-sized cells into every catalog query — hotel_rooms.image alone was ~72%
 * of the whole database. Store the bytes on the public disk and keep only the path.
 *
 * Mirrors TemplateCustomizationStore::persistImageValue(), which already does this
 * for template images; kept separate because the storage folder and callers differ.
 */
class HotelImageStore
{
    private const ALLOWED = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg+xml'];

    /**
     * Accepts whatever the front-end sent — a data-URL, an existing storage path, or
     * a remote URL — and returns what belongs in the `image` column. Data-URLs are
     * decoded to a file; everything else is normalised and passed through, so callers
     * can hand values straight back without re-uploading them.
     */
    public static function persist(mixed $value, ?int $facultyId, ?string $groupName): ?string
    {
        if (!is_string($value) || trim($value) === '') {
            return null;
        }

        $value = trim($value);

        if (!str_starts_with($value, 'data:image')) {
            return self::relativize($value);
        }

        if (!preg_match('#^data:image/([a-zA-Z0-9+.-]+);base64,(.+)$#', $value, $m)) {
            return null;
        }

        $binary = base64_decode($m[2], true);
        if ($binary === false) {
            return null;
        }

        $path = self::folder($facultyId, $groupName) . '/' . Str::uuid() . '.' . self::extension($m[1]);
        Storage::disk(self::disk())->put($path, $binary);

        return $path;
    }

    /** Local disk in development, Supabase Storage in production. See config/filesystems.php. */
    public static function disk(): string
    {
        return config('filesystems.media', 'public');
    }

    /**
     * Every prefix a stored media value may legitimately be carrying, longest
     * first so the most specific one is stripped before a shorter prefix of it.
     *
     * More than one is needed because the value may have been written under a
     * different disk than the one currently configured: an image uploaded to
     * Supabase and later read back on the local "public" disk arrives wearing
     * the bucket's path but the app's host.
     *
     * @return list<string>
     */
    private static function urlPrefixes(): array
    {
        static $prefixes = null;

        if ($prefixes !== null) {
            return $prefixes;
        }

        $candidates = [];

        try {
            $candidates[] = rtrim(Storage::disk(self::disk())->url(''), '/');
        } catch (\Throwable $e) {
            // A misconfigured disk must not stop a value being normalised.
        }

        $public = (string) config('filesystems.disks.supabase.url', env('SUPABASE_S3_PUBLIC_URL', ''));
        if ($public !== '') {
            $candidates[] = rtrim($public, '/');
        }

        $appUrl = (string) config('app.url', '');
        if ($appUrl !== '') {
            $candidates[] = rtrim($appUrl, '/') . '/storage';
        }

        // The bare object path Supabase public URLs carry. The old "/storage/"
        // strip left this behind as if it were a relative path, so it turns up
        // on its own and can be repeated several times over.
        $bucket = (string) config('filesystems.disks.supabase.bucket', env('SUPABASE_S3_BUCKET', 'hms-media'));
        if ($bucket !== '') {
            $candidates[] = 'v1/object/public/' . $bucket;
        }

        $candidates[] = '/storage';

        $prefixes = array_values(array_unique(array_filter($candidates, fn ($p) => $p !== '')));
        usort($prefixes, fn ($a, $b) => strlen($b) <=> strlen($a));

        return $prefixes;
    }

    /**
     * Collapse an absolute URL on our own media disk back to the relative path
     * that belongs in the column.
     *
     * This cannot be a plain "strip everything up to /storage/": Supabase's public
     * URL is itself .../storage/v1/object/public/<bucket>/..., so that strip left
     * "v1/object/public/<bucket>/…" behind as the stored path, which url() then
     * prefixed again on the way out. Every save/snapshot round-trip added another
     * copy of the segment until the image 404'd — and made the review diff report
     * an image as changed when only its stored spelling had.
     *
     * Idempotent, and leaves genuinely remote URLs alone.
     */
    public static function relativize(string $value): string
    {
        $value = trim($value);
        if ($value === '' || str_starts_with($value, 'data:')) {
            return $value;
        }

        $prefixes = self::urlPrefixes();

        // Guarded rather than while(true): a value may wear the same prefix many
        // times over, but it must never be possible to spin here.
        for ($guard = 0; $guard < 20; $guard++) {
            $stripped = false;
            foreach ($prefixes as $prefix) {
                if (str_starts_with($value, $prefix . '/')) {
                    $value = substr($value, strlen($prefix) + 1);
                    $stripped = true;
                    break;
                }
            }
            if (!$stripped) {
                break;
            }
        }

        return $value;
    }

    /**
     * Turn a stored value into something an <img src> can use. Legacy base64 rows and
     * remote URLs are returned untouched, so this is safe to call before every row has
     * been converted.
     */
    public static function url(?string $path): string
    {
        $path = (string) $path;

        if ($path === '') {
            return '';
        }

        if (str_starts_with($path, 'data:')) {
            return $path;
        }

        // Normalise first, so rows already carrying a doubled prefix still resolve.
        $path = self::relativize($path);

        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            return $path;
        }

        if (!str_contains($path, '://')) {
            // Ask the disk for the URL rather than assuming /storage/. On the local
            // disk that still yields /storage/...; on Supabase Storage it yields the
            // bucket's public object URL.
            return Storage::disk(self::disk())->url(ltrim($path, '/'));
        }

        return $path;
    }

    private static function extension(string $mimeSubtype): string
    {
        $ext = strtolower($mimeSubtype);

        if ($ext === 'jpeg') {
            return 'jpg';
        }

        if (!in_array($ext, self::ALLOWED, true)) {
            return 'png';
        }

        return $ext === 'svg+xml' ? 'svg' : $ext;
    }

    private static function folder(?int $facultyId, ?string $groupName): string
    {
        $group = trim((string) $groupName);

        return 'hotel-media/' . ($facultyId ?: 'shared') . '/' . ($group !== '' ? $group : 'catalog');
    }
}
