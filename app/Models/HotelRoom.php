<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * A room in the team's inventory: what Room Management creates and prices.
 *
 * Nothing about a guest lives here any more. Who is staying in the room is a
 * HotelBooking row (see the 2026_08_11_000002 migration); this model only knows how to
 * reach the booking that currently holds it.
 */
class HotelRoom extends Model
{
    public const STATUSES = [
        'Available',
        'Reserved',
        'Occupied',
        'Cleaning',
        'Maintenance',
    ];

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
    ];

    protected $casts = [
        'price' => 'integer',
    ];

    public function bookings(): HasMany
    {
        return $this->hasMany(HotelBooking::class, 'hotel_room_id', 'hotel_room_id');
    }

    /**
     * The stay that currently holds the room, if any. Latest wins: a room should only
     * ever have one open booking, and if a double-booking slipped through, the newest
     * is the one the desk just made.
     */
    public function activeBooking(): HasOne
    {
        // The status filter belongs inside ofMany(): constraints chained onto the
        // relation itself would filter after the subquery had already picked the newest
        // booking, so a room whose last stay ended would look like it had none open.
        return $this->hasOne(HotelBooking::class, 'hotel_room_id', 'hotel_room_id')
            ->ofMany(
                ['hotel_booking_id' => 'MAX'],
                fn ($query) => $query->whereIn('status', HotelBooking::OPEN_STATUSES)
            );
    }

    public static function normalizeStatus(?string $value): string
    {
        $raw = mb_strtolower(trim((string) $value));

        foreach (self::STATUSES as $status) {
            if (mb_strtolower($status) === $raw) {
                return $status;
            }
        }

        return 'Available';
    }

    /**
     * Shape the room grid reads. `reservation` is projected from the open booking
     * rather than stored — the front-end's key names predate the split.
     */
    public function toTemplateArray(): array
    {
        $booking = $this->relationLoaded('activeBooking')
            ? $this->activeBooking
            : $this->activeBooking()->with(['guest', 'payments'])->first();

        // "id"/"dbId" are the room-grid front-end's keys, not column names.
        return [
            'id'          => 'db-' . $this->hotel_room_id,
            'dbId'        => $this->hotel_room_id,
            'name'        => $this->name,
            'label'       => $this->category,
            'category'    => $this->category,
            'status'      => $this->status,
            'price'       => (int) $this->price,
            'desc'        => $this->description ?? '',
            'img'         => \App\Support\HotelImageStore::url($this->image),
            'reservation' => $booking?->toReservationArray(),
            'amenities'   => [['icon' => 'fa-bed', 'text' => 'Bed'], ['icon' => 'fa-wifi', 'text' => 'WiFi']],
        ];
    }
}
