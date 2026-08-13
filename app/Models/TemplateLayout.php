<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TemplateLayout extends Model
{
    protected $primaryKey = 'template_layout_id';

    protected $fillable = [
        'team_role_template_id',
        'version_id',
        'sort_order',
        'section_id',
        'is_visible',
    ];

    protected $casts = [
        'is_visible' => 'boolean',
    ];
}
