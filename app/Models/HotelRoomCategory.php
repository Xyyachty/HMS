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
    ];

    protected $casts = [
        'floor_number' => 'integer',
        'rate' => 'integer',
        'rooms_available' => 'integer',
    ];

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
