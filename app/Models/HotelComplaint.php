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

    public const PRIORITIES = [
        'Low',
        'Normal',
        'Urgent',
    ];

    protected $primaryKey = 'hotel_complaint_id';

    protected $fillable = [
        'group_name',
        'faculty_id',
        'group_id',
        'room_number',
        'guest_name',
        'category',
        'department',
        'priority',
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

    public static function normalizePriority(?string $value): string
    {
        $raw = mb_strtolower(trim((string) $value));

        foreach (self::PRIORITIES as $priority) {
            if (mb_strtolower($priority) === $raw) {
                return $priority;
            }
        }

        return 'Normal';
    }

    /** The department a category routes to before anyone overrides it. */
    public static function departmentForCategory(?string $category): string
    {
        return self::CATEGORY_DEPARTMENTS[self::normalizeCategory($category)] ?? 'maintenance';
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
            'roomNumber'      => $this->room_number,
            'guestName'       => $this->guest_name ?? '',
            'category'        => $this->category,
            'department'      => $this->department,
            'departmentLabel' => $this->departmentLabel(),
            'priority'        => $this->priority,
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
