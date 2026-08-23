<?php

namespace App\Support;

use App\Models\TeamRoleTemplate;
use App\Models\TemplateContentField;
use App\Models\TemplateContentItem;
use App\Models\TemplateElement;
use App\Models\TemplateImage;
use App\Models\TemplateLayout;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class TemplateCustomizationStore
{
    /** Live/current rows use version_id = 0 (never NULL). */
    public const LIVE_VERSION_ID = 0;

    public const SPECIAL_KEYS = [
        HotelTemplateBuilder::USER_ELEMENTS_KEY => 'userElements',
        HotelTemplateBuilder::DELETED_KEY => 'deleted',
        HotelTemplateBuilder::NAV_LINKS_KEY => 'navLinks',
        HotelTemplateBuilder::ROOMS_KEY => 'rooms',
        HotelTemplateBuilder::MENUS_KEY => 'menus',
        HotelTemplateBuilder::CARD_IMAGES_KEY => 'cardImages',
        HotelTemplateBuilder::HERO_SLIDES_KEY => 'heroSlides',
    ];

    /** Editor customization key => DB column on template_elements */
    private const ELEMENT_COLUMN_MAP = [
        'hmsId' => 'hms_id',
        'page' => 'page',
        'freePosition' => 'free_position',
        'moveMode' => 'move_mode',
        'keepFixed' => 'keep_fixed',
        'text' => 'text_content',
        'iconClass' => 'icon_class',
        'value' => 'display_value',
        'src' => 'image_src',
        'background-image' => 'image_background',
        'color' => 'color',
        'background-color' => 'background_color',
        'font-family' => 'font_family',
        'font-weight' => 'font_weight',
        'font-style' => 'font_style',
        'text-decoration' => 'text_decoration',
        'font-size' => 'font_size',
        'text-align' => 'text_align',
        'line-height' => 'line_height',
        'letter-spacing' => 'letter_spacing',
        'background-size' => 'background_size',
        'background-position' => 'background_position',
        'background-repeat' => 'background_repeat',
        'padding' => 'padding',
        'padding-top' => 'padding_top',
        'padding-right' => 'padding_right',
        'padding-bottom' => 'padding_bottom',
        'padding-left' => 'padding_left',
        'margin' => 'margin',
        'margin-top' => 'margin_top',
        'margin-right' => 'margin_right',
        'margin-bottom' => 'margin_bottom',
        'margin-left' => 'margin_left',
        'border' => 'border',
        'border-radius' => 'border_radius',
        'box-shadow' => 'box_shadow',
        'opacity' => 'opacity',
        'position' => 'position',
        'top' => 'top',
        'left' => 'left_pos',
        'right' => 'right_pos',
        'bottom' => 'bottom_pos',
        'width' => 'width',
        'height' => 'height',
        'max-width' => 'max_width',
        'min-width' => 'min_width',
        'min-height' => 'min_height',
        'z-index' => 'z_index',
        'transform' => 'transform',
        'display' => 'display',
        'overflow' => 'overflow',
    ];

    public static function read(TeamRoleTemplate $template, ?int $versionId = null): array
    {
        $versionId = self::normalizeVersionId($versionId);

        return [
            'customizations' => self::readCustomizations((int) $template->team_role_template_id, $versionId),
            'layout' => self::readLayout((int) $template->team_role_template_id, $versionId),
        ];
    }

    public static function readCustomizations(int $templateId, ?int $versionId = null): array
    {
        $versionId = self::normalizeVersionId($versionId);
        $out = [
            HotelTemplateBuilder::USER_ELEMENTS_KEY => [],
            HotelTemplateBuilder::DELETED_KEY => [],
        ];

        $elements = TemplateElement::query()
            ->where('team_role_template_id', $templateId)
            ->where('version_id', $versionId)
            ->get();

        foreach ($elements as $row) {
            $entry = self::elementRowToCustomization($row);
            if ($entry !== []) {
                $out[$row->element_key] = $entry;
            }
        }

        self::appendCollections($out, $templateId, $versionId);

        return $out;
    }

    public static function readLayout(int $templateId, ?int $versionId = null): array
    {
        $versionId = self::normalizeVersionId($versionId);
        $rows = TemplateLayout::query()
            ->where('team_role_template_id', $templateId)
            ->where('version_id', $versionId)
            ->orderBy('sort_order')
            ->get();

        if ($rows->isEmpty()) {
            return HotelTemplateBuilder::defaultLayout();
        }

        return $rows->map(fn (TemplateLayout $row) => [
            'id' => $row->section_id,
            'visible' => (bool) $row->is_visible,
        ])->values()->all();
    }

    /**
     * Rows waiting to be inserted, keyed by model class. Filled by queue() and sent as
     * multi-row INSERTs by flushQueued(). Every statement here is a network round-trip
     * to Supabase, so one INSERT of 200 rows beats 200 INSERTs by two orders of
     * magnitude — inserting row by row is what made a save take seconds.
     *
     * Only rows nothing else refers to are queued. TemplateContentItem is still written
     * one at a time because its id is needed immediately for child rows.
     *
     * @var array<class-string, list<array<string, mixed>>>
     */
    private static array $queued = [];

    public static function write(TeamRoleTemplate $template, array $customizations, array $layout, ?int $versionId = null): void
    {
        $templateId = (int) $template->team_role_template_id;
        $versionId = self::normalizeVersionId($versionId);

        try {
            DB::transaction(function () use ($templateId, $customizations, $layout, $versionId) {
                self::clear($templateId, $versionId);
                self::writeLayout($templateId, $layout, $versionId);
                self::writeCustomizations($templateId, $customizations, $versionId);
                self::flushQueued();
            });
        } finally {
            // A failed transaction must not leak rows into the next write.
            self::$queued = [];
        }
    }

    /** Hold a row back for a batched insert. */
    private static function queue(string $modelClass, array $row): void
    {
        self::$queued[$modelClass][] = $row;
    }

    private static function flushQueued(): void
    {
        $now = now();

        foreach (self::$queued as $modelClass => $rows) {
            $usesTimestamps = (new $modelClass)->usesTimestamps();

            // Element rows are built from whatever properties the student actually set,
            // so their column sets differ. Grouping by column signature keeps each
            // INSERT well-formed and lets omitted columns take their schema default —
            // padding with NULL instead would violate the NOT NULL boolean columns.
            $groups = [];
            foreach ($rows as $row) {
                if ($usesTimestamps) {
                    $row['created_at'] ??= $now;
                    $row['updated_at'] ??= $now;
                }
                $groups[implode('|', array_keys($row))][] = $row;
            }

            foreach ($groups as $group) {
                foreach (array_chunk($group, 200) as $chunk) {
                    $modelClass::insert($chunk);
                }
            }
        }

        self::$queued = [];
    }

    public static function snapshotToVersion(TeamRoleTemplate $template, int $versionId): void
    {
        $data = self::read($template, self::LIVE_VERSION_ID);
        self::write($template, $data['customizations'], $data['layout'], $versionId);
    }

    /** Delete historical flat copies (version_id > 0). Live rows (0) stay. */
    public static function purgeAllVersionedCopies(): void
    {
        $itemIds = TemplateContentItem::query()->where('version_id', '>', 0)->pluck('template_content_item_id');
        if ($itemIds->isNotEmpty()) {
            TemplateContentField::whereIn('template_content_item_id', $itemIds)->delete();
        }
        TemplateContentItem::query()->where('version_id', '>', 0)->whereNotNull('parent_id')->delete();
        TemplateContentItem::query()->where('version_id', '>', 0)->delete();
        TemplateElement::query()->where('version_id', '>', 0)->delete();
        TemplateImage::query()->where('version_id', '>', 0)->delete();
        TemplateLayout::query()->where('version_id', '>', 0)->delete();
    }

    /** Remove flat rows for one version snapshot. */
    public static function clearVersion(int $templateId, int $versionId): void
    {
        self::clear($templateId, $versionId);
    }

    /**
     * Keep only the newest $keep version snapshots (metadata + flat rows).
     */
    public static function pruneOldVersions(int $templateId, int $keep = 5): void
    {
        $keep = max(0, $keep);
        $keepIds = \App\Models\TeamRoleTemplateVersion::query()
            ->where('team_role_template_id', $templateId)
            ->orderByDesc('version')
            ->limit($keep)
            ->pluck('team_role_template_version_id');

        // Snapshots a task's review comparison is built on are not history to age
        // out — pruning one would silently empty the faculty's Before/After.
        $pinnedIds = \App\Models\Task::query()
            ->where(function ($q) {
                $q->whereNotNull('submitted_version_id')->orWhereNotNull('previous_version_id');
            })
            ->get(['submitted_version_id', 'previous_version_id'])
            ->flatMap(fn ($task) => [$task->submitted_version_id, $task->previous_version_id])
            ->filter()
            ->map(fn ($id) => (int) $id)
            ->unique();

        $protectedIds = $keepIds->map(fn ($id) => (int) $id)->merge($pinnedIds)->unique();

        $old = \App\Models\TeamRoleTemplateVersion::query()
            ->where('team_role_template_id', $templateId)
            ->when($protectedIds->isNotEmpty(), fn ($q) => $q->whereNotIn('team_role_template_version_id', $protectedIds))
            ->get();

        foreach ($old as $version) {
            self::clearVersion($templateId, (int) $version->team_role_template_version_id);
            $version->delete();
        }
    }

    public static function clear(int $templateId, ?int $versionId = null): void
    {
        $versionId = self::normalizeVersionId($versionId);

        // Deleting the content items is enough: template_content_fields.template_content_item_id
        // and template_content_items.parent_id are both ON DELETE CASCADE, so the
        // database removes the fields and the nested children itself. Doing it by hand
        // cost three extra round-trips (a SELECT for the ids, a fields DELETE and a
        // children DELETE) on every single save.
        TemplateContentItem::query()
            ->where('team_role_template_id', $templateId)
            ->where('version_id', $versionId)
            ->delete();

        TemplateElement::query()
            ->where('team_role_template_id', $templateId)
            ->where('version_id', $versionId)
            ->delete();

        TemplateImage::query()
            ->where('team_role_template_id', $templateId)
            ->where('version_id', $versionId)
            ->delete();

        TemplateLayout::query()
            ->where('team_role_template_id', $templateId)
            ->where('version_id', $versionId)
            ->delete();
    }

    /** @return int version_id; 0 = live */
    private static function normalizeVersionId(?int $versionId): int
    {
        if ($versionId === null || $versionId < 0) {
            return self::LIVE_VERSION_ID;
        }

        return $versionId;
    }

    private static function writeLayout(int $templateId, array $layout, ?int $versionId): void
    {
        $versionId = self::normalizeVersionId($versionId);
        foreach (array_values($layout) as $index => $section) {
            if (!is_array($section)) {
                continue;
            }
            self::queue(TemplateLayout::class, [
                'team_role_template_id' => $templateId,
                'version_id' => $versionId,
                'sort_order' => $index,
                'section_id' => (string) ($section['id'] ?? $section['key'] ?? ('section_' . $index)),
                'is_visible' => (bool) ($section['visible'] ?? true),
            ]);
        }
    }

    private static function writeCustomizations(int $templateId, array $customizations, ?int $versionId): void
    {
        $versionId = self::normalizeVersionId($versionId);
        foreach ($customizations as $key => $value) {
            if (isset(self::SPECIAL_KEYS[$key])) {
                self::writeCollection($templateId, $versionId, self::SPECIAL_KEYS[$key], $key, $value);
                continue;
            }

            if (!is_array($value)) {
                self::queue(TemplateElement::class, [
                    'team_role_template_id' => $templateId,
                    'version_id' => $versionId,
                    'element_key' => (string) $key,
                    'display_value' => self::scalarize($value),
                    'page' => 'home',
                ]);
                continue;
            }

            $row = [
                'team_role_template_id' => $templateId,
                'version_id' => $versionId,
                'element_key' => (string) $key,
            ];

            foreach ($value as $prop => $propValue) {
                $col = self::ELEMENT_COLUMN_MAP[$prop] ?? null;
                if ($col === null) {
                    continue;
                }

                if ($col === 'image_src' || $col === 'image_background') {
                    $path = self::persistImageValue($propValue, $templateId);
                    $row[$col] = $path ?: null;
                    continue;
                }

                if ($col === 'free_position' || $col === 'keep_fixed') {
                    $row[$col] = filter_var($propValue, FILTER_VALIDATE_BOOLEAN) || $propValue === '1' || $propValue === 1;
                    continue;
                }

                $row[$col] = self::scalarize($propValue);
            }

            self::queue(TemplateElement::class, $row);
        }
    }

    private static function elementRowToCustomization(TemplateElement $row): array
    {
        $entry = [];

        foreach (self::ELEMENT_COLUMN_MAP as $prop => $col) {
            $val = $row->{$col} ?? null;
            if ($val === null || $val === '') {
                continue;
            }

            if ($col === 'free_position' || $col === 'keep_fixed') {
                if ((bool) $val) {
                    $entry[$prop] = true;
                }
                continue;
            }

            if ($col === 'image_src') {
                $entry['src'] = self::publicUrlIfNeeded((string) $val);
                continue;
            }

            if ($col === 'image_background') {
                $path = self::publicUrlIfNeeded((string) $val);
                $entry['background-image'] = str_starts_with($path, 'url(') ? $path : "url('" . $path . "')";
                continue;
            }

            $entry[$prop] = self::unscalarize(is_bool($val) ? ($val ? '1' : '0') : (string) $val, $prop);
        }

        return $entry;
    }

    private static function writeCollection(int $templateId, ?int $versionId, string $collection, string $originalKey, mixed $value): void
    {
        $versionId = self::normalizeVersionId($versionId);
        if ($originalKey === HotelTemplateBuilder::DELETED_KEY && is_array($value)) {
            foreach (array_values($value) as $i => $deleted) {
                $item = TemplateContentItem::create([
                    'team_role_template_id' => $templateId,
                    'version_id' => $versionId,
                    'collection' => $collection,
                    'sort_order' => $i,
                    'item_ref' => is_string($deleted) ? $deleted : (is_array($deleted) ? ($deleted['id'] ?? null) : null),
                ]);
                if (is_array($deleted)) {
                    foreach ($deleted as $field => $fieldValue) {
                        if (is_array($fieldValue)) {
                            continue;
                        }
                        self::queue(TemplateContentField::class, [
                            'template_content_item_id' => $item->template_content_item_id,
                            'field_name' => (string) $field,
                            'field_value' => self::scalarize($fieldValue),
                        ]);
                    }
                } elseif (is_string($deleted)) {
                    self::queue(TemplateContentField::class, [
                        'template_content_item_id' => $item->template_content_item_id,
                        'field_name' => 'id',
                        'field_value' => $deleted,
                    ]);
                }
            }

            return;
        }

        if ($originalKey === HotelTemplateBuilder::USER_ELEMENTS_KEY && is_array($value)) {
            foreach (array_values($value) as $i => $el) {
                if (!is_array($el)) {
                    continue;
                }
                $item = TemplateContentItem::create([
                    'team_role_template_id' => $templateId,
                    'version_id' => $versionId,
                    'collection' => $collection,
                    'sort_order' => $i,
                    'item_ref' => isset($el['id']) ? (string) $el['id'] : null,
                ]);
                foreach ($el as $field => $fieldValue) {
                    if (is_array($fieldValue)) {
                        continue;
                    }
                    // Persist inline images for user elements
                    if (in_array($field, ['src', 'background-image', 'img'], true)) {
                        $path = self::persistImageValue($fieldValue, $templateId);
                        if ($path) {
                            $fieldValue = $path;
                            if ($field !== 'img') {
                                self::queue(TemplateImage::class, [
                                    'team_role_template_id' => $templateId,
                                    'version_id' => $versionId,
                                    'element_key' => 'userElement:' . ($el['id'] ?? $i),
                                    'kind' => $field === 'src' ? 'src' : 'background-image',
                                    'image_path' => $path,
                                ]);
                            }
                        }
                    }
                    self::queue(TemplateContentField::class, [
                        'template_content_item_id' => $item->template_content_item_id,
                        'field_name' => (string) $field,
                        'field_value' => self::scalarize($fieldValue),
                    ]);
                }
            }

            return;
        }

        if (!is_array($value)) {
            return;
        }

        $meta = TemplateContentItem::create([
            'team_role_template_id' => $templateId,
            'version_id' => $versionId,
            'collection' => $collection . '_meta',
            'sort_order' => 0,
            'item_ref' => 'meta',
        ]);

        foreach ($value as $field => $fieldValue) {
            if ($field === 'items' && is_array($fieldValue)) {
                foreach (array_values($fieldValue) as $i => $itemData) {
                    if (!is_array($itemData)) {
                        continue;
                    }
                    $item = TemplateContentItem::create([
                        'team_role_template_id' => $templateId,
                        'version_id' => $versionId,
                        'collection' => $collection,
                        'sort_order' => $i,
                        'item_ref' => isset($itemData['id']) ? (string) $itemData['id'] : null,
                    ]);
                    foreach ($itemData as $itemField => $itemValue) {
                        if ($itemField === 'amenities' && is_array($itemValue)) {
                            foreach (array_values($itemValue) as $ai => $amenity) {
                                if (!is_array($amenity)) {
                                    continue;
                                }
                                $amenityItem = TemplateContentItem::create([
                                    'team_role_template_id' => $templateId,
                                    'version_id' => $versionId,
                                    'collection' => $collection . '_amenity',
                                    'sort_order' => $ai,
                                    'parent_id' => $item->template_content_item_id,
                                ]);
                                foreach ($amenity as $af => $av) {
                                    if (is_array($av)) {
                                        continue;
                                    }
                                    self::queue(TemplateContentField::class, [
                                        'template_content_item_id' => $amenityItem->template_content_item_id,
                                        'field_name' => (string) $af,
                                        'field_value' => self::scalarize($av),
                                    ]);
                                }
                            }
                            continue;
                        }
                        if (is_array($itemValue)) {
                            continue;
                        }
                        $scalar = self::scalarize($itemValue);
                        if (in_array((string) $itemField, ['img', 'image', 'src'], true) && $scalar !== '') {
                            $path = self::persistImageValue($scalar, $templateId);
                            if ($path) {
                                $scalar = $path;
                                self::queue(TemplateImage::class, [
                                    'team_role_template_id' => $templateId,
                                    'version_id' => $versionId,
                                    'element_key' => $collection . ':' . ($itemData['id'] ?? $i),
                                    'kind' => 'content',
                                    'image_path' => $path,
                                ]);
                            }
                        }
                        self::queue(TemplateContentField::class, [
                            'template_content_item_id' => $item->template_content_item_id,
                            'field_name' => (string) $itemField,
                            'field_value' => $scalar,
                        ]);
                    }
                }
                continue;
            }

            // Keyed maps (e.g. __cardImages: {map: {"brand:logo": url}}) are a
            // flat key => url dictionary rather than an ordered items list.
            // Without this they were dropped here and never persisted at all.
            if ($field === 'map' && is_array($fieldValue)) {
                $index = 0;
                foreach ($fieldValue as $mapKey => $mapValue) {
                    if (is_array($mapValue)) {
                        continue;
                    }
                    $scalar = self::scalarize($mapValue);
                    if ($scalar !== '') {
                        $stored = self::persistImageValue($scalar, $templateId);
                        if ($stored) {
                            $scalar = $stored;
                        }
                    }
                    $mapItem = TemplateContentItem::create([
                        'team_role_template_id' => $templateId,
                        'version_id' => $versionId,
                        'collection' => $collection . '_map',
                        'sort_order' => $index++,
                        'item_ref' => (string) $mapKey,
                    ]);
                    self::queue(TemplateContentField::class, [
                        'template_content_item_id' => $mapItem->template_content_item_id,
                        'field_name' => 'value',
                        'field_value' => $scalar,
                    ]);
                }
                continue;
            }

            if (is_array($fieldValue)) {
                continue;
            }

            self::queue(TemplateContentField::class, [
                'template_content_item_id' => $meta->template_content_item_id,
                'field_name' => (string) $field,
                'field_value' => self::scalarize($fieldValue),
            ]);
        }
    }

    private static function appendCollections(array &$out, int $templateId, ?int $versionId): void
    {
        $versionId = self::normalizeVersionId($versionId);
        $items = TemplateContentItem::query()
            ->with('fields')
            ->where('team_role_template_id', $templateId)
            ->where('version_id', $versionId)
            ->orderBy('sort_order')
            ->get();

        $byCollection = $items->groupBy('collection');

        // deleted
        $out[HotelTemplateBuilder::DELETED_KEY] = [];
        foreach ($byCollection->get('deleted', collect()) as $item) {
            $fields = self::fieldsMap($item);
            if (count($fields) === 1 && isset($fields['id'])) {
                $out[HotelTemplateBuilder::DELETED_KEY][] = $fields;
            } elseif ($fields !== []) {
                $out[HotelTemplateBuilder::DELETED_KEY][] = $fields;
            } elseif ($item->item_ref) {
                $out[HotelTemplateBuilder::DELETED_KEY][] = ['id' => $item->item_ref, 'page' => 'home'];
            }
        }

        // userElements
        $out[HotelTemplateBuilder::USER_ELEMENTS_KEY] = [];
        foreach ($byCollection->get('userElements', collect()) as $item) {
            $out[HotelTemplateBuilder::USER_ELEMENTS_KEY][] = self::fieldsMap($item);
        }

        foreach ([
            'rooms' => HotelTemplateBuilder::ROOMS_KEY,
            'menus' => HotelTemplateBuilder::MENUS_KEY,
            'navLinks' => HotelTemplateBuilder::NAV_LINKS_KEY,
            'cardImages' => HotelTemplateBuilder::CARD_IMAGES_KEY,
            'heroSlides' => HotelTemplateBuilder::HERO_SLIDES_KEY,
        ] as $collection => $jsonKey) {
            $meta = $byCollection->get($collection . '_meta', collect())->first();
            $payload = $meta ? self::fieldsMap($meta) : [];
            $list = [];
            foreach ($byCollection->get($collection, collect()) as $item) {
                if ($item->parent_id) {
                    continue;
                }
                $row = self::fieldsMap($item);
                $amenities = [];
                foreach ($items->where('parent_id', $item->template_content_item_id)->where('collection', $collection . '_amenity') as $amenity) {
                    $amenities[] = self::fieldsMap($amenity);
                }
                if ($amenities !== []) {
                    $row['amenities'] = $amenities;
                }
                // Prefer image_path from template_images if present
                $imgRow = TemplateImage::query()
                    ->where('team_role_template_id', $templateId)
                    ->where('version_id', $versionId)
                    ->where('kind', 'content')
                    ->where('element_key', $collection . ':' . ($item->item_ref ?? $item->sort_order))
                    ->first();
                if ($imgRow) {
                    $row['img'] = self::publicUrlIfNeeded($imgRow->image_path);
                }
                $list[] = $row;
            }
            // Rebuild keyed maps written by the 'map' branch above.
            $map = [];
            foreach ($byCollection->get($collection . '_map', collect()) as $mapItem) {
                $ref = $mapItem->item_ref;
                if ($ref === null || $ref === '') {
                    continue;
                }
                $fields = self::fieldsMap($mapItem);
                $value = (string) ($fields['value'] ?? '');
                if ($value !== '') {
                    $map[(string) $ref] = self::publicUrlIfNeeded($value);
                }
            }

            if ($list !== [] || $payload !== [] || $map !== []) {
                $payload['items'] = $list;
                if ($map !== []) {
                    $payload['map'] = $map;
                }
                $out[$jsonKey] = $payload;
            }
        }
    }

    private static function fieldsMap(TemplateContentItem $item): array
    {
        $map = [];
        foreach ($item->fields as $field) {
            $map[$field->field_name] = self::unscalarize($field->field_value, $field->field_name);
        }

        return $map;
    }

    /**
     * Convert base64 data-URLs to stored files; strip url() wrappers; keep http(s)/storage paths.
     */
    public static function persistImageValue(mixed $value, int $templateId): ?string
    {
        if (!is_string($value) || $value === '') {
            return null;
        }

        if (preg_match("/url\\(['\"]?(.*?)['\"]?\\)/i", $value, $m)) {
            $value = $m[1];
        }

        if (str_starts_with($value, 'data:image')) {
            if (!preg_match('#^data:image/([a-zA-Z0-9+.-]+);base64,(.+)$#', $value, $m)) {
                return null;
            }
            $ext = strtolower($m[1]);
            if ($ext === 'jpeg') {
                $ext = 'jpg';
            }
            if (!in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg+xml'], true)) {
                $ext = 'png';
            }
            if ($ext === 'svg+xml') {
                $ext = 'svg';
            }
            $binary = base64_decode($m[2], true);
            if ($binary === false) {
                return null;
            }
            $path = 'hotel-media/templates/' . $templateId . '/' . Str::uuid() . '.' . $ext;
            Storage::disk(HotelImageStore::disk())->put($path, $binary);

            return $path;
        }

        // Convert absolute /storage/... or full asset URL to relative storage path when possible
        if (preg_match('#/storage/(.+)$#', $value, $m)) {
            return $m[1];
        }

        return $value;
    }

    public static function publicUrlIfNeeded(string $path): string
    {
        if ($path === '') {
            return $path;
        }
        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://') || str_starts_with($path, 'data:')) {
            return $path;
        }
        if (str_starts_with($path, '/storage/')) {
            return $path;
        }
        // Relative storage path
        if (!str_contains($path, '://')) {
            return Storage::disk(HotelImageStore::disk())->url(ltrim($path, '/'));
        }

        return $path;
    }

    private static function scalarize(mixed $value): string
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

    private static function unscalarize(?string $value, string $field): mixed
    {
        if ($value === null) {
            return '';
        }
        $boolFields = ['visible', 'freePosition', 'keepFixed', 'is_visible'];
        if (in_array($field, $boolFields, true) || str_ends_with($field, 'Visible')) {
            return in_array($value, ['1', 'true', 'yes', 'on'], true);
        }
        if (in_array($field, ['price', 'sort_order', 'version'], true) && is_numeric($value)) {
            return str_contains($value, '.') ? (float) $value : (int) $value;
        }

        return $value;
    }
}
