<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * A hotel facility Housekeeping looks after — the pool, the gym, the spa.
 *
 * Not a hotel_addon: an add-on is lent to one guest and billed to their stay, while a
 * facility is open to everyone and has posted hours instead of a quantity. Not a room's
 * amenity chips either — those are website copy on a room card.
 *
 * status is the only status stored. When one of these breaks, the repair is a
 * hotel_complaints row pointing back here; how far Maintenance has got is read off that
 * complaint rather than mirrored into a fourth value on this row. See toTemplateArray().
 */
class HotelAmenity extends Model
{
    /**
     * Available means guests can use it. Temporarily Closed is Housekeeping's own call —
     * the pool is drained today, the function room is booked out. Under Maintenance means
     * something is broken, and it is the only status a repair request can be filed from.
     */
    public const STATUSES = ['Available', 'Temporarily Closed', 'Under Maintenance'];

    /** A repair Maintenance is still holding. Anything else is back on Housekeeping's desk. */
    public const OPEN_REPAIR_STATUSES = ['Open', 'In Progress'];

    protected $primaryKey = 'hotel_amenity_id';

    protected $fillable = [
        'group_name',
        'faculty_id',
        'group_id',
        'name',
        'description',
        'location',
        'opens_at',
        'closes_at',
        'status',
        'image',
    ];

    /**
     * Every repair ever requested for this amenity, newest first, so first() is the one
     * that decides what the screens show.
     */
    public function repairs(): HasMany
    {
        return $this->hasMany(HotelComplaint::class, 'hotel_amenity_id', 'hotel_amenity_id')
            ->orderByDesc('hotel_complaint_id');
    }

    public static function normalizeStatus(?string $value): string
    {
        $raw = mb_strtolower(trim((string) $value));

        foreach (self::STATUSES as $status) {
            if (mb_strtolower($status) === $raw) {
                return $status;
            }
        }

        return 'Available';
    }

    /** 'HH:MM' or null. Anything unparseable is dropped rather than stored half-formed. */
    public static function normalizeTime(?string $value): ?string
    {
        $raw = trim((string) $value);

        if (!preg_match('/^([01]\d|2[0-3]):([0-5]\d)$/', $raw)) {
            return null;
        }

        return $raw;
    }

    /** "6:00 AM" — the shape the public site prints, built once here so both skins agree. */
    public static function formatTime(?string $value): ?string
    {
        $value = self::normalizeTime($value);
        if ($value === null) {
            return null;
        }

        [$hour, $minute] = array_map('intval', explode(':', $value));
        $suffix = $hour < 12 ? 'AM' : 'PM';
        $hour12 = $hour % 12 === 0 ? 12 : $hour % 12;

        return $hour12 . ':' . str_pad((string) $minute, 2, '0', STR_PAD_LEFT) . ' ' . $suffix;
    }

    /**
     * Shape sent to the Housekeeping screen and to the hotel template front-end.
     *
     * $repair — the newest complaint filed against this amenity — is passed in rather than
     * queried here. The screens that render this list ask for every row's complaint at once
     * (one query in HotelAmenityDesk::latestRepairsFor), so doing it per row would be an N+1,
     * the same call HotelAddon::toTemplateArray() makes about reserved counts.
     */
    public function toTemplateArray(?HotelComplaint $repair = null): array
    {
        // "id"/"dbId" are the front-end's keys for a catalogue row, not column names.
        $repairStatus = $repair?->status;
        $underMaintenance = $this->status === 'Under Maintenance';
        $openRepair = $repairStatus !== null && in_array($repairStatus, self::OPEN_REPAIR_STATUSES, true);

        $opens = self::formatTime($this->opens_at);
        $closes = self::formatTime($this->closes_at);

        return [
            'id'          => 'db-' . $this->hotel_amenity_id,
            'dbId'        => $this->hotel_amenity_id,
            'name'        => $this->name,
            'description' => $this->description ?? '',
            'location'    => $this->location ?? '',
            'opensAt'     => self::normalizeTime($this->opens_at),
            'closesAt'    => self::normalizeTime($this->closes_at),
            // Pre-joined so neither template skin has to reimplement the dash and the
            // "Open 24 hours" fallback in JSX.
            'hours'       => $opens && $closes ? $opens . ' – ' . $closes : ($opens ?: ($closes ? 'Until ' . $closes : '')),
            'status'      => $this->status,
            'img'         => \App\Support\HotelImageStore::url($this->image),

            // Derived, never stored — see the class docblock.
            'repairId'          => $repair?->hotel_complaint_id,
            'repairStatus'      => $repairStatus,
            'repairCategory'    => $repair?->category,
            'repairDetails'     => $repair?->details ?? '',
            'repairNote'        => $repair?->resolution_note ?? '',
            'repairHandledBy'   => $repair?->handled_by ?? '',
            // Maintenance still has it. Housekeeping can look but not act.
            'repairInProgress'  => $underMaintenance && $openRepair,
            // Maintenance finished. Housekeeping's move: check it and reopen the amenity.
            'awaitingVerification' => $underMaintenance && $repairStatus !== null && !$openRepair,
            // Broken, but nobody has told Maintenance yet.
            'canRequestRepair'  => $underMaintenance && !$openRepair,
        ];
    }
}
