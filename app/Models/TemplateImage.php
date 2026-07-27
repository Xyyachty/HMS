<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TemplateImage extends Model
{
    protected $fillable = [
        'team_role_template_id',
        'version_id',
        'element_key',
        'kind',
        'image_path',
    ];
}
