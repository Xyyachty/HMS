<?php

namespace App\Support;

use App\Models\TeamRoleTemplate;

/**
 * Computes what changed between two snapshots of a role's template, for the
 * faculty task-review Before/After. Reads both sides through
 * TemplateCustomizationStore's existing accessors — this never writes
 * anything, it only compares what is already there.
 *
 * A null version id means "nothing to compare against" — a first submission
 * with no earlier snapshot, or one that was pruned out from under a very old
 * task — so every key on the other side reads as fully added instead of
 * erroring or silently comparing against the live template.
 */
class TemplateDiff
{
    /** Customization property key => human label, for the Changes list. */
    private const PROPERTY_LABELS = [
        'text' => 'Text',
        'value' => 'Text',
        'iconClass' => 'Icon',
        'src' => 'Image',
        'background-image' => 'Background image',
        'color' => 'Text colour',
        'background-color' => 'Background colour',
        'font-family' => 'Font',
        'font-weight' => 'Font weight',
        'font-style' => 'Font style',
        'text-decoration' => 'Text decoration',
        'font-size' => 'Font size',
        'text-align' => 'Text alignment',
        'line-height' => 'Line height',
        'letter-spacing' => 'Letter spacing',
        'background-size' => 'Background size',
        'background-position' => 'Background position',
        'background-repeat' => 'Background repeat',
        'padding' => 'Padding',
        'padding-top' => 'Padding (top)',
        'padding-right' => 'Padding (right)',
        'padding-bottom' => 'Padding (bottom)',
        'padding-left' => 'Padding (left)',
        'margin' => 'Margin',
        'margin-top' => 'Margin (top)',
        'margin-right' => 'Margin (right)',
        'margin-bottom' => 'Margin (bottom)',
        'margin-left' => 'Margin (left)',
        'border' => 'Border',
        'border-radius' => 'Border radius',
        'box-shadow' => 'Shadow',
        'opacity' => 'Opacity',
        'width' => 'Width',
        'height' => 'Height',
        'max-width' => 'Max width',
        'min-width' => 'Min width',
        'min-height' => 'Min height',
        'z-index' => 'Stack order',
        'transform' => 'Position',
        'top' => 'Position (top)',
        'left' => 'Position (left)',
        'right' => 'Position (right)',
        'bottom' => 'Position (bottom)',
        'display' => 'Visibility',
        'overflow' => 'Overflow',
    ];

    /** Bookkeeping keys inside an element entry that are not user-visible content. */
    private const IGNORED_PROPERTIES = ['hmsId', 'page', 'freePosition', 'moveMode', 'keepFixed', 'position'];

    /** Collection json-key => human singular label, for item/map change rows. */
    private const COLLECTION_LABELS = [
        'rooms' => 'Room',
        'menus' => 'Menu item',
        'navLinks' => 'Navigation link',
        'brandName' => 'Hotel name',
        'roomCardStyle' => 'Room card colour',
        'cardImages' => 'Card image',
        'heroSlides' => 'Hero slide',
    ];

    /**
     * @return array{summary: array{added:int,modified:int,removed:int}, changes: array, highlight: array{added: array, modified: array}}
     */
    public static function between(TeamRoleTemplate $template, ?int $beforeVersionId, ?int $afterVersionId): array
    {
        $templateId = (int) $template->team_role_template_id;

        // Compare the merged site on both sides, not this role's chunk alone —
        // the same thing the Before and After previews render.
        //
        // A role's chunk is not the site. Shared keys move between roles as they
        // are saved (claimSharedContentKeys / claimSharedLogo hand ownership to
        // the newest writer), so a key leaving this role's row reads as a deletion
        // even though nothing on the site moved: reviewing a "Change Hotel Name"
        // task reported the logo as reset to default, purely because some other
        // role had since claimed it. Every other role contributes identically to
        // both sides here, so anything they own cancels out and only what this
        // role actually did between the two snapshots survives.
        $groupName = (string) $template->group_name;
        $facultyId = (int) $template->faculty_id;
        $role = (string) $template->role;

        $before = HotelTemplateBuilder::mergeTeamCustomizations(
            $groupName,
            $facultyId,
            [$role => $beforeVersionId !== null ? $beforeVersionId : 'baseline']
        );
        $after = HotelTemplateBuilder::mergeTeamCustomizations(
            $groupName,
            $facultyId,
            [$role => $afterVersionId !== null ? $afterVersionId : 'baseline']
        );

        $changes = [];
        self::diffElements($before, $after, $changes);
        self::diffUserElements($before, $after, $changes);
        self::diffDeleted($before, $after, $changes);
        self::diffCollections($before, $after, $changes);
        self::diffLayout($templateId, $beforeVersionId, $afterVersionId, $changes);

        $summary = ['added' => 0, 'modified' => 0, 'removed' => 0];
        $highlight = ['added' => [], 'modified' => []];
        foreach ($changes as $change) {
            $summary[$change['type']]++;
            if ($change['type'] === 'removed' || empty($change['key'])) {
                continue;
            }
            $highlight[$change['type']][] = [
                'key' => $change['key'],
                'hms_id' => $change['hms_id'] ?? null,
                'page' => $change['page'] ?? 'home',
            ];
        }

        return [
            'summary' => $summary,
            'changes' => $changes,
            'highlight' => $highlight,
        ];
    }

    private static function emptyCustomizations(): array
    {
        return [
            HotelTemplateBuilder::USER_ELEMENTS_KEY => [],
            HotelTemplateBuilder::DELETED_KEY => [],
        ];
    }

    /** Plain selector-keyed elements: every top-level key that is not one of the special collections. */
    private static function diffElements(array $before, array $after, array &$changes): void
    {
        $skip = array_keys(TemplateCustomizationStore::SPECIAL_KEYS);
        $beforeMap = self::elementEntries($before, $skip);
        $afterMap = self::elementEntries($after, $skip);

        foreach ($afterMap as $key => $entry) {
            if (!array_key_exists($key, $beforeMap)) {
                $changes[] = self::elementChange('added', $key, null, $entry);
            }
        }
        foreach ($beforeMap as $key => $entry) {
            if (!array_key_exists($key, $afterMap)) {
                $changes[] = self::elementChange('removed', $key, $entry, null);
            }
        }
        foreach ($afterMap as $key => $afterEntry) {
            if (!array_key_exists($key, $beforeMap)) {
                continue;
            }
            $beforeEntry = $beforeMap[$key];
            $fields = self::diffEntryFields($beforeEntry, $afterEntry);
            if ($fields === []) {
                continue;
            }
            $changes[] = [
                'type' => 'modified',
                'scope' => 'element',
                'key' => $key,
                'hms_id' => $afterEntry['hmsId'] ?? $beforeEntry['hmsId'] ?? null,
                'page' => $afterEntry['page'] ?? $beforeEntry['page'] ?? 'home',
                // Name it by what it was, so a renamed heading still reads as the
                // heading the faculty remembers rather than its new text.
                'label' => self::describeKey($key, $beforeEntry ?: $afterEntry),
                'fields' => $fields,
            ];
        }
    }

    private static function elementEntries(array $customizations, array $skipKeys): array
    {
        $out = [];
        foreach ($customizations as $key => $value) {
            if (in_array($key, $skipKeys, true)) {
                continue;
            }
            if (!is_array($value) || $value === []) {
                continue;
            }
            $out[$key] = $value;
        }

        return $out;
    }

    private static function elementChange(string $type, string $key, ?array $before, ?array $after): array
    {
        $entry = $after ?? $before ?? [];
        $fields = [];
        foreach ($entry as $prop => $value) {
            if (in_array($prop, self::IGNORED_PROPERTIES, true) || is_array($value)) {
                continue;
            }
            $fields[] = [
                'property' => $prop,
                'label' => self::propertyLabel((string) $prop),
                'from' => $type === 'removed' ? self::formatValue((string) $prop, $value) : null,
                'to' => $type === 'added' ? self::formatValue((string) $prop, $value) : null,
            ];
        }

        return [
            'type' => $type,
            'scope' => 'element',
            'key' => $key,
            'hms_id' => $entry['hmsId'] ?? null,
            'page' => $entry['page'] ?? 'home',
            'label' => self::describeKey($key, $entry),
            'fields' => $fields,
        ];
    }

    private static function diffEntryFields(array $before, array $after): array
    {
        $out = [];
        $keys = array_unique(array_merge(array_keys($before), array_keys($after)));
        foreach ($keys as $prop) {
            if (in_array($prop, self::IGNORED_PROPERTIES, true)) {
                continue;
            }
            $b = $before[$prop] ?? null;
            $a = $after[$prop] ?? null;
            if (is_array($b) || is_array($a)) {
                continue;
            }
            if (self::normalizeScalar($b) === self::normalizeScalar($a)) {
                continue;
            }
            $out[] = [
                'property' => $prop,
                'label' => self::propertyLabel((string) $prop),
                'from' => self::formatValue((string) $prop, $b),
                'to' => self::formatValue((string) $prop, $a),
            ];
        }

        return $out;
    }

    private static function propertyLabel(string $prop): string
    {
        return self::PROPERTY_LABELS[$prop] ?? ucfirst(str_replace('-', ' ', $prop));
    }

    private static function normalizeScalar(mixed $value): string
    {
        return trim((string) ($value ?? ''));
    }

    private static function formatValue(string $prop, mixed $value): ?string
    {
        $value = $value === null ? '' : (string) $value;
        if ($value === '') {
            return null;
        }
        if (in_array($prop, ['src', 'background-image'], true)) {
            $path = parse_url($value, PHP_URL_PATH) ?: $value;
            $base = basename((string) $path);

            return $base !== '' ? $base : $value;
        }

        return mb_strlen($value) > 120 ? mb_substr($value, 0, 117) . '…' : $value;
    }

    /** What the last tag in a structural selector is, in words. */
    private const TAG_LABELS = [
        'h1' => 'Heading', 'h2' => 'Heading', 'h3' => 'Subheading', 'h4' => 'Subheading',
        'h5' => 'Subheading', 'h6' => 'Subheading',
        'p' => 'Paragraph', 'span' => 'Text', 'a' => 'Link', 'button' => 'Button',
        'li' => 'List item', 'label' => 'Label', 'img' => 'Image', 'nav' => 'Navigation bar',
        'header' => 'Header', 'footer' => 'Footer', 'section' => 'Section', 'div' => 'Block',
        'input' => 'Input', 'textarea' => 'Text box', 'i' => 'Icon',
    ];

    /**
     * Name an element the way the faculty sees it, not the way it is stored.
     * The keys are raw structural selectors ("#root > main > div > h1") or a
     * data-hms-id, neither of which means anything to someone reviewing a task,
     * so lead with the kind of element and let the caller add its text.
     */
    private static function describeKey(string $key, array $entry = []): string
    {
        $name = null;

        if (preg_match('/data-(?:hms-id|edit-id)="([^"]+)"/', $key, $m)) {
            $name = 'Element';
        } else {
            // Trailing tag of the selector path, minus any :nth-of-type() suffix.
            $last = trim((string) array_slice(explode('>', $key), -1)[0]);
            $last = strtolower(trim((string) preg_replace('/[:\[].*$/', '', $last)));
            if ($last !== '' && isset(self::TAG_LABELS[$last])) {
                $name = self::TAG_LABELS[$last];
            }
        }

        $name = $name ?? 'Element';

        // The copy itself is the most recognizable handle there is.
        $text = trim((string) ($entry['text'] ?? $entry['value'] ?? ''));
        if ($text !== '') {
            if (mb_strlen($text) > 40) {
                $text = mb_substr($text, 0, 37) . '…';
            }

            return $name . ' — “' . $text . '”';
        }

        return $name;
    }

    /** __userElements: freeform elements the student added, matched by their own id. */
    private static function diffUserElements(array $before, array $after, array &$changes): void
    {
        $beforeList = self::keyedById($before[HotelTemplateBuilder::USER_ELEMENTS_KEY] ?? []);
        $afterList = self::keyedById($after[HotelTemplateBuilder::USER_ELEMENTS_KEY] ?? []);

        foreach ($afterList as $id => $item) {
            if (!array_key_exists($id, $beforeList)) {
                $changes[] = self::userElementChange('added', $id, $item);
            }
        }
        foreach ($beforeList as $id => $item) {
            if (!array_key_exists($id, $afterList)) {
                $changes[] = self::userElementChange('removed', $id, $item);
            }
        }
        foreach ($afterList as $id => $afterItem) {
            if (!array_key_exists($id, $beforeList)) {
                continue;
            }
            $beforeItem = $beforeList[$id];
            $fields = self::diffEntryFields($beforeItem, $afterItem);
            if ($fields === []) {
                continue;
            }
            $changes[] = [
                'type' => 'modified',
                'scope' => 'user_element',
                'key' => self::userElementSelector($id),
                'hms_id' => $id,
                'page' => $afterItem['page'] ?? $beforeItem['page'] ?? 'home',
                'label' => 'Added element',
                'fields' => $fields,
            ];
        }
    }

    private static function keyedById(mixed $list): array
    {
        $out = [];
        if (!is_array($list)) {
            return $out;
        }
        foreach (array_values($list) as $i => $item) {
            if (!is_array($item)) {
                continue;
            }
            $id = isset($item['id']) ? (string) $item['id'] : ('idx-' . $i);
            $out[$id] = $item;
        }

        return $out;
    }

    private static function userElementChange(string $type, string $id, array $entry): array
    {
        return [
            'type' => $type,
            'scope' => 'user_element',
            'key' => self::userElementSelector($id),
            'hms_id' => $id,
            'page' => $entry['page'] ?? 'home',
            'label' => 'Custom element',
            'fields' => [],
        ];
    }

    private static function userElementSelector(string $id): string
    {
        return '[data-hms-id="' . $id . '"]';
    }

    /**
     * __deleted: entries new in After mean the student removed a stock template
     * element. There is nothing left to point at in the After preview, so these
     * carry no highlight key — they only show up in the Changes list.
     */
    private static function diffDeleted(array $before, array $after, array &$changes): void
    {
        $beforeIds = self::deletedIds($before[HotelTemplateBuilder::DELETED_KEY] ?? []);
        $afterIds = self::deletedIds($after[HotelTemplateBuilder::DELETED_KEY] ?? []);

        foreach ($afterIds as $id => $entry) {
            if (array_key_exists($id, $beforeIds)) {
                continue;
            }
            $changes[] = [
                'type' => 'removed',
                'scope' => 'deleted',
                'key' => null,
                'hms_id' => $id,
                'page' => $entry['page'] ?? 'home',
                'label' => 'Removed a page element',
                'fields' => [],
            ];
        }
    }

    private static function deletedIds(mixed $list): array
    {
        $out = [];
        if (!is_array($list)) {
            return $out;
        }
        foreach ($list as $entry) {
            if (is_string($entry) && $entry !== '') {
                $out[$entry] = ['page' => 'home'];
            } elseif (is_array($entry) && !empty($entry['id'])) {
                $out[(string) $entry['id']] = $entry;
            }
        }

        return $out;
    }

    /** The five repeatable collections: rooms, menus, nav links, card images, hero slides. */
    private static function diffCollections(array $before, array $after, array &$changes): void
    {
        foreach (TemplateCustomizationStore::SPECIAL_KEYS as $key => $jsonName) {
            if (in_array($key, [HotelTemplateBuilder::USER_ELEMENTS_KEY, HotelTemplateBuilder::DELETED_KEY], true)) {
                continue;
            }

            $beforePayload = $before[$key] ?? [];
            $afterPayload = $after[$key] ?? [];
            $beforePayload = is_array($beforePayload) ? $beforePayload : [];
            $afterPayload = is_array($afterPayload) ? $afterPayload : [];

            self::diffCollectionMeta($jsonName, $beforePayload, $afterPayload, $changes);
            self::diffCollectionItems(
                $jsonName,
                is_array($beforePayload['items'] ?? null) ? $beforePayload['items'] : [],
                is_array($afterPayload['items'] ?? null) ? $afterPayload['items'] : [],
                $changes
            );
            self::diffCollectionMap(
                $jsonName,
                is_array($beforePayload['map'] ?? null) ? $beforePayload['map'] : [],
                is_array($afterPayload['map'] ?? null) ? $afterPayload['map'] : [],
                $changes
            );
        }
    }

    private static function diffCollectionMeta(string $jsonName, array $before, array $after, array &$changes): void
    {
        $beforeMeta = self::withoutKeys($before, ['items', 'map']);
        $afterMeta = self::withoutKeys($after, ['items', 'map']);
        $fields = self::diffEntryFields($beforeMeta, $afterMeta);
        if ($fields === []) {
            return;
        }
        $changes[] = [
            'type' => 'modified',
            'scope' => 'collection_meta',
            'key' => null,
            'hms_id' => null,
            'page' => $after['page'] ?? $before['page'] ?? 'home',
            'label' => (self::COLLECTION_LABELS[$jsonName] ?? ucfirst($jsonName)) . ' settings',
            'fields' => $fields,
        ];
    }

    private static function diffCollectionItems(string $jsonName, array $beforeItems, array $afterItems, array &$changes): void
    {
        $beforeMap = self::keyedByRef($beforeItems);
        $afterMap = self::keyedByRef($afterItems);
        $label = self::COLLECTION_LABELS[$jsonName] ?? ucfirst($jsonName);

        foreach ($afterMap as $ref => $item) {
            if (array_key_exists($ref, $beforeMap)) {
                continue;
            }
            // A team's first hotel name has no Before row, so the raw diff reads
            // as "added". What faculty need to see for Task 1 is what the
            // placeholder became, the way the logo is already reported.
            if ($jsonName === 'brandName') {
                $changes[] = [
                    'type' => 'modified', 'scope' => 'collection_item', 'key' => null, 'hms_id' => null,
                    'page' => $item['page'] ?? 'home',
                    'label' => $label,
                    'fields' => [[
                        'property' => 'label',
                        'label' => 'Name',
                        'from' => HotelTemplateBuilder::DEFAULT_BRAND_NAME,
                        'to' => $item['label'] ?? '',
                    ]],
                ];
                continue;
            }
            $changes[] = [
                'type' => 'added', 'scope' => 'collection_item', 'key' => null, 'hms_id' => null,
                'page' => $item['page'] ?? 'home',
                'label' => $label . ': ' . self::itemTitle($item),
                'fields' => [],
            ];
        }
        foreach ($beforeMap as $ref => $item) {
            if (array_key_exists($ref, $afterMap)) {
                continue;
            }
            $changes[] = [
                'type' => 'removed', 'scope' => 'collection_item', 'key' => null, 'hms_id' => null,
                'page' => $item['page'] ?? 'home',
                'label' => $label . ': ' . self::itemTitle($item),
                'fields' => [],
            ];
        }
        foreach ($afterMap as $ref => $afterItem) {
            if (!array_key_exists($ref, $beforeMap)) {
                continue;
            }
            $beforeItem = $beforeMap[$ref];
            $fields = self::diffEntryFields(
                self::withoutKeys($beforeItem, ['amenities']),
                self::withoutKeys($afterItem, ['amenities'])
            );
            if (json_encode($beforeItem['amenities'] ?? null) !== json_encode($afterItem['amenities'] ?? null)) {
                $fields[] = ['property' => 'amenities', 'label' => 'Amenities', 'from' => null, 'to' => null];
            }
            if ($fields === []) {
                continue;
            }
            $changes[] = [
                'type' => 'modified', 'scope' => 'collection_item', 'key' => null, 'hms_id' => null,
                'page' => $afterItem['page'] ?? $beforeItem['page'] ?? 'home',
                'label' => $label . ': ' . self::itemTitle($afterItem),
                'fields' => $fields,
            ];
        }
    }

    private static function keyedByRef(array $items): array
    {
        $out = [];
        foreach (array_values($items) as $i => $item) {
            if (!is_array($item)) {
                continue;
            }
            $ref = $item['id'] ?? $item['name'] ?? ('idx-' . $i);
            $out[(string) $ref] = $item;
        }

        return $out;
    }

    private static function itemTitle(array $item): string
    {
        foreach (['name', 'title', 'label', 'id'] as $field) {
            if (!empty($item[$field]) && is_string($item[$field])) {
                return $item[$field];
            }
        }

        return 'item';
    }

    private static function withoutKeys(array $arr, array $drop): array
    {
        foreach ($drop as $k) {
            unset($arr[$k]);
        }

        return $arr;
    }

    private static function diffCollectionMap(string $jsonName, array $beforeMap, array $afterMap, array &$changes): void
    {
        foreach ($afterMap as $ref => $url) {
            if (array_key_exists($ref, $beforeMap)) {
                continue;
            }
            $changes[] = self::mapChange('added', $jsonName, (string) $ref, null, $url);
        }
        foreach ($beforeMap as $ref => $url) {
            if (array_key_exists($ref, $afterMap)) {
                continue;
            }
            $changes[] = self::mapChange('removed', $jsonName, (string) $ref, $url, null);
        }
        foreach ($afterMap as $ref => $afterUrl) {
            if (!array_key_exists($ref, $beforeMap)) {
                continue;
            }
            $beforeUrl = $beforeMap[$ref];
            if (self::normalizeScalar($beforeUrl) === self::normalizeScalar($afterUrl)) {
                continue;
            }
            $changes[] = self::mapChange('modified', $jsonName, (string) $ref, $beforeUrl, $afterUrl);
        }
    }

    /**
     * A card-image map entry, keyed "kind:id" (e.g. "brand:logo"). The template
     * renders these with data-hms-content-kind / data-hms-content-id, so unlike
     * the rest of the collections they resolve to a real node and can carry a
     * highlight key — the site logo is the common case, and it is the one thing
     * every "change the logo" task turns on.
     */
    private static function mapChange(string $type, string $jsonName, string $ref, mixed $from, mixed $to): array
    {
        [$kind, $id] = array_pad(explode(':', $ref, 2), 2, '');
        $isLogo = $kind === 'brand';

        $fromLabel = $from === null ? null : self::formatValue('src', $from);
        $toLabel = $to === null ? null : self::formatValue('src', $to);

        // The site ships with a stock logo that is a template fallback, not a
        // stored row, so the first time a student replaces it there is nothing
        // on the Before side and the raw diff calls it an addition. To the
        // faculty reading this it plainly changed, so say so — and name what it
        // changed from, which is what the Before preview is actually showing.
        if ($isLogo && $type === 'added') {
            $type = 'modified';
            $fromLabel = 'Default logo';
        } elseif ($isLogo && $type === 'removed') {
            $type = 'modified';
            $toLabel = 'Default logo';
        }

        return [
            'type' => $type,
            'scope' => 'collection_map',
            'key' => ($kind !== '' && $id !== '')
                ? '[data-hms-content-kind="' . $kind . '"][data-hms-content-id="' . $id . '"]'
                : null,
            'hms_id' => null,
            'page' => 'home',
            'label' => self::mapLabel($jsonName, $kind, $id, $ref),
            'fields' => [[
                'property' => 'src',
                'label' => 'Image',
                'from' => $fromLabel,
                'to' => $toLabel,
            ]],
        ];
    }

    private static function mapLabel(string $jsonName, string $kind, string $id, string $ref): string
    {
        if ($kind === 'brand') {
            return $id === HotelTemplateBuilder::LOGO_IMAGE_ID
                ? 'Site logo'
                : 'Logo (' . str_replace('logo-', '', $id) . ')';
        }

        $label = self::COLLECTION_LABELS[$jsonName] ?? ucfirst($jsonName);

        return $label . ' image: ' . ($id !== '' ? $id : $ref);
    }

    /** Section show/hide toggles and reorders. */
    private static function diffLayout(int $templateId, ?int $beforeVersionId, ?int $afterVersionId, array &$changes): void
    {
        $before = $beforeVersionId !== null
            ? TemplateCustomizationStore::readLayout($templateId, $beforeVersionId)
            : HotelTemplateBuilder::defaultLayout();
        $after = $afterVersionId !== null
            ? TemplateCustomizationStore::readLayout($templateId, $afterVersionId)
            : HotelTemplateBuilder::defaultLayout();

        $beforeMap = [];
        foreach ($before as $section) {
            if (is_array($section) && isset($section['id'])) {
                $beforeMap[(string) $section['id']] = $section;
            }
        }

        foreach ($after as $section) {
            if (!is_array($section) || !isset($section['id'])) {
                continue;
            }
            $id = (string) $section['id'];
            $prev = $beforeMap[$id] ?? null;
            if ($prev === null) {
                continue;
            }
            $prevVisible = (bool) ($prev['visible'] ?? true);
            $nowVisible = (bool) ($section['visible'] ?? true);
            if ($prevVisible === $nowVisible) {
                continue;
            }
            $changes[] = [
                'type' => 'modified',
                'scope' => 'layout',
                'key' => null,
                'hms_id' => null,
                'page' => 'home',
                'label' => 'Section: ' . self::sectionLabel($id),
                'fields' => [[
                    'property' => 'visible',
                    'label' => 'Visibility',
                    'from' => $prevVisible ? 'Shown' : 'Hidden',
                    'to' => $nowVisible ? 'Shown' : 'Hidden',
                ]],
            ];
        }
    }

    private static function sectionLabel(string $id): string
    {
        return HotelTemplateBuilder::COMPONENT_LIBRARY[$id]['label'] ?? ucfirst($id);
    }
}
