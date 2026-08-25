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
        // Composed from first_name + last_name, and what every screen greets the
        // guest by. Kept as its own column so those reads stay one field.
        'name',
        'first_name',
        'last_name',
        'contact_number',
        'email',
        'password',
    ];

    protected $hidden = [
        'password',
    ];
}
