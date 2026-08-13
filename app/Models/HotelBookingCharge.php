<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * An extra charged to a stay by hand — minibar, laundry, a late checkout fee.
 *
 * The other two things on a final bill compute themselves: the room charge comes off
 * the booking's rate and length, room service off hotel_food_orders. This is the one
 * a member of staff types in, which is why it is a row rather than a formula.
 */
class HotelBookingCharge extends Model
{
    protected $primaryKey = 'hotel_booking_charge_id';

    protected $fillable = [
        'group_name',
        'faculty_id',
        'group_id',
        'hotel_booking_id',
        'description',
        'amount',
        'added_by',
    ];

    protected $casts = [
        'amount' => 'float',
    ];

    public function booking(): BelongsTo
    {
        return $this->belongsTo(HotelBooking::class, 'hotel_booking_id', 'hotel_booking_id');
    }

    public function toTemplateArray(): array
    {
        return [
            'id'          => $this->hotel_booking_charge_id,
            'description' => $this->description,
            'amount'      => (float) $this->amount,
            'addedBy'     => $this->added_by ?? '',
            'addedAt'     => optional($this->created_at)->toIso8601String(),
        ];
    }
}
