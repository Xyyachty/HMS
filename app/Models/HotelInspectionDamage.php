<?php

namespace App\Models;

use App\Support\HotelImageStore;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * One broken or missing item Housekeeping found while inspecting a room before the
 * guest's check-out. It is a report, not a charge: the guest pays for it only once
 * Front Desk reviews it and sets it Charged, which adds a HotelBookingCharge.
 */
class HotelInspectionDamage extends Model
{
    public const STATUSES = ['Pending Review', 'Charged', 'Waived'];

    protected $primaryKey = 'hotel_inspection_damage_id';

    protected $fillable = [
        'group_name',
        'faculty_id',
        'group_id',
        'hotel_room_inspection_id',
        'hotel_booking_id',
        'item',
        'description',
        'evidence',
        'status',
        'amount',
        'hotel_booking_charge_id',
        'reported_by',
        'reviewed_by',
        'reviewed_at',
    ];

    protected $casts = [
        'amount'      => 'float',
        'reviewed_at' => 'datetime',
    ];

    public function inspection(): BelongsTo
    {
        return $this->belongsTo(HotelRoomInspection::class, 'hotel_room_inspection_id', 'hotel_room_inspection_id');
    }

    public function booking(): BelongsTo
    {
        return $this->belongsTo(HotelBooking::class, 'hotel_booking_id', 'hotel_booking_id');
    }

    public function toTemplateArray(): array
    {
        return [
            'id'          => $this->hotel_inspection_damage_id,
            'item'        => $this->item,
            'description' => $this->description ?? '',
            'evidence'    => $this->evidence ? HotelImageStore::url($this->evidence) : '',
            'status'      => $this->status,
            'amount'      => $this->amount,
            'chargeId'    => $this->hotel_booking_charge_id,
            'reportedBy'  => $this->reported_by ?? '',
            'reviewedBy'  => $this->reviewed_by ?? '',
            'reviewedAt'  => optional($this->reviewed_at)->toIso8601String(),
        ];
    }
}
