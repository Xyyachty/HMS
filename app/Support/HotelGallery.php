<?php

namespace App\Support;

/**
 * The extra photographs of one thing — a facility, a room — on their way into a
 * `gallery` column.
 *
 * A gallery arrives as a mixed list: data-URLs for the shots just picked, and the
 * storage paths the form handed back untouched for the ones already saved.
 * HotelImageStore::persist() knows the difference — it decodes the first kind to a
 * file and normalises the second — so this only has to bound the list, drop what
 * did not survive, and keep the order it was given in.
 *
 * The bound is a parameter rather than a constant here because it belongs to the
 * thing being photographed: an amenity pages through eight, a room shows three.
 */
class HotelGallery
{
    /**
     * @param  mixed   $value  The gallery as the request sent it.
     * @param  object  $membership  Carries faculty_id and group_name for the folder.
     * @param  int     $max    How many photographs this owner may keep.
     * @return list<string>    Media-disk paths, in order, at most $max of them.
     */
    public static function persist(mixed $value, object $membership, int $max): array
    {
        if (!is_array($value)) {
            return [];
        }

        $paths = [];

        foreach ($value as $item) {
            if (count($paths) >= $max) {
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
