<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * A physical dining table: Restaurant Management adds and removes them, Front Desk
 * seats a guest at an Available one, Restaurant Management closes it out when the
 * guest leaves.
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
        'party_size',
        'assigned_by',
        'assigned_at',
    ];

    protected $casts = [
        'capacity' => 'integer',
        'party_size' => 'integer',
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
            'partySize' => $this->party_size,
            'assignedBy' => $this->assigned_by ?? '',
            'assignedAt' => optional($this->assigned_at)->toIso8601String(),
            'updatedAt' => optional($this->updated_at)->toIso8601String(),
        ];
    }
}
