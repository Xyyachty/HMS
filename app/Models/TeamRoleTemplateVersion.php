<?php

namespace App\Models;

use App\Support\TemplateCustomizationStore;
use Illuminate\Database\Eloquent\Model;

class TeamRoleTemplateVersion extends Model
{
    protected $fillable = [
        'team_role_template_id',
        'version',
        'selected_template',
        'is_published',
        'label',
        'created_by',
    ];

    protected $casts = [
        'is_published' => 'boolean',
    ];

    public function template()
    {
        return $this->belongsTo(TeamRoleTemplate::class, 'team_role_template_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function getCustomizationsAttribute(): array
    {
        if (!$this->team_role_template_id || !$this->id) {
            return [];
        }

        return TemplateCustomizationStore::readCustomizations((int) $this->team_role_template_id, (int) $this->id);
    }

    public function getLayoutAttribute(): array
    {
        if (!$this->team_role_template_id || !$this->id) {
            return \App\Support\HotelTemplateBuilder::defaultLayout();
        }

        return TemplateCustomizationStore::readLayout((int) $this->team_role_template_id, (int) $this->id);
    }
}
