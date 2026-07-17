<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TeamRoleTemplate extends Model
{
    protected $fillable = [
        'group_name',
        'faculty_id',
        'role',
        'selected_template',
        'customizations',
        'layout',
        'is_published',
        'version',
        'updated_by',
    ];

    protected $casts = [
        'customizations' => 'array',
        'layout' => 'array',
        'is_published' => 'boolean',
    ];

    public function versions()
    {
        return $this->hasMany(TeamRoleTemplateVersion::class)->orderByDesc('version');
    }

    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
