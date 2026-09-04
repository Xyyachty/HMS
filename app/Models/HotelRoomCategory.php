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
    ];

    protected $casts = [
        'floor_number' => 'integer',
        'rate' => 'integer',
    ];
}
