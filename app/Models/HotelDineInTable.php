<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * A physical dining table: Restaurant Management adds and removes them, Front Desk
 * reserves an Available one for a customer — name, contact, when they are due and
 * how many are coming — and Restaurant Management bills and closes it out once they
 * have eaten.
 */
class HotelDineInTable extends Model
{
    public const STATUSES = [
        'Available',
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
            'reservedFor' => optional($this->reserved_for)->toIso8601String(),
            'assignedBy' => $this->assigned_by ?? '',
            'assignedAt' => optional($this->assigned_at)->toIso8601String(),
            'updatedAt' => optional($this->updated_at)->toIso8601String(),
        ];
    }
}
