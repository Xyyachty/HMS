<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TemplateContentField extends Model
{
    protected $primaryKey = 'template_content_field_id';

    protected $fillable = [
        'template_content_item_id',
        'field_name',
        'field_value',
    ];
}
