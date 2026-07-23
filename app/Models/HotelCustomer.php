<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HotelCustomer extends Model
{
    protected $fillable = [
        'group_name',
        'faculty_id',
        'name',
        'email',
        'password',
    ];

    protected $hidden = [
        'password',
    ];
}
