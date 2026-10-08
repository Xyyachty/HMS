<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * One guest complaint: recorded by the Front Desk, worked by Housekeeping,
 * Maintenance, Room Management or Restaurant Services, and closed by the Front
 * Desk once the guest has confirmed the fix.
 *
 * Front Desk itself is never a complaint department — it receives and coordinates,
 * it is not complained about here.
 */
class HotelComplaint extends Model
{
    /**
     * What a guest complains about, and the department that owns it. The Front Desk
     * picks the department first and then one of its categories, so a category always
     * lands with the team that owns it. Covers both facility problems and poor staff
     * service — see SERVICE_CATEGORIES.
     */
    public const CATEGORY_DEPARTMENTS = [
        // Housekeeping
        'Dirty Room'                                   => 'housekeeping',
        'Dirty Bathroom'                               => 'housekeeping',
        'Missing Towels or Toiletries'                 => 'housekeeping',
        'Unchanged Bed Sheets'                         => 'housekeeping',
        'Bad Room Odor'                                => 'housekeeping',
        'Delayed Room Cleaning'                        => 'housekeeping',
        'Poor Housekeeping Service'                    => 'housekeeping',
        'Rude or Unprofessional Housekeeping Staff'    => 'housekeeping',
        'Other Housekeeping Problems'                  => 'housekeeping',
        // Maintenance
        'Air Conditioner Not Working'                  => 'maintenance',
        'Broken Lights'                                => 'maintenance',
        'Plumbing or Water Problems'                   => 'maintenance',
        'Broken Television or Appliances'              => 'maintenance',
        'Damaged Furniture'                            => 'maintenance',
        'Broken Door or Window'                        => 'maintenance',
        'Delayed Maintenance Service'                  => 'maintenance',
        'Poor Maintenance Service'                     => 'maintenance',
        'Rude or Unprofessional Maintenance Staff'     => 'maintenance',
        'Other Maintenance Problems'                   => 'maintenance',
        // Room Management
        'Wrong Room Assignment'                        => 'room_management',
        'Room Not Ready'                               => 'room_management',
        'Incorrect Room Information'                   => 'room_management',
        'Room Unavailable'                             => 'room_management',
        'Room Facilities Not as Advertised'            => 'room_management',
        'Room Availability Problems'                   => 'room_management',
        'Poor Room Management Service'                 => 'room_management',
        'Rude or Unprofessional Room Management Staff' => 'room_management',
        'Other Room Management Problems'               => 'room_management',
        // Restaurant Services
        'Wrong Food Order'                             => 'restaurant_management',
        'Cold or Poor-Quality Food'                    => 'restaurant_management',
        'Delayed Food Service'                         => 'restaurant_management',
        'Missing Food Items'                           => 'restaurant_management',
        'Incorrect Food Bill'                          => 'restaurant_management',
        'Dirty Dining Area'                            => 'restaurant_management',
        'Poor Restaurant Service'                      => 'restaurant_management',
        'Rude or Unprofessional Restaurant Staff'      => 'restaurant_management',
        'Other Restaurant Problems'                    => 'restaurant_management',
    ];

    /**
     * Categories about how staff treated the guest rather than about a facility.
     * The department still resolves them; faculty can filter them out for review.
     */
    public const SERVICE_CATEGORIES = [
        'Delayed Room Cleaning',
        'Poor Housekeeping Service',
        'Rude or Unprofessional Housekeeping Staff',
        'Delayed Maintenance Service',
        'Poor Maintenance Service',
        'Rude or Unprofessional Maintenance Staff',
        'Poor Room Management Service',
        'Rude or Unprofessional Room Management Staff',
        'Delayed Food Service',
        'Poor Restaurant Service',
        'Rude or Unprofessional Restaurant Staff',
    ];

    /** Keys are the team role that owns each queue, so access checks read them directly. */
    public const DEPARTMENTS = [
        'housekeeping'          => 'Housekeeping',
        'maintenance'           => 'Maintenance',
        'room_management'       => 'Room Management',
        'restaurant_management' => 'Restaurant Services',
    ];

    /**
     * Front Desk files as Pending; the department moves it to In Progress and
     * Resolved; the Front Desk closes it once the guest confirms. Cancelled is the
     * exit for a complaint withdrawn before it was resolved.
     */
    public const STATUSES = [
        'Pending',
        'In Progress',
        'Resolved',
        'Closed',
        'Cancelled',
    ];

    /** The working pipeline, in order. Cancelled sits off to the side as an exit. */
    public const FLOW = ['Pending', 'In Progress', 'Resolved', 'Closed'];

    /** Nothing moves once a complaint reaches one of these. */
    public const FINAL_STATUSES = ['Closed', 'Cancelled'];

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
        'history',
    ];

    protected $casts = [
        'resolved_at' => 'datetime',
        'history'     => 'array',
    ];

    public static function normalizeCategory(?string $value): string
    {
        $raw = mb_strtolower(trim((string) $value));

        foreach (array_keys(self::CATEGORY_DEPARTMENTS) as $category) {
            if (mb_strtolower($category) === $raw) {
                return $category;
            }
        }

        return 'Other Maintenance Problems';
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

        return 'Pending';
    }

    /** The department a category routes to. */
    public static function departmentForCategory(?string $category): string
    {
        return self::CATEGORY_DEPARTMENTS[self::normalizeCategory($category)] ?? 'maintenance';
    }

    /** @return list<string> the categories the given departments own, in display order */
    public static function categoriesFor(string ...$departments): array
    {
        return array_keys(array_filter(
            self::CATEGORY_DEPARTMENTS,
            fn ($department) => in_array($department, $departments, true)
        ));
    }

    /**
     * Whether $to is a legal move from $from: forward through FLOW only, or
     * Cancelled as an exit from Pending or In Progress. Closed is reachable from
     * Resolved only — the Front Desk cannot close what the department has not
     * resolved. Who may make which move is the route's job, not this check's.
     */
    public static function isForwardTransition(string $from, string $to): bool
    {
        if ($from === $to || in_array($from, self::FINAL_STATUSES, true)) {
            return false;
        }

        if ($to === 'Cancelled') {
            return $from !== 'Resolved';
        }

        if ($to === 'Closed') {
            return $from === 'Resolved';
        }

        $fromIndex = array_search($from, self::FLOW, true);
        $toIndex = array_search($to, self::FLOW, true);

        return $fromIndex !== false && $toIndex !== false && $toIndex > $fromIndex;
    }

    /**
     * Raised by a Housekeeping inspection or an amenity repair rather than by a
     * guest. Those finish at Resolved: there is no guest for the Front Desk to
     * confirm with.
     */
    public function isInternal(): bool
    {
        return $this->hotel_room_inspection_id !== null || $this->hotel_amenity_id !== null;
    }

    public function isServiceComplaint(): bool
    {
        return in_array($this->category, self::SERVICE_CATEGORIES, true);
    }

    /** Appends one entry to the complaint's history. Persisted on the next save(). */
    public function logHistory(string $event, ?string $by, ?string $note = null): void
    {
        $history = $this->history ?? [];
        $history[] = array_filter([
            'event' => $event,
            'by'    => $by,
            'note'  => $note,
            'at'    => now()->toIso8601String(),
        ], fn ($value) => $value !== null && $value !== '');
        $this->history = $history;
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
            'internal'        => $this->isInternal(),
            // Empty when the guest is not staying in a room (a restaurant walk-in).
            'roomNumber'      => $this->room_number ?? '',
            'guestName'       => $this->guest_name ?? '',
            'category'        => $this->category,
            'kind'            => $this->isServiceComplaint() ? 'service' : 'facility',
            'department'      => $this->department,
            'departmentLabel' => $this->departmentLabel(),
            'details'         => $this->details,
            'status'          => $this->status,
            'resolutionNote'  => $this->resolution_note ?? '',
            'filedBy'         => $this->filed_by ?? '',
            'handledBy'       => $this->handled_by ?? '',
            'history'         => array_values($this->history ?? []),
            'filedAt'         => optional($this->created_at)->toIso8601String(),
            'resolvedAt'      => optional($this->resolved_at)->toIso8601String(),
            'updatedAt'       => optional($this->updated_at)->toIso8601String(),
        ];
    }
}
