<?php

namespace App\Support;

use App\Models\HotelAmenity;

/**
 * The extra photographs of one facility, on their way into hotel_amenities.gallery.
 *
 * A gallery arrives as a mixed list: data-URLs for the shots just picked, and the
 * storage paths the form handed back untouched for the ones already saved.
 * HotelImageStore::persist() knows the difference — it decodes the first kind to a
 * file and normalises the second — so this only has to bound the list, drop what
 * did not survive, and keep the order Housekeeping put them in.
 */
class HotelAmenityGallery
{
    /**
     * @param  mixed  $value  The gallery as the request sent it.
     * @return list<string>   Media-disk paths, in order, at most GALLERY_MAX of them.
     */
    public static function persist(mixed $value, object $membership): array
    {
        if (!is_array($value)) {
            return [];
        }

        $paths = [];

        foreach ($value as $item) {
            if (count($paths) >= HotelAmenity::GALLERY_MAX) {
                break;
            }

            $path = HotelImageStore::persist(
                is_string($item) ? $item : null,
                $membership->faculty_id,
                $membership->group_name
            );

            // A duplicate would show as the carousel pausing on one slide, which
            // reads as a fault rather than as the same photograph twice.
            if ($path !== null && !in_array($path, $paths, true)) {
                $paths[] = $path;
            }
        }

        return $paths;
    }
}
