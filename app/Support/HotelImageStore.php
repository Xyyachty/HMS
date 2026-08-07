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
            // Collapse an absolute /storage/... URL back to the relative path we store.
            if (preg_match('#/storage/(.+)$#', $value, $m)) {
                return $m[1];
            }

            return $value;
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

        if (str_starts_with($path, 'http://')
            || str_starts_with($path, 'https://')
            || str_starts_with($path, 'data:')
            || str_starts_with($path, '/storage/')) {
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
