<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HotelCustomer extends Model
{
    protected $primaryKey = 'hotel_customer_id';

    protected $fillable = [
        'group_name',
        'faculty_id',
        'group_id',
        'name',
        'email',
        'password',
    ];

    protected $hidden = [
        'password',
    ];
}
