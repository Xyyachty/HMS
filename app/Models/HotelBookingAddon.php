<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * One add-on lent to one stay.
 *
 * name and unit_price are snapshots taken when Front Desk attached it, the same call
 * hotel_bookings.room_rate makes — Housekeeping repricing a folding bed next week must not
 * change what this guest was billed. hotel_addon_id stays alongside them so availability
 * is an id lookup rather than a name match.
 */
class HotelBookingAddon extends Model
{
    protected $primaryKey = 'hotel_booking_addon_id';

    protected $fillable = [
        'group_name',
        'faculty_id',
        'group_id',
        'hotel_booking_id',
        'hotel_addon_id',
        'name',
        'unit_price',
        'qty',
    ];

    protected $casts = [
        'unit_price' => 'integer',
        'qty'        => 'integer',
    ];

    public function booking(): BelongsTo
    {
        return $this->belongsTo(HotelBooking::class, 'hotel_booking_id', 'hotel_booking_id');
    }

    public function addon(): BelongsTo
    {
        return $this->belongsTo(HotelAddon::class, 'hotel_addon_id', 'hotel_addon_id');
    }

    /** An extra bed is charged per night of the stay; anything else once per stay. */
    public function isPerNight(): bool
    {
        return HotelAddon::namesExtraBed($this->name);
    }

    public function lineTotal(int $nights = 1): int
    {
        return (int) $this->unit_price * (int) $this->qty * ($this->isPerNight() ? max(1, $nights) : 1);
    }

    public function toTemplateArray(int $nights = 1): array
    {
        return [
            'id'        => $this->hotel_booking_addon_id,
            'addonId'   => $this->hotel_addon_id,
            'name'      => $this->name,
            'qty'       => (int) $this->qty,
            'unitPrice' => (int) $this->unit_price,
            'perNight'  => $this->isPerNight(),
            'nights'    => $this->isPerNight() ? max(1, $nights) : 1,
            'line'      => $this->lineTotal($nights),
        ];
    }
}
