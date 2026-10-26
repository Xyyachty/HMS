<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * What a dine-in customer paid before their table was closed out.
 *
 * The room-stay equivalent is HotelBookingPayment; this one hangs off a table
 * rather than a booking, because a walk-in diner has no stay to bill against. Every
 * figure is a snapshot taken when the bill was settled — see the migration for why.
 */
class HotelDineInPayment extends Model
{
    public const METHODS = ['Cash', 'GCash', 'Card', 'Other'];

    protected $primaryKey = 'hotel_dine_in_payment_id';

    protected $fillable = [
        'group_name',
        'faculty_id',
        'group_id',
        'hotel_dine_in_table_id',
        'table_name',
        'guest_name',
        'party_size',
        'total_due',
        'amount_paid',
        'balance',
        'method',
        'reference',
        'items',
        'order_ids',
        'collected_by',
        'paid_at',
    ];

    protected $casts = [
        'total_due'   => 'float',
        'amount_paid' => 'float',
        'balance'     => 'float',
        'party_size'  => 'integer',
        'items'       => 'array',
        'order_ids'   => 'array',
        'paid_at'     => 'datetime',
    ];

    public function table(): BelongsTo
    {
        return $this->belongsTo(HotelDineInTable::class, 'hotel_dine_in_table_id', 'hotel_dine_in_table_id');
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

    /** Shape the dine-in front-end reads. */
    public function toTemplateArray(): array
    {
        // "id" here is the front-end's key for a payment, not the column name.
        return [
            'id'         => $this->hotel_dine_in_payment_id,
            'tableId'    => $this->hotel_dine_in_table_id,
            'tableName'  => $this->table_name,
            'guestName'  => $this->guest_name ?? '',
            'partySize'  => $this->party_size,
            'totalDue'   => (float) $this->total_due,
            'amountPaid' => (float) $this->amount_paid,
            'balance'    => (float) $this->balance,
            'method'     => $this->method,
            'reference'  => $this->reference ?? '',
            'items'      => $this->items ?? [],
            'orderIds'   => $this->order_ids ?? [],
            'collectedBy' => $this->collected_by ?? '',
            'paidAt'     => optional($this->paid_at)->toIso8601String(),
        ];
    }
}
