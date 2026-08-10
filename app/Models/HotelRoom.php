<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HotelRoom extends Model
{
    protected $primaryKey = 'hotel_room_id';

    protected $fillable = [
        'group_name',
        'faculty_id',
        'group_id',
        'name',
        'category',
        'status',
        'price',
        'description',
        'image',
        'reservation',
    ];

    protected $casts = [
        'reservation' => 'array',
    ];
}
