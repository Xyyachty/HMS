<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TeamRoleTemplateVersion extends Model
{
    protected $fillable = [
        'team_role_template_id',
        'version',
        'selected_template',
        'customizations',
        'layout',
        'is_published',
        'label',
        'created_by',
    ];

    protected $casts = [
        'customizations' => 'array',
        'layout' => 'array',
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
}
