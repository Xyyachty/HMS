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
     * Whether one of these sleeps one more guest in the room: an extra or folding bed,
     * a cot, a crib. Bedding (sheets, linen, covers) does not.
     *
     * ponytail: read off the name, so a bed called something else is missed. Add a
     * "sleeps" column on hotel_addons if teams name beds in ways this does not catch.
     */
    public function isExtraBed(): bool
    {
        return (bool) preg_match('/\b(beds?|cots?|cribs?)\b/i', (string) $this->name)
            && !preg_match('/sheet|linen|cover|spread/i', (string) $this->name);
    }

    /**
     * How many extra guests these add-on lines sleep. $lines: [['addon_id' => int, 'qty' => int], ...].
     */
    public static function extraBedsIn(array $lines): int
    {
        $qty = collect($lines)->groupBy('addon_id')->map(fn ($l) => $l->sum('qty'));
        if ($qty->isEmpty()) {
            return 0;
        }

        return (int) self::whereIn('hotel_addon_id', $qty->keys())->get()
            ->filter->isExtraBed()
            ->sum(fn (self $addon) => $qty[$addon->hotel_addon_id]);
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
            'extraBed'  => $this->isExtraBed(),
            'img'       => \App\Support\HotelImageStore::url($this->image),
        ];
    }
}
