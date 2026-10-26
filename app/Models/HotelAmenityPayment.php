<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Money taken against a reservation — a down payment, a settlement, or the note that the
 * whole thing went onto a staying guest's room account instead.
 *
 * total_due and balance are snapshots of what was owed when the money changed hands, the
 * same call HotelBookingPayment makes. The live figures always come from the reservation;
 * these exist so a receipt keeps saying what it said on the day.
 */
class HotelAmenityPayment extends Model
{
    /**
     * 'Charged to Room' is not a payment in cash — it records that the amount moved onto
     * a checked-in guest's folio as hotel_booking_charges, so the reservation reads
     * settled without anyone pretending money crossed the desk.
     */
    public const TYPES = ['Full', 'Partial', 'Charged to Room'];

    public const METHODS = ['Cash', 'GCash', 'Card', 'Bank Transfer', 'Room Account', 'Other'];

    protected $primaryKey = 'hotel_amenity_payment_id';

    protected $fillable = [
        'group_name',
        'faculty_id',
        'group_id',
        'hotel_amenity_reservation_id',
        'type',
        'amount_paid',
        'total_due',
        'balance',
        'method',
        'reference',
        'payer_name',
        'notes',
        'collected_by',
        'paid_at',
    ];

    protected $casts = [
        'amount_paid' => 'float',
        'total_due'   => 'float',
        'balance'     => 'float',
        'paid_at'     => 'datetime',
    ];

    public function reservation(): BelongsTo
    {
        return $this->belongsTo(
            HotelAmenityReservation::class,
            'hotel_amenity_reservation_id',
            'hotel_amenity_reservation_id'
        );
    }

    public static function normalizeType(?string $value): string
    {
        $raw = mb_strtolower(trim((string) $value));

        foreach (self::TYPES as $type) {
            if (mb_strtolower($type) === $raw) {
                return $type;
            }
        }

        return 'Full';
    }

    public static function normalizeMethod(?string $value): string
    {
        $raw = mb_strtolower(trim((string) $value));

        foreach (self::METHODS as $method) {
            if (mb_strtolower($method) === $raw) {
                return $method;
            }
        }

        return 'Cash';
    }

    public function toTemplateArray(): array
    {
        // "id" here is the front-end's key for a payment, not the column name.
        return [
            'id'          => $this->hotel_amenity_payment_id,
            'type'        => $this->type,
            'amountPaid'  => (float) $this->amount_paid,
            'totalDue'    => (float) $this->total_due,
            'balance'     => (float) $this->balance,
            'method'      => $this->method,
            'reference'   => $this->reference ?? '',
            'payerName'   => $this->payer_name ?? '',
            'notes'       => $this->notes ?? '',
            'collectedBy' => $this->collected_by ?? '',
            'paidAt'      => optional($this->paid_at)->toIso8601String(),
        ];
    }
}
