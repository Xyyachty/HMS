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

    public static function payload(TeamRoleTemplate $template, bool $canEdit): array
    {
        return [
            'id' => $template->id,
            'role' => $template->role,
            'role_label' => self::ROLES[$template->role] ?? $template->role,
            'selected_template' => $template->selected_template,
            'customizations' => $template->customizations ?? [],
            'layout' => $template->layout ?? self::defaultLayout(),
            'is_published' => (bool) $template->is_published,
            'version' => (int) $template->version,
            'updated_at' => optional($template->updated_at)->toIso8601String(),
            'sync_version' => optional($template->updated_at)->timestamp ?? 0,
            'can_edit' => $canEdit,
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
                $template->customizations = $data['customizations'];
            }
            if (array_key_exists('layout', $data)) {
                $template->layout = $data['layout'];
            }
            if (array_key_exists('selected_template', $data) && $data['selected_template'] !== null) {
                $template->selected_template = (string) $data['selected_template'];
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

            // Keep legacy group_settings in sync (selected template + publish state)
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
                $template->customizations = $data['customizations'];
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
     * Mirror Front Desk template state into group_settings (legacy / group-wide lock).
     */
    public static function syncGroupSettings(TeamRoleTemplate $template): void
    {
        if ($template->role !== 'front_desk') {
            return;
        }

        $payload = [
            'selected_template' => $template->selected_template,
            'customizations' => $template->customizations ?? [],
            'is_published' => (bool) $template->is_published,
        ];

        // Only overwrite selected_template when a value is present
        if ($template->selected_template === null || $template->selected_template === '') {
            unset($payload['selected_template']);
        }

        GroupSettings::updateOrCreate(
            [
                'group_name' => $template->group_name,
                'faculty_id' => $template->faculty_id,
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
