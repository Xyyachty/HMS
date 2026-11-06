<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;

/**
 * A housekeeping request during a stay. See the migration and HotelServiceRequestDesk.
 */
class HotelServiceRequest extends Model
{
    /** What a guest or the Front Desk can ask Housekeeping for. */
    public const TYPES = [
        'Room Cleaning',
        'Fresh Towels and Linen',
        'Toiletries Refill',
        'Extra Pillow or Blanket',
        'Turndown Service',
        'Other Housekeeping Help',
    ];

    /** The working order, as the panel's status sequence. Cancelled sits off to the side. */
    public const FLOW = ['Pending', 'Accepted', 'In Progress', 'Completed'];

    public const STATUSES = ['Pending', 'Accepted', 'In Progress', 'Completed', 'Cancelled'];

    /** Still Housekeeping's to do. */
    public const OPEN_STATUSES = ['Pending', 'Accepted', 'In Progress'];

    protected $primaryKey = 'hotel_service_request_id';

    protected $fillable = [
        'group_name',
        'faculty_id',
        'group_id',
        'hotel_booking_id',
        'room_name',
        'guest_name',
        'type',
        'preferred_time',
        'notes',
        'source',
        'status',
        'requested_by',
        'handled_by',
        'completion_notes',
        'hotel_complaint_id',
        'accepted_at',
        'started_at',
        'completed_at',
        'cancelled_at',
    ];

    protected $casts = [
        'accepted_at'  => 'datetime',
        'started_at'   => 'datetime',
        'completed_at' => 'datetime',
        'cancelled_at' => 'datetime',
    ];

    /** False until the migration has run, so a deploy that has not migrated yet keeps working. */
    public static function supported(): bool
    {
        static $has = null;

        return $has ??= Schema::hasTable('hotel_service_requests');
    }

    public static function normalizeType(?string $value): ?string
    {
        $raw = mb_strtolower(trim((string) $value));
        foreach (self::TYPES as $type) {
            if (mb_strtolower($type) === $raw) {
                return $type;
            }
        }

        return null;
    }

    public function booking()
    {
        return $this->belongsTo(HotelBooking::class, 'hotel_booking_id', 'hotel_booking_id');
    }

    public function isOpen(): bool
    {
        return in_array($this->status, self::OPEN_STATUSES, true);
    }

    /** Shape the staff screens read. */
    public function toTemplateArray(): array
    {
        return [
            'id'              => $this->hotel_service_request_id,
            'bookingId'       => $this->hotel_booking_id,
            'roomName'        => $this->room_name ?? '',
            'guestName'       => $this->guest_name ?? '',
            'type'            => $this->type,
            'preferredTime'   => $this->preferred_time ?? '',
            'notes'           => $this->notes ?? '',
            'source'          => $this->source,
            'status'          => $this->status,
            'requestedBy'     => $this->requested_by ?? '',
            'handledBy'       => $this->handled_by ?? '',
            'completionNotes' => $this->completion_notes ?? '',
            'complaintId'     => $this->hotel_complaint_id,
            'createdAt'       => optional($this->created_at)->toIso8601String(),
            'acceptedAt'      => optional($this->accepted_at)->toIso8601String(),
            'startedAt'       => optional($this->started_at)->toIso8601String(),
            'completedAt'     => optional($this->completed_at)->toIso8601String(),
            'cancelledAt'     => optional($this->cancelled_at)->toIso8601String(),
        ];
    }

    /** What the guest sees on My Stay: no staff names. */
    public function toGuestArray(): array
    {
        return array_intersect_key($this->toTemplateArray(), array_flip([
            'id', 'type', 'preferredTime', 'notes', 'status', 'createdAt', 'completedAt',
        ]));
    }
}
