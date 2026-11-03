<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * One Senior Citizen or PWD on a stay, and the statutory discount their share earns.
 *
 * The rule (RA 9994 and RA 10754, BIR RR 7-2010 Sec. 7): a sale to a Senior Citizen or
 * PWD is VAT-exempt, so the 12% VAT comes off their share first, and the 20% discount
 * is then taken from that VAT-exempt price. Only the portion the person "exclusively
 * used, consumed or enjoyed" qualifies, which is why a companion's discount is worked
 * out on their own share of the room and never on the whole booking.
 *
 * Nothing is taken off until Front Desk has verified the person's ID (verified_at).
 */
class HotelBookingDiscount extends Model
{
    public const TYPES = ['Senior Citizen', 'PWD'];

    /** Minimum age for the Senior Citizen discount (RA 9994 Sec. 3). */
    public const SENIOR_AGE = 60;

    public const DISCOUNT_RATE = 0.20;

    /*
     * Room prices in HMS are quoted VAT-inclusive, as Philippine consumer prices are.
     * ponytail: one fixed rate for every team; a per-hotel "not VAT-registered" switch
     * (discount on the full price, no VAT line) when a scenario needs it.
     */
    public const VAT_RATE = 0.12;

    protected $primaryKey = 'hotel_booking_discount_id';

    protected $fillable = [
        'group_name',
        'faculty_id',
        'group_id',
        'hotel_booking_id',
        'type',
        'occupant',
        'child_index',
        'is_primary',
        'full_name',
        'birth_date',
        'id_number',
        'id_hash',
        'verified_at',
        'verified_by',
        'eligible_amount',
        'vat_exempt_amount',
        'discount_amount',
    ];

    protected $casts = [
        'child_index'       => 'integer',
        'is_primary'        => 'boolean',
        'birth_date'        => 'date',
        // Government ID numbers are personal data: stored encrypted, shown masked.
        'id_number'         => 'encrypted',
        'verified_at'       => 'datetime',
        'eligible_amount'   => 'float',
        'vat_exempt_amount' => 'float',
        'discount_amount'   => 'float',
    ];

    protected $hidden = ['id_number', 'id_hash'];

    /** Whether the table exists yet, so a deploy whose migration has not run still serves bookings. */
    public static function supported(): bool
    {
        static $has = null;

        return $has ??= \Illuminate\Support\Facades\Schema::hasTable('hotel_booking_discounts');
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

    public static function hashId(string $type, string $idNumber): string
    {
        return hash('sha256', $type . '|' . mb_strtoupper(preg_replace('/[\s-]+/', '', $idNumber)));
    }

    /**
     * The statutory computation on one person's VAT-inclusive share.
     *
     *   share ÷ 1.12          = VAT-exempt price   (the VAT removed is share − that)
     *   20% of the VAT-exempt = discount
     *   VAT-exempt − discount = what the person's share costs
     *
     * Each step is rounded to the centavo, so the lines on the bill add up exactly.
     *
     * @return array{eligible: float, vatExemptPrice: float, vat: float, discount: float, payable: float, less: float}
     */
    public static function compute(float $share): array
    {
        $eligible = round(max(0, $share), 2);
        $vatExemptPrice = round($eligible / (1 + self::VAT_RATE), 2);
        $vat = round($eligible - $vatExemptPrice, 2);
        $discount = round($vatExemptPrice * self::DISCOUNT_RATE, 2);

        return [
            'eligible'       => $eligible,
            'vatExemptPrice' => $vatExemptPrice,
            'vat'            => $vat,
            'discount'       => $discount,
            'payable'        => round($vatExemptPrice - $discount, 2),
            'less'           => round($vat + $discount, 2),
        ];
    }

    public function booking(): BelongsTo
    {
        return $this->belongsTo(HotelBooking::class, 'hotel_booking_id', 'hotel_booking_id');
    }

    public function isVerified(): bool
    {
        return $this->verified_at !== null;
    }

    /** "•••• 4821": enough for the desk to match the card, not enough to copy it. */
    public function maskedIdNumber(): string
    {
        $id = (string) $this->id_number;

        return mb_strlen($id) <= 4 ? '••••' : '•••• ' . mb_substr($id, -4);
    }

    public function occupantLabel(): string
    {
        return $this->is_primary ? 'Primary guest' : ($this->occupant === 'child' ? 'Child' : 'Companion');
    }

    /** What the screens read, priced live by the booking it belongs to. */
    public function toTemplateArray(HotelBooking $booking): array
    {
        $money = self::compute($booking->discountShare($this));

        return [
            'id'          => $this->hotel_booking_discount_id,
            'type'        => $this->type,
            'occupant'    => $this->occupant,
            'who'         => $this->occupantLabel(),
            'isPrimary'   => (bool) $this->is_primary,
            'fullName'    => $this->full_name,
            'birthDate'   => optional($this->birth_date)->toDateString(),
            'idNumber'    => $this->maskedIdNumber(),
            'verified'    => $this->isVerified(),
            'status'      => $this->isVerified() ? 'Verified' : 'Pending Verification',
            'verifiedBy'  => $this->verified_by ?? '',
            'verifiedAt'  => optional($this->verified_at)->toIso8601String(),
        ] + $money;
    }
}
