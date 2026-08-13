<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * A person who has stayed, or is booked to stay, at the team's hotel.
 *
 * Separate from HotelCustomer on purpose: that one is a login for the public-facing
 * simulation site, this one is the guest record the Front Desk types in and can exist
 * without any account at all.
 */
class HotelGuest extends Model
{
    protected $primaryKey = 'hotel_guest_id';

    protected $fillable = [
        'group_name',
        'faculty_id',
        'group_id',
        'full_name',
        'contact_no',
        'email',
        'id_number',
    ];

    public function bookings(): HasMany
    {
        return $this->hasMany(HotelBooking::class, 'hotel_guest_id', 'hotel_guest_id');
    }

    /**
     * Reuses the guest who has already stayed here under the same name and contact
     * detail, so a repeat visitor does not accumulate one row per stay. Matching on
     * name alone would merge two different Marias; email/ID is what tells them apart.
     */
    public static function findOrCreateFor(array $membership, array $data): self
    {
        $fullName = trim((string) ($data['full_name'] ?? '')) ?: 'Guest';
        $email    = self::clean($data['email'] ?? null);
        $idNumber = self::clean($data['id_number'] ?? null);
        $contact  = self::clean($data['contact_no'] ?? null);

        $query = self::where('group_name', $membership['group_name'])
            ->where('faculty_id', $membership['faculty_id'])
            ->where('full_name', $fullName);

        if ($idNumber !== null) {
            $query->where('id_number', $idNumber);
        } elseif ($email !== null) {
            $query->where('email', $email);
        } elseif ($contact !== null) {
            $query->where('contact_no', $contact);
        }

        $guest = $query->first();

        if ($guest) {
            // A returning guest may have given a new number or address this time.
            $guest->fill(array_filter([
                'contact_no' => $contact,
                'email'      => $email,
                'id_number'  => $idNumber,
            ], fn ($value) => $value !== null))->save();

            return $guest;
        }

        return self::create([
            'group_name' => $membership['group_name'],
            'faculty_id' => $membership['faculty_id'],
            'group_id'   => $membership['group_id'] ?? null,
            'full_name'  => $fullName,
            'contact_no' => $contact,
            'email'      => $email,
            'id_number'  => $idNumber,
        ]);
    }

    private static function clean($value): ?string
    {
        $value = trim((string) $value);

        return $value === '' ? null : $value;
    }
}
