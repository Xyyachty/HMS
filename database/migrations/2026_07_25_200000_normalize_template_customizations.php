<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Clean partial failure from a previous attempt
        Schema::dropIfExists('template_content_fields');
        Schema::dropIfExists('template_content_items');
        Schema::dropIfExists('template_images');
        Schema::dropIfExists('template_styles');
        Schema::dropIfExists('template_layouts');

        Schema::create('template_layouts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('team_role_template_id')->constrained('team_role_templates')->cascadeOnDelete();
            $table->unsignedBigInteger('version_id')->nullable()->index();
            $table->unsignedInteger('sort_order')->default(0);
            $table->string('section_id', 120);
            $table->boolean('is_visible')->default(true);
            $table->timestamps();

            $table->index(['team_role_template_id', 'version_id'], 'tpl_layouts_template_version_idx');
        });

        Schema::create('template_styles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('team_role_template_id')->constrained('team_role_templates')->cascadeOnDelete();
            $table->unsignedBigInteger('version_id')->nullable()->index();
            $table->text('element_key');
            $table->string('property', 120);
            $table->longText('value')->nullable();
            $table->timestamps();

            $table->index(['team_role_template_id', 'version_id'], 'tpl_styles_template_version_idx');
        });

        Schema::create('template_images', function (Blueprint $table) {
            $table->id();
            $table->foreignId('team_role_template_id')->constrained('team_role_templates')->cascadeOnDelete();
            $table->unsignedBigInteger('version_id')->nullable()->index();
            $table->text('element_key');
            $table->string('kind', 40)->default('src'); // src | background-image | content
            $table->string('image_path', 500);
            $table->timestamps();

            $table->index(['team_role_template_id', 'version_id'], 'tpl_images_template_version_idx');
        });

        Schema::create('template_content_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('team_role_template_id')->constrained('team_role_templates')->cascadeOnDelete();
            $table->unsignedBigInteger('version_id')->nullable()->index();
            $table->string('collection', 64); // rooms, menus, navLinks, cardImages, userElements, deleted
            $table->unsignedInteger('sort_order')->default(0);
            $table->string('item_ref', 120)->nullable();
            $table->foreignId('parent_id')->nullable()->constrained('template_content_items')->cascadeOnDelete();
            $table->timestamps();

            $table->index(['team_role_template_id', 'version_id', 'collection'], 'tpl_content_collection_idx');
        });

        Schema::create('template_content_fields', function (Blueprint $table) {
            $table->id();
            $table->foreignId('content_item_id')->constrained('template_content_items')->cascadeOnDelete();
            $table->string('field_name', 120);
            $table->longText('field_value')->nullable();
            $table->timestamps();

            $table->index(['content_item_id', 'field_name'], 'tpl_content_fields_idx');
        });

        // Migrate existing JSON → flat rows
        $this->migrateExistingData();

        Schema::table('team_role_templates', function (Blueprint $table) {
            if (Schema::hasColumn('team_role_templates', 'customizations')) {
                $table->dropColumn('customizations');
            }
            if (Schema::hasColumn('team_role_templates', 'layout')) {
                $table->dropColumn('layout');
            }
        });

        Schema::table('team_role_template_versions', function (Blueprint $table) {
            if (Schema::hasColumn('team_role_template_versions', 'customizations')) {
                $table->dropColumn('customizations');
            }
            if (Schema::hasColumn('team_role_template_versions', 'layout')) {
                $table->dropColumn('layout');
            }
        });

        if (Schema::hasColumn('group_settings', 'customizations')) {
            Schema::table('group_settings', function (Blueprint $table) {
                $table->dropColumn('customizations');
            });
        }
    }

    private function migrateExistingData(): void
    {
        $templates = DB::table('team_role_templates')->get();
        foreach ($templates as $template) {
            $customizations = $this->decodeJson($template->customizations ?? null);
            $layout = $this->decodeJson($template->layout ?? null);
            $this->writeNormalized((int) $template->id, null, is_array($customizations) ? $customizations : [], is_array($layout) ? $layout : []);
        }

        $versions = DB::table('team_role_template_versions')->get();
        foreach ($versions as $version) {
            $customizations = $this->decodeJson($version->customizations ?? null);
            $layout = $this->decodeJson($version->layout ?? null);
            $this->writeNormalized(
                (int) $version->team_role_template_id,
                (int) $version->id,
                is_array($customizations) ? $customizations : [],
                is_array($layout) ? $layout : []
            );
        }
    }

    private function decodeJson(mixed $value): mixed
    {
        if ($value === null || $value === '') {
            return null;
        }
        if (is_array($value)) {
            return $value;
        }
        if (is_string($value)) {
            $decoded = json_decode($value, true);

            return json_last_error() === JSON_ERROR_NONE ? $decoded : null;
        }

        return null;
    }

    private function writeNormalized(int $templateId, ?int $versionId, array $customizations, array $layout): void
    {
        $now = now();

        foreach (array_values($layout) as $index => $section) {
            if (!is_array($section)) {
                continue;
            }
            $sectionId = (string) ($section['id'] ?? $section['key'] ?? ('section_' . $index));
            DB::table('template_layouts')->insert([
                'team_role_template_id' => $templateId,
                'version_id' => $versionId,
                'sort_order' => $index,
                'section_id' => $sectionId,
                'is_visible' => (bool) ($section['visible'] ?? true),
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        $special = ['__userElements', '__deleted', '__navLinks', '__rooms', '__menus', '__cardImages'];

        foreach ($customizations as $key => $value) {
            if (in_array($key, $special, true)) {
                $this->migrateCollection($templateId, $versionId, (string) $key, $value, $now);
                continue;
            }

            if (!is_array($value)) {
                DB::table('template_styles')->insert([
                    'team_role_template_id' => $templateId,
                    'version_id' => $versionId,
                    'element_key' => (string) $key,
                    'property' => 'value',
                    'value' => is_scalar($value) ? (string) $value : '',
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
                continue;
            }

            foreach ($value as $prop => $propValue) {
                if ($prop === 'src' || $prop === 'background-image') {
                    $path = $this->normalizeImageValue($propValue, $templateId);
                    if ($path !== null && $path !== '') {
                        DB::table('template_images')->insert([
                            'team_role_template_id' => $templateId,
                            'version_id' => $versionId,
                            'element_key' => (string) $key,
                            'kind' => $prop === 'src' ? 'src' : 'background-image',
                            'image_path' => $path,
                            'created_at' => $now,
                            'updated_at' => $now,
                        ]);
                    }
                    continue;
                }

                $scalar = $this->scalarize($propValue);
                // Never store raw base64 in styles
                if (is_string($scalar) && str_starts_with($scalar, 'data:image')) {
                    $path = $this->normalizeImageValue($scalar, $templateId);
                    if ($path) {
                        DB::table('template_images')->insert([
                            'team_role_template_id' => $templateId,
                            'version_id' => $versionId,
                            'element_key' => (string) $key,
                            'kind' => 'src',
                            'image_path' => $path,
                            'created_at' => $now,
                            'updated_at' => $now,
                        ]);
                    }
                    continue;
                }

                DB::table('template_styles')->insert([
                    'team_role_template_id' => $templateId,
                    'version_id' => $versionId,
                    'element_key' => (string) $key,
                    'property' => (string) $prop,
                    'value' => $scalar,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }
        }
    }

    private function migrateCollection(int $templateId, ?int $versionId, string $key, mixed $value, $now): void
    {
        $collection = match ($key) {
            '__userElements' => 'userElements',
            '__deleted' => 'deleted',
            '__navLinks' => 'navLinks',
            '__rooms' => 'rooms',
            '__menus' => 'menus',
            '__cardImages' => 'cardImages',
            default => ltrim($key, '_'),
        };

        if ($key === '__deleted' && is_array($value)) {
            foreach (array_values($value) as $i => $deleted) {
                $itemId = DB::table('template_content_items')->insertGetId([
                    'team_role_template_id' => $templateId,
                    'version_id' => $versionId,
                    'collection' => $collection,
                    'sort_order' => $i,
                    'item_ref' => is_string($deleted) ? $deleted : (is_array($deleted) ? ($deleted['id'] ?? null) : null),
                    'parent_id' => null,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
                if (is_array($deleted)) {
                    foreach ($deleted as $field => $fieldValue) {
                        DB::table('template_content_fields')->insert([
                            'content_item_id' => $itemId,
                            'field_name' => (string) $field,
                            'field_value' => $this->scalarize($fieldValue),
                            'created_at' => $now,
                            'updated_at' => $now,
                        ]);
                    }
                } elseif (is_string($deleted)) {
                    DB::table('template_content_fields')->insert([
                        'content_item_id' => $itemId,
                        'field_name' => 'id',
                        'field_value' => $deleted,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ]);
                }
            }

            return;
        }

        if ($key === '__userElements' && is_array($value)) {
            foreach (array_values($value) as $i => $item) {
                if (!is_array($item)) {
                    continue;
                }
                $itemId = DB::table('template_content_items')->insertGetId([
                    'team_role_template_id' => $templateId,
                    'version_id' => $versionId,
                    'collection' => $collection,
                    'sort_order' => $i,
                    'item_ref' => $item['id'] ?? null,
                    'parent_id' => null,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
                foreach ($item as $field => $fieldValue) {
                    if (is_array($fieldValue)) {
                        continue;
                    }
                    DB::table('template_content_fields')->insert([
                        'content_item_id' => $itemId,
                        'field_name' => (string) $field,
                        'field_value' => $this->scalarize($fieldValue),
                        'created_at' => $now,
                        'updated_at' => $now,
                    ]);
                }
            }

            return;
        }

        if (!is_array($value)) {
            return;
        }

        // Object collections: __rooms, __menus, __navLinks, __cardImages
        $metaItemId = DB::table('template_content_items')->insertGetId([
            'team_role_template_id' => $templateId,
            'version_id' => $versionId,
            'collection' => $collection . '_meta',
            'sort_order' => 0,
            'item_ref' => 'meta',
            'parent_id' => null,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        foreach ($value as $field => $fieldValue) {
            if ($field === 'items' && is_array($fieldValue)) {
                foreach (array_values($fieldValue) as $i => $item) {
                    if (!is_array($item)) {
                        continue;
                    }
                    $itemId = DB::table('template_content_items')->insertGetId([
                        'team_role_template_id' => $templateId,
                        'version_id' => $versionId,
                        'collection' => $collection,
                        'sort_order' => $i,
                        'item_ref' => isset($item['id']) ? (string) $item['id'] : null,
                        'parent_id' => null,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ]);
                    foreach ($item as $itemField => $itemValue) {
                        if ($itemField === 'amenities' && is_array($itemValue)) {
                            foreach (array_values($itemValue) as $ai => $amenity) {
                                if (!is_array($amenity)) {
                                    continue;
                                }
                                $amenityId = DB::table('template_content_items')->insertGetId([
                                    'team_role_template_id' => $templateId,
                                    'version_id' => $versionId,
                                    'collection' => $collection . '_amenity',
                                    'sort_order' => $ai,
                                    'item_ref' => null,
                                    'parent_id' => $itemId,
                                    'created_at' => $now,
                                    'updated_at' => $now,
                                ]);
                                foreach ($amenity as $af => $av) {
                                    DB::table('template_content_fields')->insert([
                                        'content_item_id' => $amenityId,
                                        'field_name' => (string) $af,
                                        'field_value' => $this->scalarize($av),
                                        'created_at' => $now,
                                        'updated_at' => $now,
                                    ]);
                                }
                            }
                            continue;
                        }
                        if (is_array($itemValue)) {
                            // Skip nested objects/arrays that are not amenities (e.g. empty badgeStyle)
                            if ($itemValue === []) {
                                continue;
                            }
                            continue;
                        }
                        $fieldName = (string) $itemField;
                        $scalar = $this->scalarize($itemValue);
                        if (in_array($fieldName, ['img', 'image', 'src'], true) && $scalar !== '') {
                            $path = $this->normalizeImageValue($scalar, $templateId);
                            if ($path) {
                                DB::table('template_images')->insert([
                                    'team_role_template_id' => $templateId,
                                    'version_id' => $versionId,
                                    'element_key' => $collection . ':' . ($item['id'] ?? $i),
                                    'kind' => 'content',
                                    'image_path' => $path,
                                    'created_at' => $now,
                                    'updated_at' => $now,
                                ]);
                                $scalar = $path;
                            } elseif (str_starts_with($scalar, 'data:image')) {
                                $scalar = '';
                            }
                        }
                        DB::table('template_content_fields')->insert([
                            'content_item_id' => $itemId,
                            'field_name' => $fieldName,
                            'field_value' => $scalar,
                            'created_at' => $now,
                            'updated_at' => $now,
                        ]);
                    }
                }
                continue;
            }

            if (is_array($fieldValue)) {
                continue;
            }

            DB::table('template_content_fields')->insert([
                'content_item_id' => $metaItemId,
                'field_name' => (string) $field,
                'field_value' => $this->scalarize($fieldValue),
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
    }

    private function normalizeImageValue(mixed $value, ?int $templateId = null): ?string
    {
        if (!is_string($value) || $value === '') {
            return null;
        }
        // url('...') or url("...")
        if (preg_match("/url\\(['\"]?(.*?)['\"]?\\)/i", $value, $m)) {
            $value = $m[1];
        }
        if (str_starts_with($value, 'data:image')) {
            if ($templateId === null) {
                return null;
            }
            if (!preg_match('#^data:image/([a-zA-Z0-9+.-]+);base64,(.+)$#', $value, $m)) {
                return null;
            }
            $ext = strtolower($m[1]);
            if ($ext === 'jpeg') {
                $ext = 'jpg';
            }
            if ($ext === 'svg+xml') {
                $ext = 'svg';
            }
            if (!in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg'], true)) {
                $ext = 'png';
            }
            $binary = base64_decode($m[2], true);
            if ($binary === false) {
                return null;
            }
            $path = 'hotel-media/templates/' . $templateId . '/' . uniqid('img_', true) . '.' . $ext;
            \Illuminate\Support\Facades\Storage::disk('public')->put($path, $binary);

            return $path;
        }

        return $value;
    }

    private function scalarize(mixed $value): string
    {
        if ($value === null) {
            return '';
        }
        if (is_bool($value)) {
            return $value ? '1' : '0';
        }
        if (is_int($value) || is_float($value)) {
            return (string) $value;
        }
        if (is_string($value)) {
            return $value;
        }

        return '';
    }

    public function down(): void
    {
        Schema::table('team_role_templates', function (Blueprint $table) {
            $table->json('customizations')->nullable();
            $table->json('layout')->nullable();
        });

        Schema::table('team_role_template_versions', function (Blueprint $table) {
            $table->json('customizations')->nullable();
            $table->json('layout')->nullable();
        });

        if (Schema::hasTable('group_settings') && !Schema::hasColumn('group_settings', 'customizations')) {
            Schema::table('group_settings', function (Blueprint $table) {
                $table->json('customizations')->nullable();
            });
        }

        Schema::dropIfExists('template_content_fields');
        Schema::dropIfExists('template_content_items');
        Schema::dropIfExists('template_images');
        Schema::dropIfExists('template_styles');
        Schema::dropIfExists('template_layouts');
    }
};
