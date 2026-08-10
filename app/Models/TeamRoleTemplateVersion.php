<?php

namespace App\Models;

use App\Support\TemplateCustomizationStore;
use Illuminate\Database\Eloquent\Model;

class TeamRoleTemplateVersion extends Model
{
    protected $primaryKey = 'team_role_template_version_id';

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
        return $this->belongsTo(TeamRoleTemplate::class, 'team_role_template_id', 'team_role_template_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by', 'user_id');
    }

    public function getCustomizationsAttribute(): array
    {
        if (!$this->team_role_template_id || !$this->team_role_template_version_id) {
            return [];
        }

        return TemplateCustomizationStore::readCustomizations(
            (int) $this->team_role_template_id,
            (int) $this->team_role_template_version_id
        );
    }

    public function getLayoutAttribute(): array
    {
        if (!$this->team_role_template_id || !$this->team_role_template_version_id) {
            return \App\Support\HotelTemplateBuilder::defaultLayout();
        }

        return TemplateCustomizationStore::readLayout(
            (int) $this->team_role_template_id,
            (int) $this->team_role_template_version_id
        );
    }
}
