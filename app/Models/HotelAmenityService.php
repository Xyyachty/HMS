<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * One bookable treatment at a by-appointment facility — a Swedish massage, a foot spa.
 *
 * duration_minutes is what turns a start time into a slot: the desk picks when it begins
 * and the end is computed, which is what the overlap check compares.
 */
class HotelAmenityService extends Model
{
    protected $primaryKey = 'hotel_amenity_service_id';

    protected $fillable = [
        'group_name',
        'faculty_id',
        'group_id',
        'hotel_amenity_id',
        'name',
        'description',
        'duration_minutes',
        'price',
        'is_active',
    ];

    protected $casts = [
        'duration_minutes' => 'integer',
        'price'            => 'integer',
        'is_active'        => 'boolean',
    ];

    public function amenity(): BelongsTo
    {
        return $this->belongsTo(HotelAmenity::class, 'hotel_amenity_id', 'hotel_amenity_id');
    }

    /** Retired services stay in the table so booked appointments still resolve. */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /** "1h 30m" — how the picker prints a duration. */
    public function durationLabel(): string
    {
        $minutes = max(0, (int) $this->duration_minutes);

        if ($minutes < 60) {
            return $minutes . 'm';
        }

        $hours = intdiv($minutes, 60);
        $rest = $minutes % 60;

        return $rest === 0 ? $hours . 'h' : $hours . 'h ' . $rest . 'm';
    }

    public function toTemplateArray(): array
    {
        // "id" here is the front-end's key for a service, not the column name.
        return [
            'id'          => $this->hotel_amenity_service_id,
            'amenityId'   => $this->hotel_amenity_id,
            'name'        => $this->name,
            'description' => $this->description ?? '',
            'minutes'     => (int) $this->duration_minutes,
            'duration'    => $this->durationLabel(),
            'price'       => (int) $this->price,
            'isActive'    => (bool) $this->is_active,
        ];
    }
}
