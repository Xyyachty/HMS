<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Something Housekeeping lends a guest on top of the room — a folding bed, an extra towel.
 *
 * quantity is how many the hotel owns. What is free right now is that minus whatever is out
 * on open stays, which lives in hotel_booking_addons rather than on this row; see
 * HotelAddonDesk::reservedFor(). Nothing here decrements, so nothing has to be given back.
 */
class HotelAddon extends Model
{
    protected $primaryKey = 'hotel_addon_id';

    protected $fillable = [
        'group_name',
        'faculty_id',
        'group_id',
        'name',
        'price',
        'quantity',
        'image',
    ];

    protected $casts = [
        'price'    => 'integer',
        'quantity' => 'integer',
    ];

    public function bookingAddons(): HasMany
    {
        return $this->hasMany(HotelBookingAddon::class, 'hotel_addon_id', 'hotel_addon_id');
    }

    /**
     * Shape sent to the hotel template front-end.
     *
     * $reserved — how many are out on open stays — is passed in rather than queried here.
     * The screens that render this list ask for every row's figure at once (one grouped
     * query in HotelAddonDesk::reservedFor), so doing it per row would be an N+1.
     */
    public function toTemplateArray(int $reserved = 0): array
    {
        // "id"/"dbId" are the front-end's keys for a catalogue row, not column names.
        $available = max(0, (int) $this->quantity - $reserved);

        return [
            'id'        => 'db-' . $this->hotel_addon_id,
            'dbId'      => $this->hotel_addon_id,
            'name'      => $this->name,
            'price'     => (int) $this->price,
            'quantity'  => (int) $this->quantity,
            'reserved'  => $reserved,
            'available' => $available,
            // Derived, never stored: a status column and a quantity column that can
            // disagree with each other is a bug waiting to be filed.
            'status'    => $available > 0 ? 'Available' : 'Out of Stock',
            'img'       => \App\Support\HotelImageStore::url($this->image),
        ];
    }
}
