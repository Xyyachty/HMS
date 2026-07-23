<?php

namespace App\Support;

use App\Models\GroupSettings;
use App\Models\Student;
use App\Models\StudentGroup;
use App\Models\TeamRoleTemplate;
use App\Models\TeamRoleTemplateVersion;
use App\Models\TeamTemplateEditGrant;
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

    /**
     * Hotel website pages each role may redesign.
     * Teammates still see the full merged site; edit is page-scoped.
     */
    public const ROLE_EDITABLE_PAGES = [
        'front_desk' => ['home'],
        'room_management' => ['rooms'],
        'restaurant_management' => ['restaurant'],
        'housekeeping' => ['experience'],
        'maintenance' => [],
    ];

    public const USER_ELEMENTS_KEY = '__userElements';
    public const DELETED_KEY = '__deleted';
    public const NAV_LINKS_KEY = '__navLinks';
    public const ROOMS_KEY = '__rooms';
    public const MENUS_KEY = '__menus';
    public const CARD_IMAGES_KEY = '__cardImages';

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

    public static function membershipFor(User $user): ?StudentGroup
    {
        $student = $user->student;
        if (!$student) {
            return null;
        }

        return StudentGroup::with('roles')->where('student_id', $student->id)->first();
    }

    public static function studentRoleKeys(StudentGroup $membership): array
    {
        return $membership->roles->pluck('role')->filter()->values()->all();
    }

    /**
     * Can this student edit this role's template?
     * True if they own the role OR faculty granted a cross-role edit grant.
     */
    public static function canEdit(User $user, StudentGroup $membership, string $role): bool
    {
        if (!self::isValidRole($role)) {
            return false;
        }

        if (in_array($role, self::studentRoleKeys($membership), true)) {
            return true;
        }

        $studentId = $user->student?->id;
        if (!$studentId) {
            return false;
        }

        return TeamTemplateEditGrant::query()
            ->where('faculty_id', $membership->faculty_id)
            ->where('group_name', $membership->group_name)
            ->where('student_id', $studentId)
            ->where('role', $role)
            ->exists();
    }

    public static function canView(User $user, StudentGroup $membership, string $role): bool
    {
        if (!self::isValidRole($role)) {
            return false;
        }

        // Any authenticated teammate (same faculty + group membership) can view
        return (int) ($user->student?->id) > 0
            && (int) $membership->faculty_id > 0
            && $membership->group_name !== '';
    }

    public static function ensureTemplate(StudentGroup $membership, string $role): TeamRoleTemplate
    {
        return TeamRoleTemplate::firstOrCreate(
            [
                'group_name' => $membership->group_name,
                'faculty_id' => $membership->faculty_id,
                'role' => $role,
            ],
            [
                'selected_template' => null, // Front Desk must choose Template 1 or 2
                'customizations' => [],
                'layout' => self::defaultLayout(),
                'is_published' => false,
                'version' => 1,
            ]
        );
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
     */
    public static function mergeTeamCustomizations(string $groupName, int $facultyId): array
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
            $chunk = is_array($row->customizations) ? $row->customizations : [];
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
                        $merged[self::DELETED_KEY] = array_values(array_unique(array_merge(
                            $merged[self::DELETED_KEY],
                            $value
                        )));
                    }
                    continue;
                }
                $merged[$key] = $value;
            }
        }

        return $merged;
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

            if ($key === self::CARD_IMAGES_KEY && is_array($value)
                && in_array($role, ['front_desk', 'restaurant_management', 'housekeeping', 'room_management'], true)) {
                $out[$key] = $value;
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

        return [
            'id' => $template->id,
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
                $template->customizations = self::filterCustomizationsForRole(
                    is_array($data['customizations']) ? $data['customizations'] : [],
                    $template->role
                );
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
                        ->where('id', '!=', $template->id)
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

            $template->updated_by = $user->id;
            $template->version = ((int) $template->version) + ($snapshot ? 1 : 0);
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

            // Keep legacy group_settings in sync (merged team site)
            self::syncGroupSettings($template);

            if ($snapshot) {
                TeamRoleTemplateVersion::create([
                    'team_role_template_id' => $template->id,
                    'version' => $template->version,
                    'selected_template' => $template->selected_template,
                    'customizations' => $template->customizations,
                    'layout' => $template->layout,
                    'is_published' => $template->is_published,
                    'label' => $label ?: ($publish ? 'Published' : 'Auto-save'),
                    'created_by' => $user->id,
                ]);
            }

            return $template->fresh();
        });
    }

    /** Lightweight auto-save: updates content without bumping version number every few seconds. */
    public static function autosave(TeamRoleTemplate $template, array $data, User $user): TeamRoleTemplate
    {
        return DB::transaction(function () use ($template, $data, $user) {
            if (array_key_exists('customizations', $data)) {
                $template->customizations = self::filterCustomizationsForRole(
                    is_array($data['customizations']) ? $data['customizations'] : [],
                    $template->role
                );
            }
            if (array_key_exists('layout', $data)) {
                $template->layout = $data['layout'];
            }
            if (array_key_exists('selected_template', $data) && $data['selected_template'] !== null) {
                $template->selected_template = (string) $data['selected_template'];
            }
            $template->updated_by = $user->id;
            $template->save();
            $template->touch();

            self::syncGroupSettings($template);

            return $template->fresh();
        });
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
            'customizations' => self::mergeTeamCustomizations($groupName, $facultyId),
            'is_published' => (bool) ($frontDesk?->is_published ?? $template->is_published),
        ];

        if ($selected !== null && $selected !== '') {
            $payload['selected_template'] = $selected;
        }

        GroupSettings::updateOrCreate(
            [
                'group_name' => $groupName,
                'faculty_id' => $facultyId,
            ],
            $payload
        );
    }

    public static function restoreVersion(TeamRoleTemplate $template, int $versionNumber, User $user): TeamRoleTemplate
    {
        $version = TeamRoleTemplateVersion::where('team_role_template_id', $template->id)
            ->where('version', $versionNumber)
            ->firstOrFail();

        return self::save($template, [
            'customizations' => $version->customizations ?? [],
            'layout' => $version->layout ?? self::defaultLayout(),
            'selected_template' => $version->selected_template,
        ], $user, false, true, 'Restored v' . $versionNumber);
    }

    public static function grantEdit(StudentGroup $membership, Student $student, string $role, User $facultyUser): TeamTemplateEditGrant
    {
        return TeamTemplateEditGrant::updateOrCreate(
            [
                'faculty_id' => $membership->faculty_id,
                'group_name' => $membership->group_name,
                'student_id' => $student->id,
                'role' => $role,
            ],
            ['granted_by' => $facultyUser->id]
        );
    }

    public static function revokeEdit(StudentGroup $membership, Student $student, string $role): void
    {
        TeamTemplateEditGrant::where('faculty_id', $membership->faculty_id)
            ->where('group_name', $membership->group_name)
            ->where('student_id', $student->id)
            ->where('role', $role)
            ->delete();
    }
}
