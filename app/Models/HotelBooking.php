<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * One guest's stay in one room.
 *
 * This is the row that used to be the hotel_rooms.reservation JSON blob. Because it is
 * now its own table a room can have a whole history of stays, past bookings survive the
 * next guest, and Verify Guest can search across every booking instead of walking rooms.
 *
 * Lifecycle, and who moves it:
 *   Reserved    Front Desk / the public site took the booking and the payment
 *   Arrived     Front Desk confirmed the guest at the desk (Verify Guest)
 *   Checked In  Room Management handed the room over; the room goes Occupied
 *   Checked Out the stay ended; the room is released back to Available
 *   Cancelled   the booking was called off before anyone arrived
 */
class HotelBooking extends Model
{
    public const STATUSES = [
        'Reserved',
        'Arrived',
        'Checked In',
        'Checked Out',
        'Cancelled',
    ];

    /** Statuses that still hold the room. Anything else is history. */
    public const OPEN_STATUSES = ['Reserved', 'Arrived', 'Checked In'];

    /** Length of one charged stay block, in hours — matches the front-end's BLOCK_HOURS. */
    public const BLOCK_HOURS = 12;

    protected $primaryKey = 'hotel_booking_id';

    protected $fillable = [
        'group_name',
        'faculty_id',
        'group_id',
        'hotel_room_id',
        'hotel_guest_id',
        'status',
        'check_in',
        'check_in_time',
        'check_out',
        'room_rate',
        'reserved_at',
        'arrived_at',
        'checked_in_at',
        'checked_out_at',
        'cancelled_at',
        'booked_by',
        'notes',
    ];

    protected $casts = [
        'check_in'       => 'date',
        'check_out'      => 'date',
        'room_rate'      => 'integer',
        'reserved_at'    => 'datetime',
        'arrived_at'     => 'datetime',
        'checked_in_at'  => 'datetime',
        'checked_out_at' => 'datetime',
        'cancelled_at'   => 'datetime',
    ];

    public function room(): BelongsTo
    {
        return $this->belongsTo(HotelRoom::class, 'hotel_room_id', 'hotel_room_id');
    }

    public function guest(): BelongsTo
    {
        return $this->belongsTo(HotelGuest::class, 'hotel_guest_id', 'hotel_guest_id');
    }

    public function payments(): HasMany
    {
        return $this->hasMany(HotelBookingPayment::class, 'hotel_booking_id', 'hotel_booking_id');
    }

    /** Bookings that still hold their room. */
    public function scopeOpen(Builder $query): Builder
    {
        return $query->whereIn('status', self::OPEN_STATUSES);
    }

    public static function normalizeStatus(?string $value): string
    {
        $raw = mb_strtolower(trim((string) $value));

        foreach (self::STATUSES as $status) {
            if (mb_strtolower($status) === $raw) {
                return $status;
            }
        }

        return 'Reserved';
    }

    public function isOpen(): bool
    {
        return in_array($this->status, self::OPEN_STATUSES, true);
    }

    /**
     * Reserved -> Arrived is the only distinction the desk screens draw, and both
     * Checked In and Checked Out imply the guest turned up.
     */
    public function arrivalStatus(): string
    {
        return $this->arrived_at || in_array($this->status, ['Arrived', 'Checked In', 'Checked Out'], true)
            ? 'Arrived'
            : 'Reserved';
    }

    /** Charged 12-hour blocks between check-in and check-out; at least one. */
    public function stayBlocks(): int
    {
        if (!$this->check_in || !$this->check_out) {
            return 1;
        }

        $clock = preg_match('/^\d{1,2}:\d{2}/', (string) $this->check_in_time)
            ? substr((string) $this->check_in_time, 0, 5)
            : '00:00';

        try {
            $start = Carbon::parse($this->check_in->toDateString() . ' ' . $clock);
            $end   = Carbon::parse($this->check_out->toDateString() . ' ' . $clock);
        } catch (\Throwable $e) {
            return 1;
        }

        $hours = $start->diffInMinutes($end, false) / 60;

        return $hours <= 0 ? 1 : max(1, (int) ceil($hours / self::BLOCK_HOURS));
    }

    public function totalDue(): float
    {
        return $this->stayBlocks() * (float) $this->room_rate;
    }

    public function amountPaid(): float
    {
        return (float) $this->payments->sum('amount_paid');
    }

    /**
     * The shape the room grid, Verify Guest and Guest Details all read as
     * `room.reservation`. Key names are the ones the old JSON blob used, so the
     * front-end kept working unchanged when the blob became these tables.
     */
    public function toReservationArray(): array
    {
        $guest   = $this->guest;
        $payment = $this->payments->sortByDesc('paid_at')->first();

        return [
            'bookingId'     => $this->hotel_booking_id,
            'guestId'       => $this->hotel_guest_id,
            'status'        => $this->status,
            'fullName'      => $guest?->full_name ?? 'Guest',
            'contactNo'     => $guest?->contact_no ?? '',
            'email'         => $guest?->email ?? '',
            'idNumber'      => $guest?->id_number ?? '',
            'checkIn'       => optional($this->check_in)->toDateString(),
            'checkInTime'   => $this->check_in_time ?? '',
            'checkOut'      => optional($this->check_out)->toDateString(),
            'roomRate'      => (int) $this->room_rate,
            'totalDue'      => $this->totalDue(),
            'amountPaid'    => $this->amountPaid(),
            'balance'       => max(0, $this->totalDue() - $this->amountPaid()),
            'reservedAt'    => optional($this->reserved_at)->toIso8601String(),
            'arrivalStatus' => $this->arrivalStatus(),
            'arrivedAt'     => optional($this->arrived_at)->toIso8601String(),
            'checkedInAt'   => optional($this->checked_in_at)->toIso8601String(),
            'checkedOutAt'  => optional($this->checked_out_at)->toIso8601String(),
            'bookedBy'      => $this->booked_by ?? '',
            'notes'         => $this->notes ?? '',
            'payment'       => $payment?->toTemplateArray(),
            'payments'      => $this->payments->map->toTemplateArray()->values()->all(),
        ];
    }
}
