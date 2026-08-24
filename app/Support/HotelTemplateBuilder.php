<?php

namespace App\Support;

use App\Models\GroupSettings;
use App\Models\StudentGroup;
use App\Models\TeamRoleTemplate;
use App\Models\TeamRoleTemplateVersion;
use App\Models\TemplateContentItem;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class HotelTemplateBuilder
{
    public const ROLES = [
        'front_desk' => 'Front Desk',
        'room_management' => 'Room Management',
        'restaurant_management' => 'Restaurant Services',
        'housekeeping' => 'Housekeeping Services',
        'maintenance' => 'Maintenance',
    ];

    /** How many version snapshots to keep per role template (reduces DB redundancy). */
    public const MAX_VERSION_SNAPSHOTS = 5;

    /**
     * Hotel website pages each role may redesign.
     * Teammates still see the full merged site; edit is page-scoped.
     */
    public const ROLE_EDITABLE_PAGES = [
        'front_desk' => ['home'],
        'room_management' => ['rooms'],
        'restaurant_management' => ['restaurant'],
        // Housekeeping also owns the add-ons catalogue the Amenities page lists,
        // so it is the one that page's static copy belongs to as well.
        'housekeeping' => ['experience', 'amenities'],
        'maintenance' => [],
    ];

    /** Department builder page each role opens. */
    public const ROLE_ROUTES = [
        'front_desk' => 'students.frontdesk',
        'room_management' => 'students.roommanagement',
        'restaurant_management' => 'students.restaurant',
        'housekeeping' => 'students.housekeeping',
        'maintenance' => 'students.maintenance',
    ];

    public const USER_ELEMENTS_KEY = '__userElements';
    public const DELETED_KEY = '__deleted';
    public const NAV_LINKS_KEY = '__navLinks';
    public const ROOMS_KEY = '__rooms';
    public const MENUS_KEY = '__menus';
    public const CARD_IMAGES_KEY = '__cardImages';
    public const HERO_SLIDES_KEY = '__heroSlides';

    /**
     * Site-content keys more than one role may write. Unlike element entries
     * these are not page-scoped, so if several rows keep a copy the merge below
     * resolves them by role order and the later role silently wins — losing the
     * change whoever edited most recently actually made. Each of these is kept
     * in exactly one row; writing one claims it and clears the siblings.
     *
     * __cardImages is NOT here: most of it is per-card artwork owned by whichever
     * role owns that card, so it is merged key by key instead — see
     * mergeTeamCustomizations() and filterCustomizationsForRole(). The one entry
     * inside it that IS shared, the site logo, is claimed on its own by
     * claimSharedLogo() so clearing it cannot take the rest of the map with it.
     */
    public const SHARED_CONTENT_KEYS = [
        self::NAV_LINKS_KEY,
        self::ROOMS_KEY,
        self::MENUS_KEY,
    ];

    /**
     * The site's single logo, stored as one card image rather than one per
     * section. Any of these roles may change it and the change applies to the
     * whole site — header, footer and every page read this one key.
     */
    public const LOGO_IMAGE_ID = 'logo';
    public const LOGO_IMAGE_MAP_KEY = 'brand:' . self::LOGO_IMAGE_ID;
    public const SITE_OWNING_ROLES = [
        'front_desk',
        'restaurant_management',
        'housekeeping',
        'room_management',
    ];

    /** Default section library per role (no drag-and-drop — add/remove/reorder via buttons). */
    public const COMPONENT_LIBRARY = [
        'hero' => ['label' => 'Hero Banner', 'description' => 'Large headline with background image'],
        'welcome' => ['label' => 'Welcome Text', 'description' => 'Intro paragraph and CTA'],
        'features' => ['label' => 'Feature Cards', 'description' => '3-up service cards'],
        'gallery' => ['label' => 'Image Gallery', 'description' => 'Photo grid'],
        'cta' => ['label' => 'Call to Action', 'description' => 'Button strip'],
        'contact' => ['label' => 'Contact Block', 'description' => 'Phone, email, hours'],
        'amenities' => ['label' => 'Amenities List', 'description' => 'Icon + label list'],
        'footer' => ['label' => 'Footer', 'description' => 'Links and copyright'],
    ];

    public static function defaultLayout(): array
    {
        return [
            ['id' => 'hero', 'visible' => true],
            ['id' => 'welcome', 'visible' => true],
            ['id' => 'features', 'visible' => true],
            ['id' => 'cta', 'visible' => true],
            ['id' => 'footer', 'visible' => true],
        ];
    }

    public static function isValidRole(string $role): bool
    {
        return array_key_exists($role, self::ROLES);
    }

    public static function editablePagesForRole(string $role): array
    {
        return self::ROLE_EDITABLE_PAGES[$role] ?? [];
    }

    public static function preferredPageForRole(string $role): string
    {
        $pages = self::editablePagesForRole($role);

        return $pages[0] ?? 'home';
    }

    public static function routeNameForRole(string $role): ?string
    {
        return self::ROLE_ROUTES[$role] ?? null;
    }

    /**
     * Builder modules a student can open, one per role they hold.
     * Ordered by ROLES so the list stays stable instead of following the
     * order faculty happened to tick the role checkboxes.
     *
     * @param  string[]  $roles
     * @return array<int, array{role: string, label: string, route: string, editable: bool}>
     */
    public static function modulesForRoles(array $roles): array
    {
        $modules = [];

        foreach (self::ROLES as $role => $label) {
            if (!in_array($role, $roles, true)) {
                continue;
            }
            $route = self::routeNameForRole($role);
            if (!$route) {
                continue;
            }

            $modules[] = [
                'role' => $role,
                'label' => $label,
                'route' => $route,
                'editable' => self::editablePagesForRole($role) !== [],
            ];
        }

        return $modules;
    }

    public static function membershipFor(User $user): ?StudentGroup
    {
        $student = $user->student;
        if (!$student) {
            return null;
        }

        return StudentGroup::with('roles')->where('student_id', $student->user_information_id)->first();
    }

    public static function studentRoleKeys(StudentGroup $membership): array
    {
        return $membership->roles->pluck('role')->filter()->values()->all();
    }

    /**
     * Can this student edit this role's template?
     * True only if they own the role. Faculty-issued cross-role edit grants
     * were removed; a member edits their own section and nothing else.
     */
    public static function canEdit(User $user, StudentGroup $membership, string $role): bool
    {
        if (!self::isValidRole($role)) {
            return false;
        }

        return in_array($role, self::studentRoleKeys($membership), true);
    }

    public static function canView(User $user, StudentGroup $membership, string $role): bool
    {
        if (!self::isValidRole($role)) {
            return false;
        }

        // Any authenticated teammate (same faculty + group membership) can view
        return (int) ($user->student?->user_information_id) > 0
            && (int) $membership->faculty_id > 0
            && $membership->group_name !== '';
    }

    public static function ensureTemplate(StudentGroup $membership, string $role): TeamRoleTemplate
    {
        $template = TeamRoleTemplate::firstOrCreate(
            [
                'group_name' => $membership->group_name,
                'faculty_id' => $membership->faculty_id,
                'role' => $role,
            ],
            [
                'selected_template' => null, // Front Desk must choose Template 1 or 2
                'is_published' => false,
                'version' => 1,
            ]
        );

        if ($template->wasRecentlyCreated) {
            $template->customizations = [];
            $template->layout = self::defaultLayout();
            $template->save();
        }

        // Self-heal group_id on every access, not just at creation, so any row that
        // predates the backfill (or a deploy-ordering gap) fixes itself.
        if ($template->group_id !== $membership->group_id) {
            $template->group_id = $membership->group_id;
            $template->save();
        }

        return $template;
    }

    /**
     * Freeze a role's template exactly as it stands right now and return the
     * snapshot's row id.
     *
     * Taken when a student submits a task, so the faculty review always has a real
     * "this is what they handed in" anchor. save() only snapshots on a manual save
     * or publish — the builder's 7s autosave deliberately does not — so relying on
     * that history left teams who never pressed Ctrl+S with nothing to compare.
     *
     * The template's own version counter is not bumped: this records the work, it
     * is not another edit to it. The snapshot row still needs a version number of
     * its own, though — (team_role_template_id, version) is unique, so reusing the
     * template's current number collides with whatever save-snapshot already holds
     * it, and every snapshot after the first would fail. Numbering from the highest
     * this template has ever used keeps the row unique and still sorts it newest,
     * which is the order pruneOldVersions() keeps by.
     *
     * Returns null rather than throwing — a failed snapshot costs the comparison,
     * and must never cost the student their submission.
     */
    public static function snapshotForReview(TeamRoleTemplate $template, ?User $user = null, string $label = 'Submitted'): ?int
    {
        try {
            return DB::transaction(function () use ($template, $user, $label) {
                $nextVersion = 1 + (int) max(
                    (int) TeamRoleTemplateVersion::where('team_role_template_id', $template->team_role_template_id)
                        ->max('version'),
                    (int) $template->version
                );

                $versionRow = TeamRoleTemplateVersion::create([
                    'team_role_template_id' => $template->team_role_template_id,
                    'version' => $nextVersion,
                    'selected_template' => $template->selected_template,
                    'is_published' => $template->is_published,
                    'label' => $label,
                    'created_by' => $user?->user_id,
                ]);

                TemplateCustomizationStore::snapshotToVersion(
                    $template,
                    (int) $versionRow->team_role_template_version_id
                );

                return (int) $versionRow->team_role_template_version_id;
            });
        } catch (\Throwable $e) {
            report($e);

            return null;
        }
    }

    /** Max updated_at across the team's role templates — drives live sync for everyone. */
    public static function teamSyncVersion(string $groupName, int $facultyId): int
    {
        $ts = TeamRoleTemplate::where('group_name', $groupName)
            ->where('faculty_id', $facultyId)
            ->max('updated_at');

        return $ts ? strtotime((string) $ts) : 0;
    }

    /**
     * Merge every role's customizations into one hotel site preview.
     * Later roles overwrite same selector keys; user elements are concatenated.
     *
     * $versionOverrides lets a caller ask for one role's chunk as it looked at
     * a past version instead of live — used by the faculty Before/After review
     * preview. Every other caller passes nothing and gets today's exact result.
     * The literal string 'baseline' instead of a version id renders that role's
     * chunk as empty (a first submission has no earlier snapshot to fall back
     * on, so "Before" is the pristine template with nothing customized yet).
     */
    public static function mergeTeamCustomizations(string $groupName, int $facultyId, array $versionOverrides = []): array
    {
        $order = array_flip(array_keys(self::ROLES));
        $rows = TeamRoleTemplate::where('group_name', $groupName)
            ->where('faculty_id', $facultyId)
            ->get()
            ->sortBy(fn ($row) => $order[$row->role] ?? 99)
            ->values();

        $merged = [
            self::USER_ELEMENTS_KEY => [],
            self::DELETED_KEY => [],
        ];

        foreach ($rows as $row) {
            $override = $versionOverrides[$row->role] ?? null;
            if ($override === 'baseline') {
                $chunk = [self::USER_ELEMENTS_KEY => [], self::DELETED_KEY => []];
            } elseif ($override !== null) {
                $chunk = TemplateCustomizationStore::readCustomizations(
                    (int) $row->team_role_template_id,
                    (int) $override
                );
            } else {
                $chunk = is_array($row->customizations) ? $row->customizations : [];
            }
            foreach ($chunk as $key => $value) {
                if ($key === self::USER_ELEMENTS_KEY) {
                    if (is_array($value)) {
                        $merged[self::USER_ELEMENTS_KEY] = array_merge(
                            $merged[self::USER_ELEMENTS_KEY],
                            array_values($value)
                        );
                    }
                    continue;
                }
                if ($key === self::DELETED_KEY) {
                    if (is_array($value)) {
                        // Entries are page-scoped arrays (['id' => …, 'page' => …]),
                        // not plain ids, so array_unique's string comparison would
                        // stringify them and blow up with "Array to string conversion".
                        $merged[self::DELETED_KEY] = self::uniqueDeletedEntries(array_merge(
                            $merged[self::DELETED_KEY],
                            $value
                        ));
                    }
                    continue;
                }
                // Each role's row only ever carries its own section's logo entry
                // (see filterCustomizationsForRole), so union the maps instead of
                // letting the last row in ROLES order replace the whole thing.
                if ($key === self::CARD_IMAGES_KEY) {
                    $incomingMap = (is_array($value) && isset($value['map']) && is_array($value['map'])) ? $value['map'] : [];
                    $existingMap = (isset($merged[$key]['map']) && is_array($merged[$key]['map'])) ? $merged[$key]['map'] : [];
                    $merged[$key] = ['map' => array_merge($existingMap, $incomingMap)];
                    continue;
                }
                $merged[$key] = $value;
            }
        }

        return $merged;
    }

    /**
     * De-duplicate __deleted entries across roles. Entries may be a bare id
     * string (legacy) or a page-scoped array, so identity is the serialized
     * value rather than a string cast.
     */
    private static function uniqueDeletedEntries(array $entries): array
    {
        $seen = [];
        $out = [];

        foreach ($entries as $entry) {
            $fingerprint = is_array($entry) ? json_encode($entry) : (string) $entry;
            if (isset($seen[$fingerprint])) {
                continue;
            }
            $seen[$fingerprint] = true;
            $out[] = $entry;
        }

        return $out;
    }

    /**
     * Keep only shared site-content keys when switching Template 1 ↔ 2.
     * Structural free-position / transform edits target different DOMs and scramble layout.
     */
    public static function stripCrossTemplateLayoutCustomizations(array $customizations): array
    {
        $keepKeys = [
            self::NAV_LINKS_KEY,
            self::ROOMS_KEY,
            self::MENUS_KEY,
            self::CARD_IMAGES_KEY,
        ];
        $out = [];
        foreach ($keepKeys as $key) {
            if (array_key_exists($key, $customizations)) {
                $out[$key] = $customizations[$key];
            }
        }

        return $out;
    }

    /**
     * Keep only customization entries that belong to this role's editable pages.
     * Untagged legacy entries are attributed to Front Desk (home).
     */
    public static function filterCustomizationsForRole(array $customizations, string $role): array
    {
        $pages = self::editablePagesForRole($role);
        $out = [
            self::USER_ELEMENTS_KEY => [],
            self::DELETED_KEY => [],
        ];

        if ($pages === []) {
            return $out;
        }

        foreach ($customizations as $key => $value) {
            if ($key === self::USER_ELEMENTS_KEY) {
                if (!is_array($value)) {
                    continue;
                }
                foreach ($value as $item) {
                    if (!is_array($item)) {
                        continue;
                    }
                    $page = $item['page'] ?? null;
                    if ($page === null || $page === '') {
                        if ($role !== 'front_desk') {
                            continue;
                        }
                        $item['page'] = 'home';
                        $page = 'home';
                    }
                    if (in_array($page, $pages, true)) {
                        $out[self::USER_ELEMENTS_KEY][] = $item;
                    }
                }
                continue;
            }

            if ($key === self::DELETED_KEY) {
                if (!is_array($value)) {
                    continue;
                }
                foreach ($value as $deleted) {
                    if (is_array($deleted)) {
                        $page = $deleted['page'] ?? null;
                        if ($page === null || $page === '') {
                            if ($role !== 'front_desk') {
                                continue;
                            }
                            $deleted['page'] = 'home';
                            $page = 'home';
                        }
                        if (in_array($page, $pages, true)) {
                            $out[self::DELETED_KEY][] = $deleted;
                        }
                    } elseif (is_string($deleted) && $role === 'front_desk') {
                        $out[self::DELETED_KEY][] = ['id' => $deleted, 'page' => 'home'];
                    }
                }
                continue;
            }

            // Shared site content: Front Desk may edit rooms shown on Home;
            // Room Management may edit rooms on the Rooms page; both persist __rooms.
            if ($key === self::ROOMS_KEY && in_array($role, ['front_desk', 'room_management'], true)) {
                if (is_array($value)) {
                    $value['page'] = $value['page'] ?? 'rooms';
                    $out[$key] = $value;
                }
                continue;
            }

            if ($key === self::NAV_LINKS_KEY && $role === 'front_desk' && is_array($value)) {
                $value['page'] = $value['page'] ?? 'home';
                $out[$key] = $value;
                continue;
            }

            if ($key === self::MENUS_KEY && $role === 'restaurant_management' && is_array($value)) {
                $value['page'] = $value['page'] ?? 'restaurant';
                $out[$key] = $value;
                continue;
            }

            // The site has one shared logo under brand:logo, which any site-owning
            // role may change. It has to survive this filter for whichever role
            // saved it, or that role's change would be stripped before it is
            // written and the new logo would vanish on the next read.
            //
            // The per-section brand:logo-* entries are what the logo used to be.
            // They are kept for the role whose page owns them so no existing site
            // loses its picture, but nothing writes them any more: the template
            // reads them only as a fallback when brand:logo is unset.
            if ($key === self::CARD_IMAGES_KEY && is_array($value)) {
                $incomingMap = (isset($value['map']) && is_array($value['map'])) ? $value['map'] : [];
                $ownMap = [];
                foreach ($incomingMap as $mapKey => $url) {
                    [$kind, $id] = array_pad(explode(':', (string) $mapKey, 2), 2, '');
                    if ($kind !== 'brand') {
                        // Not a logo entry — keep it for roles that already owned this key.
                        if (in_array($role, self::SITE_OWNING_ROLES, true)) {
                            $ownMap[$mapKey] = $url;
                        }
                        continue;
                    }
                    if ($id === self::LOGO_IMAGE_ID) {
                        if (in_array($role, self::SITE_OWNING_ROLES, true)) {
                            $ownMap[$mapKey] = $url;
                        }
                        continue;
                    }
                    if (str_starts_with($id, 'logo-')) {
                        $section = substr($id, 5);
                        if (in_array($section, $pages, true)) {
                            $ownMap[$mapKey] = $url;
                        }
                    }
                }
                if ($ownMap !== []) {
                    $out[$key] = ['map' => $ownMap];
                }
                continue;
            }

            if (!is_array($value)) {
                if ($role === 'front_desk') {
                    $out[$key] = ['value' => $value, 'page' => 'home'];
                }
                continue;
            }

            $page = $value['page'] ?? null;
            if ($page === null || $page === '') {
                if ($role !== 'front_desk') {
                    continue;
                }
                $value['page'] = 'home';
                $page = 'home';
            }

            if (in_array($page, $pages, true)) {
                $out[$key] = $value;
            }
        }

        return $out;
    }

    public static function payload(TeamRoleTemplate $template, bool $canEdit): array
    {
        $groupName = (string) $template->group_name;
        $facultyId = (int) $template->faculty_id;
        $editablePages = self::editablePagesForRole($template->role);

        // "id" is the key the builder front-end reads, not the column name.
        return [
            'id' => $template->team_role_template_id,
            'role' => $template->role,
            'role_label' => self::ROLES[$template->role] ?? $template->role,
            'selected_template' => $template->selected_template,
            'customizations' => self::mergeTeamCustomizations($groupName, $facultyId),
            'own_customizations' => $template->customizations ?? [],
            'layout' => $template->layout ?? self::defaultLayout(),
            'is_published' => (bool) $template->is_published,
            'version' => (int) $template->version,
            'updated_at' => optional($template->updated_at)->toIso8601String(),
            'sync_version' => self::teamSyncVersion($groupName, $facultyId),
            'can_edit' => $canEdit,
            'editable_pages' => $editablePages,
            'preferred_page' => self::preferredPageForRole($template->role),
            'components' => self::COMPONENT_LIBRARY,
        ];
    }

    public static function save(
        TeamRoleTemplate $template,
        array $data,
        User $user,
        bool $publish = false,
        bool $snapshot = true,
        ?string $label = null
    ): TeamRoleTemplate {
        return DB::transaction(function () use ($template, $data, $user, $publish, $snapshot, $label) {
            if (array_key_exists('customizations', $data)) {
                $ownCustomizations = self::filterCustomizationsForRole(
                    is_array($data['customizations']) ? $data['customizations'] : [],
                    $template->role
                );
                $template->customizations = $ownCustomizations;
            }
            if (array_key_exists('layout', $data)) {
                $template->layout = $data['layout'];
            }
            if (array_key_exists('selected_template', $data) && $data['selected_template'] !== null) {
                $nextTemplate = (string) $data['selected_template'];
                $prevTemplate = (string) ($template->selected_template ?? '');
                $template->selected_template = $nextTemplate;

                // Switching Template 1 <-> 2 (or first pick after dirty edits): drop free-pos / overlays.
                if ($prevTemplate !== $nextTemplate) {
                    $cleaned = self::stripCrossTemplateLayoutCustomizations(
                        is_array($template->customizations) ? $template->customizations : []
                    );
                    $template->customizations = $cleaned;

                    TeamRoleTemplate::where('group_name', $template->group_name)
                        ->where('faculty_id', $template->faculty_id)
                        ->where('team_role_template_id', '!=', $template->team_role_template_id)
                        ->get()
                        ->each(function (TeamRoleTemplate $row) use ($nextTemplate) {
                            $row->selected_template = $nextTemplate;
                            $row->customizations = self::filterCustomizationsForRole(
                                self::stripCrossTemplateLayoutCustomizations(
                                    is_array($row->customizations) ? $row->customizations : []
                                ),
                                $row->role
                            );
                            $row->save();
                        });
                }
            }
            if ($publish) {
                $template->is_published = true;
            }

            $template->updated_by = $user->user_id;
            if ($snapshot) {
                // Step past every version row this template already has, not just
                // past the counter: review snapshots take numbers of their own
                // (see snapshotForReview), and (team_role_template_id, version) is
                // unique, so counting blindly would eventually land on one and
                // abort the save — costing the student the work they just saved.
                $template->version = 1 + (int) max(
                    (int) TeamRoleTemplateVersion::where('team_role_template_id', $template->team_role_template_id)
                        ->max('version'),
                    (int) $template->version
                );
            }
            if ($template->version < 1) {
                $template->version = 1;
            }
            $template->save();
            $template->touch();

            // Front Desk template choice applies to the whole team
            if ($template->role === 'front_desk' && !empty($template->selected_template)) {
                TeamRoleTemplate::where('group_name', $template->group_name)
                    ->where('faculty_id', $template->faculty_id)
                    ->where('role', '!=', 'front_desk')
                    ->update(['selected_template' => $template->selected_template]);
            }

            // This row is the newest writer of any shared key it holds.
            self::claimSharedContentKeys($template, $ownCustomizations ?? null);
            self::claimSharedLogo($template, $ownCustomizations ?? null);

            // Keep legacy group_settings in sync (merged team site)
            self::syncGroupSettings($template);

            if ($snapshot) {
                $versionRow = TeamRoleTemplateVersion::create([
                    'team_role_template_id' => $template->team_role_template_id,
                    'version' => $template->version,
                    'selected_template' => $template->selected_template,
                    'is_published' => $template->is_published,
                    'label' => $label ?: ($publish ? 'Published' : 'Auto-save'),
                    'created_by' => $user->user_id,
                ]);
                TemplateCustomizationStore::snapshotToVersion($template, (int) $versionRow->team_role_template_version_id);
                TemplateCustomizationStore::pruneOldVersions(
                    (int) $template->team_role_template_id,
                    self::MAX_VERSION_SNAPSHOTS
                );
            }

            return $template->fresh();
        });
    }

    /** Lightweight auto-save: updates content without bumping version number every few seconds. */
    public static function autosave(TeamRoleTemplate $template, array $data, User $user): TeamRoleTemplate
    {
        return DB::transaction(function () use ($template, $data, $user) {
            if (array_key_exists('customizations', $data)) {
                $ownCustomizations = self::filterCustomizationsForRole(
                    is_array($data['customizations']) ? $data['customizations'] : [],
                    $template->role
                );
                $template->customizations = $ownCustomizations;
            }
            if (array_key_exists('layout', $data)) {
                $template->layout = $data['layout'];
            }
            if (array_key_exists('selected_template', $data) && $data['selected_template'] !== null) {
                $template->selected_template = (string) $data['selected_template'];
            }
            $template->updated_by = $user->user_id;
            $template->save();
            $template->touch();

            self::claimSharedContentKeys($template, $ownCustomizations ?? null);
            self::claimSharedLogo($template, $ownCustomizations ?? null);
            self::syncGroupSettings($template);

            return $template->fresh();
        });
    }

    /**
     * Give this row sole ownership of every shared content key it carries by
     * dropping stale copies from its teammates' rows. Without this the merge
     * picks a winner by role order, so a role that never touched the key can
     * overwrite the value a teammate just saved.
     */
    public static function claimSharedContentKeys(TeamRoleTemplate $template, ?array $customizations = null): void
    {
        // The caller normally already holds these. Falling back to the accessor would
        // re-read the whole tree from the database — the saved-hook clears the pending
        // value first, so it cannot be served from memory — costing ~6 extra queries.
        $mine = $customizations ?? (is_array($template->customizations) ? $template->customizations : []);
        $claimed = array_values(array_intersect(self::SHARED_CONTENT_KEYS, array_keys($mine)));

        if ($claimed === []) {
            return;
        }

        // Shared keys are stored as content-item collections, so the stale copies can be
        // deleted directly. The previous version read each sibling's entire customization
        // tree through the accessor and then re-saved it, which fired the saved-hook and
        // rewrote that sibling's every row — around 40 statements per save, all of it to
        // remove a handful of rows. Fields and nested items cascade on delete.
        $collections = array_values(array_filter(array_map(
            fn (string $key) => TemplateCustomizationStore::SPECIAL_KEYS[$key] ?? null,
            $claimed
        )));

        if ($collections === []) {
            return;
        }

        $siblingIds = TeamRoleTemplate::where('group_name', $template->group_name)
            ->where('faculty_id', $template->faculty_id)
            ->where('team_role_template_id', '!=', $template->team_role_template_id)
            ->pluck('team_role_template_id');

        if ($siblingIds->isEmpty()) {
            return;
        }

        TemplateContentItem::query()
            ->whereIn('team_role_template_id', $siblingIds)
            ->where('version_id', TemplateCustomizationStore::LIVE_VERSION_ID)
            ->whereIn('collection', $collections)
            ->delete();
    }

    /**
     * Give this row sole ownership of the site logo.
     *
     * The logo is one entry inside __cardImages, and that key cannot be claimed
     * wholesale the way __rooms or __menus are: the rest of the map is per-card
     * artwork belonging to other roles, and deleting the whole collection would
     * take their room and menu pictures with it. So only the logo's own row is
     * dropped from the teammates' copies.
     *
     * Without this the merge in mergeTeamCustomizations() unions the maps in
     * ROLES order, so a teammate holding an older logo further down that order
     * would silently reinstate it over the change just saved.
     */
    public static function claimSharedLogo(TeamRoleTemplate $template, ?array $customizations = null): void
    {
        $mine = $customizations ?? (is_array($template->customizations) ? $template->customizations : []);
        $map = $mine[self::CARD_IMAGES_KEY]['map'] ?? null;

        if (!is_array($map) || !array_key_exists(self::LOGO_IMAGE_MAP_KEY, $map)) {
            return;
        }

        $siblingIds = TeamRoleTemplate::where('group_name', $template->group_name)
            ->where('faculty_id', $template->faculty_id)
            ->where('team_role_template_id', '!=', $template->team_role_template_id)
            ->pluck('team_role_template_id');

        if ($siblingIds->isEmpty()) {
            return;
        }

        // Card images are written as one content item per map entry, keyed by
        // item_ref — see TemplateCustomizationStore::writeCollection()'s 'map'
        // branch. Deleting just this ref leaves every other card image intact.
        TemplateContentItem::query()
            ->whereIn('team_role_template_id', $siblingIds)
            ->where('version_id', TemplateCustomizationStore::LIVE_VERSION_ID)
            ->where('collection', TemplateCustomizationStore::SPECIAL_KEYS[self::CARD_IMAGES_KEY] . '_map')
            ->where('item_ref', self::LOGO_IMAGE_MAP_KEY)
            ->delete();
    }

    /**
     * Mirror team hotel site into group_settings (legacy / group-wide lock).
     */
    public static function syncGroupSettings(TeamRoleTemplate $template): void
    {
        $groupName = (string) $template->group_name;
        $facultyId = (int) $template->faculty_id;

        $frontDesk = TeamRoleTemplate::where('group_name', $groupName)
            ->where('faculty_id', $facultyId)
            ->where('role', 'front_desk')
            ->first();

        $selected = $frontDesk?->selected_template ?: $template->selected_template;
        $payload = [
            'is_published' => (bool) ($frontDesk?->is_published ?? $template->is_published),
        ];

        if ($selected !== null && $selected !== '') {
            $payload['selected_template'] = $selected;
        }

        // Called from inside save()/autosave()'s transaction. Two teammates saving the
        // same team at once can collide on the (group_name, faculty_id) unique index,
        // and on PostgreSQL a duplicate-key error aborts the WHOLE transaction — every
        // later statement then fails with "current transaction is aborted", losing the
        // save. MySQL simply let the retry through. Nesting the write gives it its own
        // savepoint, so a collision rolls back this unit only and the retry succeeds.
        DB::transaction(function () use ($groupName, $facultyId, $payload, $template) {
            GroupSettings::updateOrCreate(
                [
                    'group_name' => $groupName,
                    'faculty_id' => $facultyId,
                ],
                $payload + ['group_id' => $template->group_id]
            );
        });
    }

    public static function restoreVersion(TeamRoleTemplate $template, int $versionNumber, User $user): TeamRoleTemplate
    {
        $version = TeamRoleTemplateVersion::where('team_role_template_id', $template->team_role_template_id)
            ->where('version', $versionNumber)
            ->firstOrFail();

        return self::save($template, [
            'customizations' => $version->customizations ?? [],
            'layout' => $version->layout ?? self::defaultLayout(),
            'selected_template' => $version->selected_template,
        ], $user, false, true, 'Restored v' . $versionNumber);
    }
}
