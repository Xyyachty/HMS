<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TemplateContentField extends Model
{
    protected $fillable = [
        'content_item_id',
        'field_name',
        'field_value',
    ];
}
