<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * One guest's trip to a facility they have to be signed into — the pool, the gym.
 *
 * exited_at is the entire state machine. Null means they are still inside, which is what
 * the capacity check counts and what the Front Desk badge queues. There is no status
 * column: a second way of saying the same thing is a second thing that can be wrong.
 */
class HotelAmenityVisit extends Model
{
    protected $primaryKey = 'hotel_amenity_visit_id';

    protected $fillable = [
        'group_name',
        'faculty_id',
        'group_id',
        'hotel_amenity_id',
        'hotel_booking_id',
        'amenity_name',
        'guest_name',
        'room_name',
        'party_size',
        'entered_at',
        'exited_at',
        'registered_by',
        'closed_by',
        'notes',
    ];

    protected $casts = [
        'party_size' => 'integer',
        'entered_at' => 'datetime',
        'exited_at'  => 'datetime',
    ];

    public function amenity(): BelongsTo
    {
        return $this->belongsTo(HotelAmenity::class, 'hotel_amenity_id', 'hotel_amenity_id');
    }

    public function booking(): BelongsTo
    {
        return $this->belongsTo(HotelBooking::class, 'hotel_booking_id', 'hotel_booking_id');
    }

    /** Visits still open — the guests actually in the facility. */
    public function scopeInside(Builder $query): Builder
    {
        return $query->whereNull('exited_at');
    }

    public function isInside(): bool
    {
        return $this->exited_at === null;
    }

    /**
     * Minutes spent inside, or minutes so far for someone still in there. The screen
     * renders the running figure from entered_at on its own clock; this is the settled
     * number that goes in the register.
     */
    public function minutesSpent(): int
    {
        if (!$this->entered_at) {
            return 0;
        }

        return max(0, $this->entered_at->diffInMinutes($this->exited_at ?? now()));
    }

    /** Shape sent to the Front Desk screen. */
    public function toTemplateArray(): array
    {
        // "id" here is the front-end's key for a visit, not the column name.
        return [
            'id'           => $this->hotel_amenity_visit_id,
            'amenityId'    => $this->hotel_amenity_id,
            'amenityName'  => $this->amenity_name,
            'bookingId'    => $this->hotel_booking_id,
            'guestName'    => $this->guest_name,
            'roomName'     => $this->room_name ?? '',
            'partySize'    => (int) $this->party_size,
            'enteredAt'    => optional($this->entered_at)->toIso8601String(),
            'exitedAt'     => optional($this->exited_at)->toIso8601String(),
            // Derived, never stored — see the class docblock.
            'isInside'     => $this->isInside(),
            'minutes'      => $this->minutesSpent(),
            'registeredBy' => $this->registered_by ?? '',
            'closedBy'     => $this->closed_by ?? '',
            'notes'        => $this->notes ?? '',
        ];
    }
}
