<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Housekeeping's post-checkout pass on a room.
 *
 * Opened by HotelHousekeepingDesk::openInspection() the moment a stay ends, inside the
 * same transaction as the check-out itself — see HotelBookingDesk::checkOut(). A
 * damage/repair/missing-item finding does not live on this row; it becomes a
 * HotelComplaint (department 'maintenance'), because that queue, its statuses and its
 * notifications already exist. This model only tracks the inspection pass and, through
 * complaints(), which issues it raised.
 */
class HotelRoomInspection extends Model
{
    /** Where the room is in the post-checkout pass. */
    public const STATUSES = [
        'Pending',                // room just came free; nobody has looked at it
        'Inspecting',             // a housekeeper opened it
        'Awaiting Repair',        // an issue was handed to Maintenance
        'Awaiting Re-inspection', // Maintenance closed every issue; final pass due
        'Completed',              // room is sellable again
    ];

    /** Everything short of Completed still needs Housekeeping's attention. */
    public const OPEN_STATUSES = ['Pending', 'Inspecting', 'Awaiting Repair', 'Awaiting Re-inspection'];

    /**
     * What the inspector found, and the complaint category each one suggests. Null
     * means nothing to report — the room only needed cleaning, so no complaint is
     * filed. The category is a starting point: HotelHousekeepingDesk::reportIssue()
     * lets the caller override it, the same way the Front Desk complaint form does.
     */
    public const FINDINGS = [
        'Cleaning Only'     => null,
        'Damaged Equipment' => 'Appliance / TV',
        'Needs Repair'      => 'Furniture / Fixtures',
        'Missing Items'     => 'Other',
    ];

    protected $primaryKey = 'hotel_room_inspection_id';

    protected $fillable = [
        'group_name',
        'faculty_id',
        'group_id',
        'hotel_room_id',
        'hotel_booking_id',
        'room_name',
        'guest_name',
        'checked_out_at',
        'status',
        'finding',
        'notes',
        'inspected_by',
        'completed_by',
        'inspected_at',
        'completed_at',
    ];

    protected $casts = [
        'checked_out_at' => 'datetime',
        'inspected_at'   => 'datetime',
        'completed_at'   => 'datetime',
    ];

    public function room(): BelongsTo
    {
        return $this->belongsTo(HotelRoom::class, 'hotel_room_id', 'hotel_room_id');
    }

    public function booking(): BelongsTo
    {
        return $this->belongsTo(HotelBooking::class, 'hotel_booking_id', 'hotel_booking_id');
    }

    /** Issues this inspection raised, oldest first — the order they were found in. */
    public function complaints(): HasMany
    {
        return $this->hasMany(HotelComplaint::class, 'hotel_room_inspection_id', 'hotel_room_inspection_id')
            ->orderBy('hotel_complaint_id');
    }

    public function scopeOpen(Builder $query): Builder
    {
        return $query->whereIn('status', self::OPEN_STATUSES);
    }

    /** Whether any issue this inspection raised is still open. Gates complete(). */
    public function hasOpenIssues(): bool
    {
        return $this->complaints()
            ->whereNotIn('status', ['Resolved', 'Cancelled'])
            ->exists();
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

    public static function normalizeFinding(?string $value): ?string
    {
        $raw = mb_strtolower(trim((string) $value));

        foreach (array_keys(self::FINDINGS) as $finding) {
            if (mb_strtolower($finding) === $raw) {
                return $finding;
            }
        }

        return null;
    }

    /** Shape sent to the Housekeeping front-end. */
    public function toTemplateArray(): array
    {
        $issues = $this->relationLoaded('complaints')
            ? $this->complaints
            : $this->complaints()->get();

        return [
            'id'             => $this->hotel_room_inspection_id,
            'roomId'         => $this->hotel_room_id,
            'roomName'       => $this->room_name,
            'roomStatus'     => $this->room?->status,
            'guestName'      => $this->guest_name ?? '',
            'checkedOutAt'   => optional($this->checked_out_at)->toIso8601String(),
            'status'         => $this->status,
            'finding'        => $this->finding,
            'notes'          => $this->notes ?? '',
            'inspectedBy'    => $this->inspected_by ?? '',
            'completedBy'    => $this->completed_by ?? '',
            'inspectedAt'    => optional($this->inspected_at)->toIso8601String(),
            'completedAt'    => optional($this->completed_at)->toIso8601String(),
            'issues'         => $issues->map(fn (HotelComplaint $c) => $c->toTemplateArray())->values()->all(),
            'openIssueCount' => $issues->whereNotIn('status', ['Resolved', 'Cancelled'])->count(),
        ];
    }
}
