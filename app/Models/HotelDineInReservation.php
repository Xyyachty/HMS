<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;

/**
 * A guest's record of a table they reserved on the hotel's website, reached through
 * the private link they were given. While open, its status is read off the table it
 * sits on; see the migration and HotelTableDesk.
 */
class HotelDineInReservation extends Model
{
    /** How the visit ended, written when the table is freed. */
    public const OUTCOMES = ['Completed', 'Cancelled', 'No-Show'];

    /** The link keeps working this long after the time the guest was due. */
    public const LINK_DAYS = 7;

    protected $primaryKey = 'hotel_dine_in_reservation_id';

    protected $fillable = [
        'group_name',
        'faculty_id',
        'group_id',
        'hotel_dine_in_table_id',
        'table_name',
        'hotel_customer_id',
        'guest_name',
        'party_size',
        'reserved_for',
        'token_hash',
        'outcome',
        'ended_at',
    ];

    protected $hidden = ['token_hash'];

    protected $casts = [
        'party_size' => 'integer',
        'reserved_for' => 'datetime',
        'ended_at' => 'datetime',
    ];

    /** False until the migration has run, so a deploy that has not migrated yet keeps working. */
    public static function supported(): bool
    {
        static $supported = null;

        return $supported ??= Schema::hasTable('hotel_dine_in_reservations');
    }

    public static function hashToken(string $token): string
    {
        return hash('sha256', $token);
    }

    public static function findByToken(string $token): ?self
    {
        return self::supported() && $token !== ''
            ? self::where('token_hash', self::hashToken($token))->first()
            : null;
    }

    public function reference(): string
    {
        return 'REST-' . str_pad((string) $this->hotel_dine_in_reservation_id, 6, '0', STR_PAD_LEFT);
    }

    /** reserved_for is stored as Manila wall-clock; this reads it back that way. */
    public function dueAt(): ?\Carbon\Carbon
    {
        return $this->reserved_for?->copy()->shiftTimezone('Asia/Manila');
    }

    public function linkExpired(): bool
    {
        $due = $this->dueAt();

        return $due !== null && $due->copy()->addDays(self::LINK_DAYS)->isPast();
    }

    /**
     * What the guest sees, and nothing the staff screens keep: no contact number,
     * no staff names, no other table. $table is the table it sits on, if still open.
     */
    public function toGuestArray(?HotelDineInTable $table): array
    {
        // The table's own status while the reservation is on it; the outcome after.
        $live = $this->outcome === null && $table !== null;
        $status = match (true) {
            $live && $table->status === 'Reserved' => 'Confirmed',
            $live && $table->status === 'Occupied' => 'Seated',
            $this->outcome !== null => $this->outcome,
            default => 'Closed',
        };

        return [
            'reference' => $this->reference(),
            'guestName' => $this->guest_name ?: 'Guest',
            'tableName' => $live ? $table->name : $this->table_name,
            'partySize' => $this->party_size,
            'reservedFor' => $this->dueAt()?->toIso8601String(),
            'status' => $status,
        ];
    }
}
