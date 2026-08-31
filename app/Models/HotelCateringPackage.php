<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * A catering package Restaurant Services sells to a function room booking.
 *
 * Priced per head and carrying no stock, which is exactly why it is not a HotelMenuItem:
 * the order pipeline decrements a menu item's stock per unit ordered, and a hundred-guest
 * buffet has no shelf to come off. Restaurant Services owns and prices these; Front Desk
 * picks one when booking an event and cannot create one.
 */
class HotelCateringPackage extends Model
{
    protected $primaryKey = 'hotel_catering_package_id';

    protected $fillable = [
        'group_name',
        'faculty_id',
        'group_id',
        'name',
        'description',
        'inclusions',
        'price_per_head',
        'min_guests',
        'image',
        'is_active',
    ];

    protected $casts = [
        'price_per_head' => 'integer',
        'min_guests'     => 'integer',
        'is_active'      => 'boolean',
    ];

    /** Retired packages stay in the table so booked events still resolve. */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /** What this package costs for a party of $guests. */
    public function totalFor(int $guests): int
    {
        return max(0, (int) $this->price_per_head) * max(0, $guests);
    }

    public function toTemplateArray(): array
    {
        // "id"/"dbId" are the front-end's keys for a catalogue row, not column names.
        return [
            'id'           => 'db-' . $this->hotel_catering_package_id,
            'dbId'         => $this->hotel_catering_package_id,
            'name'         => $this->name,
            'description'  => $this->description ?? '',
            'inclusions'   => $this->inclusions ?? '',
            'pricePerHead' => (int) $this->price_per_head,
            'minGuests'    => (int) $this->min_guests,
            'isActive'     => (bool) $this->is_active,
            'img'          => \App\Support\HotelImageStore::url($this->image),
        ];
    }
}
