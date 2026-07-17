<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GroupSettings extends Model
{
    use HasFactory;

    protected $fillable = [
        'group_name',
        'faculty_id',
        'selected_template',
        'customizations',
        'is_published',
    ];

    protected $casts = [
        'customizations' => 'array',
        'is_published' => 'boolean',
    ];
}
