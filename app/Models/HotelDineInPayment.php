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
        'party_ref',
        'subtotal',
        'discounts',
        'discount_total',
        'service_charge_rate',
        'service_charge',
        'splits',
    ];

    /** False until the split-payment migration has run. */
    public static function splitsSupported(): bool
    {
        static $has = null;

        return $has ??= \Illuminate\Support\Facades\Schema::hasColumn('hotel_dine_in_payments', 'splits');
    }

    /** False until the discounts / service charge migration has run. */
    public static function pricingSupported(): bool
    {
        static $has = null;

        return $has ??= \Illuminate\Support\Facades\Schema::hasColumn('hotel_dine_in_payments', 'service_charge');
    }

    protected $casts = [
        'subtotal'            => 'float',
        'discounts'           => 'array',
        'discount_total'      => 'float',
        'service_charge_rate' => 'float',
        'service_charge'      => 'float',
        'splits'              => 'array',
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
            // Set when one payment settled several tables reserved together.
            'partyRef'   => $this->party_ref ?? null,
            // Null on payments from before discounts and service charge: their
            // total_due was the food alone.
            'subtotal'          => $this->subtotal ?? (float) $this->total_due,
            'discounts'         => $this->discounts ?? [],
            'discountTotal'     => (float) ($this->discount_total ?? 0),
            'serviceChargeRate' => (float) ($this->service_charge_rate ?? 0),
            'serviceCharge'     => (float) ($this->service_charge ?? 0),
            // Each part of a bill paid more than one way; one part otherwise.
            'splits'            => $this->splits ?: [[
                'method' => $this->method, 'amount' => (float) $this->amount_paid, 'reference' => $this->reference ?? '',
            ]],
            'paidAt'     => optional($this->paid_at)->toIso8601String(),
        ];
    }
}
