<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * The hotel concept a Front Desk team proposes — one row per team.
 *
 * Writes go through HotelConceptController, which records a revision on every
 * save; nothing else should update this table directly, or the history lies.
 */
class HotelConcept extends Model
{
    protected $primaryKey = 'hotel_concept_id';

    protected $fillable = [
        'group_name',
        'faculty_id',
        'group_id',
        'title',
        'description',
        'hotel_type',
        'created_by',
        'updated_by',
    ];

    /** The dropdown on the student form, and the only accepted values. */
    public const HOTEL_TYPES = [
        'resort' => 'Resort',
        'business' => 'Business Hotel',
        'boutique' => 'Boutique Hotel',
        'luxury' => 'Luxury Hotel',
        'budget' => 'Budget / Economy Hotel',
        'inn' => 'Inn / Lodge',
        'hostel' => 'Hostel',
        'apartment' => 'Apartment / Serviced Residence',
        'casino' => 'Casino Hotel',
        'eco' => 'Eco / Green Hotel',
    ];

    /** Fields the edit history reports on, with the labels it prints. */
    public const TRACKED_FIELDS = [
        'title' => 'Title',
        'description' => 'Description',
        'hotel_type' => 'Hotel Type',
    ];

    public function group()
    {
        return $this->belongsTo(Group::class, 'group_id', 'group_id');
    }

    public function faculty()
    {
        return $this->belongsTo(Faculty::class, 'faculty_id', 'faculty_id');
    }

    public function revisions()
    {
        return $this->hasMany(HotelConceptRevision::class, 'hotel_concept_id', 'hotel_concept_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by', 'user_id');
    }

    public function editor()
    {
        return $this->belongsTo(User::class, 'updated_by', 'user_id');
    }

    public static function typeLabel(?string $type): string
    {
        return self::HOTEL_TYPES[(string) $type] ?? ((string) $type !== '' ? ucwords(str_replace('_', ' ', (string) $type)) : '—');
    }

    public function getHotelTypeLabelAttribute(): string
    {
        return self::typeLabel($this->hotel_type);
    }
}
