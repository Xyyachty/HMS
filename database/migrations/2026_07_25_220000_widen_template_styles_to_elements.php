<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /** CSS / editor props stored as real columns (kebab → snake). */
    private array $styleColumns = [
        'color', 'background_color', 'font_family', 'font_weight', 'font_style',
        'text_decoration', 'font_size', 'text_align', 'line_height', 'letter_spacing',
        'background_size', 'background_position', 'background_repeat',
        'padding', 'padding_top', 'padding_right', 'padding_bottom', 'padding_left',
        'margin', 'margin_top', 'margin_right', 'margin_bottom', 'margin_left',
        'border', 'border_radius', 'box_shadow', 'opacity',
        'position', 'top', 'left_pos', 'right_pos', 'bottom_pos',
        'width', 'height', 'max_width', 'min_width', 'min_height',
        'z_index', 'transform', 'display', 'overflow',
    ];

    public function up(): void
    {
        Schema::create('template_elements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('team_role_template_id')->constrained('team_role_templates')->cascadeOnDelete();
            $table->unsignedBigInteger('version_id')->nullable()->index();
            $table->text('element_key');

            // Editor meta (readable columns)
            $table->string('hms_id', 120)->nullable();
            $table->string('page', 64)->nullable();
            $table->boolean('free_position')->default(false);
            $table->string('move_mode', 40)->nullable();
            $table->boolean('keep_fixed')->default(false);
            $table->text('text_content')->nullable();
            $table->string('icon_class', 255)->nullable();
            $table->string('display_value', 255)->nullable();

            // Images as paths (not base64)
            $table->string('image_src', 500)->nullable();
            $table->string('image_background', 500)->nullable();

            // Common CSS as real columns
            $table->string('color', 120)->nullable();
            $table->string('background_color', 120)->nullable();
            $table->string('font_family', 255)->nullable();
            $table->string('font_weight', 40)->nullable();
            $table->string('font_style', 40)->nullable();
            $table->string('text_decoration', 80)->nullable();
            $table->string('font_size', 40)->nullable();
            $table->string('text_align', 40)->nullable();
            $table->string('line_height', 40)->nullable();
            $table->string('letter_spacing', 40)->nullable();
            $table->string('background_size', 80)->nullable();
            $table->string('background_position', 80)->nullable();
            $table->string('background_repeat', 40)->nullable();
            $table->string('padding', 80)->nullable();
            $table->string('padding_top', 40)->nullable();
            $table->string('padding_right', 40)->nullable();
            $table->string('padding_bottom', 40)->nullable();
            $table->string('padding_left', 40)->nullable();
            $table->string('margin', 80)->nullable();
            $table->string('margin_top', 40)->nullable();
            $table->string('margin_right', 40)->nullable();
            $table->string('margin_bottom', 40)->nullable();
            $table->string('margin_left', 40)->nullable();
            $table->string('border', 120)->nullable();
            $table->string('border_radius', 80)->nullable();
            $table->string('box_shadow', 255)->nullable();
            $table->string('opacity', 20)->nullable();
            $table->string('position', 40)->nullable();
            $table->string('top', 40)->nullable();
            $table->string('left_pos', 40)->nullable(); // "left" is reserved-ish; use left_pos
            $table->string('right_pos', 40)->nullable();
            $table->string('bottom_pos', 40)->nullable();
            $table->string('width', 40)->nullable();
            $table->string('height', 40)->nullable();
            $table->string('max_width', 40)->nullable();
            $table->string('min_width', 40)->nullable();
            $table->string('min_height', 40)->nullable();
            $table->string('z_index', 20)->nullable();
            $table->string('transform', 255)->nullable();
            $table->string('display', 40)->nullable();
            $table->string('overflow', 40)->nullable();

            $table->timestamps();
            $table->index(['team_role_template_id', 'version_id'], 'tpl_elements_template_version_idx');
        });

        $this->migrateFromEav();

        Schema::dropIfExists('template_styles');

        // Element images now live on template_elements; keep content images only
        if (Schema::hasTable('template_images')) {
            DB::table('template_images')->whereIn('kind', ['src', 'background-image'])->delete();
        }
    }

    private function migrateFromEav(): void
    {
        if (!Schema::hasTable('template_styles')) {
            return;
        }

        $now = now();
        $groups = DB::table('template_styles')
            ->orderBy('id')
            ->get()
            ->groupBy(fn ($row) => $row->team_role_template_id . '|' . ($row->version_id ?? 'live') . '|' . $row->element_key);

        foreach ($groups as $rows) {
            $first = $rows->first();
            $payload = [
                'team_role_template_id' => $first->team_role_template_id,
                'version_id' => $first->version_id,
                'element_key' => $first->element_key,
                'hms_id' => null,
                'page' => null,
                'free_position' => false,
                'move_mode' => null,
                'keep_fixed' => false,
                'text_content' => null,
                'icon_class' => null,
                'display_value' => null,
                'image_src' => null,
                'image_background' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ];

            foreach ($this->styleColumns as $col) {
                $payload[$col] = null;
            }

            foreach ($rows as $row) {
                $prop = (string) $row->property;
                $val = $row->value;

                if ($prop === 'hmsId') {
                    $payload['hms_id'] = $val;
                } elseif ($prop === 'page') {
                    $payload['page'] = $val;
                } elseif ($prop === 'freePosition') {
                    $payload['free_position'] = in_array((string) $val, ['1', 'true', 'yes'], true);
                } elseif ($prop === 'moveMode') {
                    $payload['move_mode'] = $val;
                } elseif ($prop === 'keepFixed') {
                    $payload['keep_fixed'] = in_array((string) $val, ['1', 'true', 'yes'], true);
                } elseif ($prop === 'text') {
                    $payload['text_content'] = $val;
                } elseif ($prop === 'iconClass') {
                    $payload['icon_class'] = $val;
                } elseif ($prop === 'value') {
                    $payload['display_value'] = $val;
                } elseif ($prop === 'left') {
                    $payload['left_pos'] = $val;
                } elseif ($prop === 'right') {
                    $payload['right_pos'] = $val;
                } elseif ($prop === 'bottom') {
                    $payload['bottom_pos'] = $val;
                } else {
                    $col = str_replace('-', '_', $prop);
                    if (array_key_exists($col, $payload)) {
                        $payload[$col] = $val;
                    }
                }
            }

            // Attach element images if present
            $images = DB::table('template_images')
                ->where('team_role_template_id', $first->team_role_template_id)
                ->where('element_key', $first->element_key)
                ->where(function ($q) use ($first) {
                    if ($first->version_id === null) {
                        $q->whereNull('version_id');
                    } else {
                        $q->where('version_id', $first->version_id);
                    }
                })
                ->whereIn('kind', ['src', 'background-image'])
                ->get();

            foreach ($images as $img) {
                if ($img->kind === 'src') {
                    $payload['image_src'] = $img->image_path;
                } else {
                    $payload['image_background'] = $img->image_path;
                }
            }

            DB::table('template_elements')->insert($payload);
        }

        // Elements that only had images (no styles)
        $styledKeys = DB::table('template_elements')->get()->map(fn ($r) => $r->team_role_template_id . '|' . ($r->version_id ?? 'live') . '|' . $r->element_key)->all();

        $onlyImages = DB::table('template_images')
            ->whereIn('kind', ['src', 'background-image'])
            ->get()
            ->groupBy(fn ($row) => $row->team_role_template_id . '|' . ($row->version_id ?? 'live') . '|' . $row->element_key);

        foreach ($onlyImages as $key => $imgs) {
            if (in_array($key, $styledKeys, true)) {
                continue;
            }
            $first = $imgs->first();
            $payload = [
                'team_role_template_id' => $first->team_role_template_id,
                'version_id' => $first->version_id,
                'element_key' => $first->element_key,
                'hms_id' => null,
                'page' => null,
                'free_position' => false,
                'move_mode' => null,
                'keep_fixed' => false,
                'text_content' => null,
                'icon_class' => null,
                'display_value' => null,
                'image_src' => null,
                'image_background' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ];
            foreach ($this->styleColumns as $col) {
                $payload[$col] = null;
            }
            foreach ($imgs as $img) {
                if ($img->kind === 'src') {
                    $payload['image_src'] = $img->image_path;
                } else {
                    $payload['image_background'] = $img->image_path;
                }
            }
            DB::table('template_elements')->insert($payload);
        }
    }

    public function down(): void
    {
        Schema::create('template_styles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('team_role_template_id')->constrained('team_role_templates')->cascadeOnDelete();
            $table->unsignedBigInteger('version_id')->nullable()->index();
            $table->text('element_key');
            $table->string('property', 120);
            $table->longText('value')->nullable();
            $table->timestamps();
        });

        // Best-effort reverse is omitted (wide → EAV can be rebuilt from app if needed)
        Schema::dropIfExists('template_elements');
    }
};
