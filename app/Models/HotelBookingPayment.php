<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Money taken against a booking. One row per payment, so a partial payment at
 * reservation and the balance settled at the desk are two records rather than one
 * overwritten total.
 */
class HotelBookingPayment extends Model
{
    public const TYPES = ['Full', 'Partial'];

    /**
     * What the desk can take. 'Other' is the catch-all the final-bill screen offers for
     * anything not on this list — the column is free text, so nothing is lost by it.
     */
    public const METHODS = ['Cash', 'GCash', 'Card', 'Bank Transfer', 'Other'];

    protected $primaryKey = 'hotel_booking_payment_id';

    protected $fillable = [
        'group_name',
        'faculty_id',
        'group_id',
        'hotel_booking_id',
        'type',
        'amount_paid',
        'total_due',
        'balance',
        'method',
        'reference',
        'payer_name',
        'notes',
        'paid_at',
    ];

    protected $casts = [
        'amount_paid' => 'float',
        'total_due'   => 'float',
        'balance'     => 'float',
        'paid_at'     => 'datetime',
    ];

    public function booking(): BelongsTo
    {
        return $this->belongsTo(HotelBooking::class, 'hotel_booking_id', 'hotel_booking_id');
    }

    public static function normalizeType(?string $value): string
    {
        return strtolower(trim((string) $value)) === 'partial' ? 'Partial' : 'Full';
    }

    /** Shape the booking/room front-end reads — same keys the old payment blob used. */
    public function toTemplateArray(): array
    {
        return [
            'id'         => $this->hotel_booking_payment_id,
            'type'       => $this->type,
            'amountPaid' => (float) $this->amount_paid,
            'totalDue'   => (float) $this->total_due,
            'balance'    => (float) $this->balance,
            'method'     => $this->method,
            'reference'  => $this->reference ?? '',
            'payerName'  => $this->payer_name ?? '',
            'notes'      => $this->notes ?? '',
            'paidAt'     => optional($this->paid_at)->toIso8601String(),
        ];
    }
}
