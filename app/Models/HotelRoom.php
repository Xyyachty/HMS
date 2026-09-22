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
    // Housekeeping condition only. Who is staying in the room is a HotelBooking
    // status (Booked / Arrived / Checked In / Checked Out), not this — see
    // activeBooking() and the Rooms page calendar, which is what carries occupancy now.
    public const STATUSES = [
        'Available',
        'Cleaning',
        'Maintenance',
    ];

    /**
     * How many photographs one room keeps, the primary one included. Three is what
     * the picker draws and what the card rotates through.
     */
    public const GALLERY_MAX = 3;

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
        'gallery',
    ];

    protected $casts = [
        'price' => 'integer',
        'gallery' => 'array',
    ];

    /**
     * Whether this database has the `gallery` column yet.
     *
     * Asked because the column arrives in a migration of its own: between pulling
     * the code and running that migration, a save carrying a gallery would be an
     * INSERT against a column that is not there. Answered once per request.
     */
    public static function supportsGallery(): bool
    {
        static $has = null;

        if ($has === null) {
            $has = \Illuminate\Support\Facades\Schema::hasColumn('hotel_rooms', 'gallery');
        }

        return $has;
    }

    /**
     * Every photograph of this room, primary first, as URLs.
     *
     * The primary one is not repeated if it also appears in the gallery: the card
     * pages straight through this list, and a duplicate would read as the rotation
     * stalling on the first slide.
     *
     * @return list<string>
     */
    public function imageUrls(): array
    {
        $paths = self::supportsGallery() && is_array($this->gallery) ? $this->gallery : [];
        array_unshift($paths, $this->image);

        $urls = [];
        foreach ($paths as $path) {
            $url = \App\Support\HotelImageStore::url(is_string($path) ? $path : '');
            if ($url !== '' && !in_array($url, $urls, true)) {
                $urls[] = $url;
            }
        }

        return $urls;
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(HotelBooking::class, 'hotel_room_id', 'hotel_room_id');
    }

    /**
     * The stay that currently holds the room, if any.
     *
     * A room may now carry several open bookings at once — one guest in the room and
     * further stays booked for later dates. "Active" is the earliest of them: that is
     * the guest the desk is working with today. Picking the newest row instead would
     * put next month's booking on the room card, in Verify Guest and in room service.
     * hotel_booking_id breaks ties between two stays starting the same day.
     */
    public function activeBooking(): HasOne
    {
        // The status filter belongs inside ofMany(): constraints chained onto the
        // relation itself would filter after the subquery had already picked the
        // booking, so a room whose last stay ended would look like it had none open.
        return $this->hasOne(HotelBooking::class, 'hotel_room_id', 'hotel_room_id')
            ->ofMany(
                ['check_in' => 'MIN', 'hotel_booking_id' => 'MIN'],
                fn ($query) => $query->whereIn('status', HotelBooking::OPEN_STATUSES)
            );
    }

    /** Every stay still holding a claim on the room, in the order they fall. */
    public function openBookings(): HasMany
    {
        return $this->hasMany(HotelBooking::class, 'hotel_room_id', 'hotel_room_id')
            ->open()
            ->orderBy('check_in');
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
            : $this->activeBooking()->with(['guest', 'payments', 'foodOrders', 'charges', 'addons'])->first();

        $openBookings = $this->relationLoaded('openBookings')
            ? $this->openBookings
            : $this->openBookings()->get();

        // "id"/"dbId" are the room-grid front-end's keys, not column names.
        return [
            'id'           => 'db-' . $this->hotel_room_id,
            'dbId'         => $this->hotel_room_id,
            'name'         => $this->name,
            'label'        => $this->category,
            'category'     => $this->category,
            'status'       => $this->status,
            'price'        => (int) $this->price,
            'desc'         => $this->description ?? '',
            'img'          => \App\Support\HotelImageStore::url($this->image),
            // Primary first. The card rotates through these; 'img' stays for the
            // places that only ever draw one.
            'imgs'         => $this->imageUrls(),
            'reservation'  => $booking?->toReservationArray(),
            'bookedRanges' => $openBookings
                ->filter(fn (HotelBooking $b) => $b->check_in && $b->check_out)
                ->map(fn (HotelBooking $b) => [
                    'bookingId' => $b->hotel_booking_id,
                    // 'to' is exclusive — the checkout date itself is free for the next guest.
                    'from'      => $b->check_in->toDateString(),
                    'to'        => $b->check_out->toDateString(),
                    'status'    => $b->status,
                ])
                ->values()
                ->all(),
            'amenities'    => [['icon' => 'fa-bed', 'text' => 'Bed'], ['icon' => 'fa-wifi', 'text' => 'WiFi']],
        ];
    }

    /**
     * The same room as a guest on the public site may see it.
     *
     * A separate method rather than a flag on toTemplateArray(), because the difference is
     * not cosmetic and the default must stay the safe one: `reservation` carries the
     * current occupant's full name, email, contact number, ID number and every payment
     * row through HotelBooking::toReservationArray(). The staff grid needs all of that.
     * A public page polling every eight seconds would be publishing the hotel's guest
     * list, so here it is simply absent.
     *
     * bookedRanges stays. It is dates and statuses with nobody's name on it, and it is
     * what the availability calendar a guest books against is drawn from.
     */
    public function toPublicArray(): array
    {
        $openBookings = $this->relationLoaded('openBookings')
            ? $this->openBookings
            : $this->openBookings()->get();

        return [
            'id'           => 'db-' . $this->hotel_room_id,
            'dbId'         => $this->hotel_room_id,
            'name'         => $this->name,
            'label'        => $this->category,
            'category'     => $this->category,
            'status'       => $this->status,
            'price'        => (int) $this->price,
            'desc'         => $this->description ?? '',
            'img'          => \App\Support\HotelImageStore::url($this->image),
            // Primary first. The card rotates through these; 'img' stays for the
            // places that only ever draw one.
            'imgs'         => $this->imageUrls(),
            'bookedRanges' => $openBookings
                ->filter(fn (HotelBooking $b) => $b->check_in && $b->check_out)
                ->map(fn (HotelBooking $b) => [
                    // 'to' is exclusive — the checkout date itself is free for the next guest.
                    'from'   => $b->check_in->toDateString(),
                    'to'     => $b->check_out->toDateString(),
                    // No bookingId and no guest: a visitor needs to know the room is taken,
                    // not who has it or which record says so.
                    'status' => $b->status,
                ])
                ->values()
                ->all(),
            'amenities'    => [['icon' => 'fa-bed', 'text' => 'Bed'], ['icon' => 'fa-wifi', 'text' => 'WiFi']],
        ];
    }
}
