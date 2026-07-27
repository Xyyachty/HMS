<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TemplateElement extends Model
{
    protected $fillable = [
        'team_role_template_id',
        'version_id',
        'element_key',
        'hms_id',
        'page',
        'free_position',
        'move_mode',
        'keep_fixed',
        'text_content',
        'icon_class',
        'display_value',
        'image_src',
        'image_background',
        'color',
        'background_color',
        'font_family',
        'font_weight',
        'font_style',
        'text_decoration',
        'font_size',
        'text_align',
        'line_height',
        'letter_spacing',
        'background_size',
        'background_position',
        'background_repeat',
        'padding',
        'padding_top',
        'padding_right',
        'padding_bottom',
        'padding_left',
        'margin',
        'margin_top',
        'margin_right',
        'margin_bottom',
        'margin_left',
        'border',
        'border_radius',
        'box_shadow',
        'opacity',
        'position',
        'top',
        'left_pos',
        'right_pos',
        'bottom_pos',
        'width',
        'height',
        'max_width',
        'min_width',
        'min_height',
        'z_index',
        'transform',
        'display',
        'overflow',
    ];

    protected $casts = [
        'free_position' => 'boolean',
        'keep_fixed' => 'boolean',
    ];
}
