<?php

namespace App\Models;

use App\Support\HotelTemplateBuilder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GroupSettings extends Model
{
    use HasFactory;

    protected $fillable = [
        'group_name',
        'faculty_id',
        'group_id',
        'selected_template',
        'is_published',
    ];

    protected $casts = [
        'is_published' => 'boolean',
    ];

    /** Merged team customizations from normalized role templates (not a JSON column). */
    public function getCustomizationsAttribute(): array
    {
        return HotelTemplateBuilder::mergeTeamCustomizations(
            (string) $this->group_name,
            (int) $this->faculty_id
        );
    }
}
