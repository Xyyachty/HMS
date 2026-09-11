<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * A room category a team added itself, beyond the five it started with.
 *
 * The defaults are not rows — see App\Support\HotelRoomDefaults, which reads both and
 * hands the rest of the app one merged list.
 */
class HotelRoomCategory extends Model
{
    protected $primaryKey = 'hotel_room_category_id';

    protected $fillable = [
        'group_name',
        'faculty_id',
        'group_id',
        'name',
        'floor_number',
        'rate',
        'description',
        // What the category shows a guest, set in the design stage: its picture,
        // what the stay includes (one per line) and how many rooms of it the
        // hotel says it has.
        'image_path',
        'inclusions',
        'rooms_available',
        // What a guest asks before booking: the rest of the pictures, how many
        // people fit, what the bed is and how big the room is.
        'gallery',
        'capacity',
        'bed_type',
        'room_size',
    ];

    protected $casts = [
        'floor_number' => 'integer',
        'rate' => 'integer',
        'rooms_available' => 'integer',
        'gallery' => 'array',
        'capacity' => 'integer',
    ];

    /**
     * Whether the showcase columns are on the table yet.
     *
     * Production migrates on deploy, so the code ships before the columns do.
     * Asked once per request rather than per row: this is read while building
     * every category on a page that lists all of them.
     */
    public static function supportsShowcase(): bool
    {
        static $supported = null;

        if ($supported === null) {
            $supported = \Illuminate\Support\Facades\Schema::hasColumn('hotel_room_categories', 'gallery');
        }

        return $supported;
    }

    /** The gallery as a clean list of image paths, however it was stored. */
    public function galleryList(): array
    {
        if (!self::supportsShowcase()) {
            return [];
        }

        $raw = $this->gallery;
        if (is_string($raw)) {
            $decoded = json_decode($raw, true);
            $raw = is_array($decoded) ? $decoded : [];
        }

        $out = [];
        foreach (is_array($raw) ? $raw : [] as $item) {
            $url = trim((string) $item);
            if ($url !== '' && !in_array($url, $out, true)) {
                $out[] = $url;
            }
        }

        return array_slice($out, 0, self::GALLERY_MAX);
    }

    /**
     * The extra angles stored beside image_path.
     *
     * A category shows three photographs of the one room design, and the first
     * of them is image_path, so two more is the whole gallery.
     */
    public const GALLERY_MAX = 2;

    /** The inclusions as the list the browser works with, not the stored text. */
    public function getInclusionListAttribute(): array
    {
        return self::splitInclusions($this->inclusions);
    }

    /**
     * One inclusion per line, blank lines dropped. Accepts either the textarea's
     * text or a list, so a caller can hand over whichever it is holding.
     */
    public static function splitInclusions($value): array
    {
        if (is_array($value)) {
            $lines = $value;
        } else {
            // \R covers CRLF, CR and LF in one, so a textarea pasted from any
            // platform splits the same way.
            $lines = preg_split('/\R/u', (string) $value) ?: [];
        }

        $out = [];
        foreach ($lines as $line) {
            $clean = trim((string) $line);
            if ($clean !== '') {
                $out[] = mb_substr($clean, 0, 120);
            }
        }

        return array_slice($out, 0, 20);
    }
}
