<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * A physical dining table: Restaurant Management adds and removes them, Front Desk
 * reserves an Available one for a customer — name, contact, when they are due and
 * how many are coming — it goes Occupied when that customer actually turns up, and
 * Restaurant Management bills and closes it out once they have eaten.
 *
 * Reserved and Occupied are deliberately not the same thing: a held table is not a
 * table with anyone at it, and only a table with someone at it can be ordered at.
 */
class HotelDineInTable extends Model
{
    /**
     * Available  nobody has claimed it
     * Reserved   Front Desk is holding it for a customer who has not arrived yet
     * Occupied   the customer is sitting at it, so it can be ordered at and billed
     */
    public const STATUSES = [
        'Available',
        'Reserved',
        'Occupied',
    ];

    protected $primaryKey = 'hotel_dine_in_table_id';

    protected $fillable = [
        'group_name',
        'faculty_id',
        'group_id',
        'name',
        'capacity',
        'status',
        'guest_name',
        'contact_no',
        'party_size',
        'reserved_for',
        'assigned_by',
        'assigned_at',
        'party_ref',
    ];

    protected $casts = [
        'capacity' => 'integer',
        'party_size' => 'integer',
        // When the customer is due, as opposed to assigned_at — when the desk wrote
        // the reservation down.
        'reserved_for' => 'datetime',
        'assigned_at' => 'datetime',
    ];

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

    /** False until the party_ref migration has run. */
    public static function partySupported(): bool
    {
        static $has = null;

        return $has ??= \Illuminate\Support\Facades\Schema::hasColumn('hotel_dine_in_tables', 'party_ref');
    }

    /**
     * This table and every other table reserved with it for the same guest, in name
     * order. Just this table when it was reserved alone.
     *
     * @return \Illuminate\Support\Collection<int, self>
     */
    public function partyTables(): \Illuminate\Support\Collection
    {
        if (!self::partySupported() || !$this->party_ref) {
            return collect([$this]);
        }

        return self::where('group_name', $this->group_name)
            ->where('faculty_id', $this->faculty_id)
            ->where('party_ref', $this->party_ref)
            ->orderBy('name')
            ->get();
    }

    /** "Table 2 + Table 5": the tables of a party, as the bill and receipt name them. */
    public static function joinNames(iterable $tables): string
    {
        return \Illuminate\Support\Str::limit(collect($tables)->pluck('name')->implode(' + '), 250, '…');
    }

    /** Shape sent to the dine-in front-end. */
    public function toTemplateArray(): array
    {
        // "id" here is the front-end's key for a table, not the column name.
        return [
            'id' => $this->hotel_dine_in_table_id,
            'name' => $this->name,
            'capacity' => (int) $this->capacity,
            'status' => $this->status,
            'guestName' => $this->guest_name ?? '',
            'contactNo' => $this->contact_no ?? '',
            'partySize' => $this->party_size,
            // Stored as Manila wall-clock; sent with +08:00 so browsers show that time.
            'reservedFor' => $this->reserved_for?->shiftTimezone('Asia/Manila')->toIso8601String(),
            'assignedBy' => $this->assigned_by ?? '',
            'assignedAt' => optional($this->assigned_at)->toIso8601String(),
            // Shared by the tables reserved together for one guest; null when alone.
            'partyRef' => $this->party_ref ?? null,
            'updatedAt' => optional($this->updated_at)->toIso8601String(),
        ];
    }
}
