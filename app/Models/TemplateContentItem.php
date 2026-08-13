<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TemplateContentItem extends Model
{
    protected $primaryKey = 'template_content_item_id';

    protected $fillable = [
        'team_role_template_id',
        'version_id',
        'collection',
        'sort_order',
        'item_ref',
        'parent_id',
    ];

    public function fields()
    {
        return $this->hasMany(TemplateContentField::class, 'template_content_item_id', 'template_content_item_id');
    }

    public function children()
    {
        return $this->hasMany(self::class, 'parent_id', 'template_content_item_id');
    }
}
