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

    /** The details that tell two guests with the same name apart. */
    public const IDENTIFIERS = ['id_number', 'email', 'contact_no'];

    /**
     * Reuses the guest who has already stayed here under the same name and contact
     * detail, so a repeat visitor does not accumulate one row per stay. Matching on
     * name alone would merge two different Marias; email/ID is what tells them apart.
     *
     * The name is compared without case or stray spaces, and any one shared detail is
     * enough: a guest who booked online with an email and shows ID at the desk is still
     * the same person. A different ID number never is.
     */
    public static function findOrCreateFor(array $membership, array $data): self
    {
        $fullName = trim((string) ($data['full_name'] ?? '')) ?: 'Guest';
        $email    = self::clean($data['email'] ?? null);
        $idNumber = self::clean($data['id_number'] ?? null);
        $contact  = self::clean($data['contact_no'] ?? null);
        $given    = ['id_number' => $idNumber, 'email' => $email, 'contact_no' => $contact];

        $candidates = self::where('group_name', $membership['group_name'])
            ->where('faculty_id', $membership['faculty_id'])
            ->whereRaw('LOWER(TRIM(full_name)) = ?', [mb_strtolower($fullName)])
            ->orderBy('hotel_guest_id')
            ->get();

        // A shared detail first; else a record with no details on file, which nothing
        // could tell apart from this guest; else, when this guest gave none either, the
        // name is all there is to go by.
        $guest = $candidates->first(fn (self $c) => self::sharesDetail($c->only(self::IDENTIFIERS), $given))
            ?? $candidates->first(fn (self $c) => !self::hasDetails($c->only(self::IDENTIFIERS)))
            ?? (self::hasDetails($given) ? null : $candidates->first());

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

    /** Same ID, email or phone, case aside — and no two different ID numbers. */
    public static function sharesDetail(array $a, array $b): bool
    {
        $norm = fn ($value) => ($value = mb_strtolower(trim((string) $value))) === '' ? null : $value;

        if ($norm($a['id_number'] ?? null) && $norm($b['id_number'] ?? null)
            && $norm($a['id_number']) !== $norm($b['id_number'])) {
            return false;
        }

        foreach (self::IDENTIFIERS as $field) {
            if ($norm($a[$field] ?? null) !== null && $norm($a[$field] ?? null) === $norm($b[$field] ?? null)) {
                return true;
            }
        }

        return false;
    }

    public static function hasDetails(array $details): bool
    {
        foreach (self::IDENTIFIERS as $field) {
            if (trim((string) ($details[$field] ?? '')) !== '') {
                return true;
            }
        }

        return false;
    }

    private static function clean($value): ?string
    {
        $value = trim((string) $value);

        return $value === '' ? null : $value;
    }
}
