<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * One guest complaint: recorded by the Front Desk, worked by Maintenance or
 * Housekeeping.
 */
class HotelComplaint extends Model
{
    /**
     * What a guest complains about, and who handles it by default. The Front Desk
     * sees this department pre-selected and may change it before saving, so this is
     * a suggestion rather than a rule.
     */
    public const CATEGORY_DEPARTMENTS = [
        'Aircon / Cooling'      => 'maintenance',
        'Plumbing / Water'      => 'maintenance',
        'Electrical / Lighting' => 'maintenance',
        'Appliance / TV'        => 'maintenance',
        'Furniture / Fixtures'  => 'maintenance',
        'Room Cleanliness'      => 'housekeeping',
        'Linens / Towels'       => 'housekeeping',
        'Toiletries / Supplies' => 'housekeeping',
        'Trash / Odor'          => 'housekeeping',
        'Noise / Disturbance'   => 'housekeeping',
        'Other'                 => 'maintenance',
    ];

    public const DEPARTMENTS = [
        'maintenance'  => 'Maintenance',
        'housekeeping' => 'Housekeeping',
    ];

    /** Front Desk files as Open; the department moves it along. */
    public const STATUSES = [
        'Open',
        'In Progress',
        'Resolved',
        'Cancelled',
    ];

    /** The working pipeline, in order. Cancelled sits off to the side as an exit. */
    public const FLOW = ['Open', 'In Progress', 'Resolved'];

    protected $primaryKey = 'hotel_complaint_id';

    protected $fillable = [
        'group_name',
        'faculty_id',
        'group_id',
        'hotel_room_inspection_id',
        'hotel_amenity_id',
        'room_number',
        'guest_name',
        'category',
        'department',
        'details',
        'status',
        'resolution_note',
        'filed_by',
        'handled_by',
        'resolved_at',
    ];

    protected $casts = [
        'resolved_at' => 'datetime',
    ];

    public static function normalizeCategory(?string $value): string
    {
        $raw = mb_strtolower(trim((string) $value));

        foreach (array_keys(self::CATEGORY_DEPARTMENTS) as $category) {
            if (mb_strtolower($category) === $raw) {
                return $category;
            }
        }

        return 'Other';
    }

    public static function normalizeDepartment(?string $value): string
    {
        $raw = mb_strtolower(trim((string) $value));

        return array_key_exists($raw, self::DEPARTMENTS) ? $raw : 'maintenance';
    }

    public static function normalizeStatus(?string $value): string
    {
        $raw = mb_strtolower(trim((string) $value));

        foreach (self::STATUSES as $status) {
            if (mb_strtolower($status) === $raw) {
                return $status;
            }
        }

        return 'Open';
    }

    /** The department a category routes to before anyone overrides it. */
    public static function departmentForCategory(?string $category): string
    {
        return self::CATEGORY_DEPARTMENTS[self::normalizeCategory($category)] ?? 'maintenance';
    }

    /**
     * Whether $to is a legal move from $from: forward through FLOW only, or
     * Cancelled as an exit from anywhere still open. Nothing moves once a
     * complaint has reached Resolved or Cancelled. Reassigning a complaint to the
     * other department resets it to Open separately from this check — that reset
     * is the new department starting fresh, not a user demoting a status.
     */
    public static function isForwardTransition(string $from, string $to): bool
    {
        if ($from === $to || in_array($from, ['Resolved', 'Cancelled'], true)) {
            return false;
        }

        if ($to === 'Cancelled') {
            return true;
        }

        $fromIndex = array_search($from, self::FLOW, true);
        $toIndex = array_search($to, self::FLOW, true);

        return $fromIndex !== false && $toIndex !== false && $toIndex > $fromIndex;
    }

    public function departmentLabel(): string
    {
        return self::DEPARTMENTS[$this->department] ?? 'Maintenance';
    }

    /** Shape sent to the complaints front-end. */
    public function toTemplateArray(): array
    {
        // "id" here is the front-end's key for a complaint, not the column name.
        return [
            'id'              => $this->hotel_complaint_id,
            'inspectionId'    => $this->hotel_room_inspection_id,
            'amenityId'       => $this->hotel_amenity_id,
            'roomNumber'      => $this->room_number,
            'guestName'       => $this->guest_name ?? '',
            'category'        => $this->category,
            'department'      => $this->department,
            'departmentLabel' => $this->departmentLabel(),
            'details'         => $this->details,
            'status'          => $this->status,
            'resolutionNote'  => $this->resolution_note ?? '',
            'filedBy'         => $this->filed_by ?? '',
            'handledBy'       => $this->handled_by ?? '',
            'filedAt'         => optional($this->created_at)->toIso8601String(),
            'resolvedAt'      => optional($this->resolved_at)->toIso8601String(),
            'updatedAt'       => optional($this->updated_at)->toIso8601String(),
        ];
    }
}
