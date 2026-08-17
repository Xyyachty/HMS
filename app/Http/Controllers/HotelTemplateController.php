<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Student;
use App\Models\StudentGroup;
use App\Models\TeamRoleTemplateVersion;
use App\Models\UserInformation;
use App\Support\HotelTemplateBuilder;
use App\Support\Notifier;
use App\Support\StudentGroupSync;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class HotelTemplateController extends Controller
{
    private function resolveContext(Request $request, string $role)
    {
        if (!HotelTemplateBuilder::isValidRole($role)) {
            return [null, null, response()->json(['error' => 'Invalid role'], 422)];
        }

        $user = $request->user();
        $membership = HotelTemplateBuilder::membershipFor($user);
        if (!$membership) {
            return [null, null, response()->json(['error' => 'Join a team first'], 404)];
        }

        StudentGroupSync::heartbeat($user, $membership);

        return [$user, $membership, null];
    }

    public function show(Request $request, string $role)
    {
        [$user, $membership, $error] = $this->resolveContext($request, $role);
        if ($error) {
            return $error;
        }

        if (!HotelTemplateBuilder::canView($user, $membership, $role)) {
            return response()->json(['error' => 'Forbidden'], 403);
        }

        $template = HotelTemplateBuilder::ensureTemplate($membership, $role);
        $canEdit = HotelTemplateBuilder::canEdit($user, $membership, $role);

        return response()->json(HotelTemplateBuilder::payload($template, $canEdit));
    }

    public function sync(Request $request, string $role)
    {
        return $this->show($request, $role);
    }

    public function save(Request $request, string $role)
    {
        [$user, $membership, $error] = $this->resolveContext($request, $role);
        if ($error) {
            return $error;
        }

        if (!HotelTemplateBuilder::canEdit($user, $membership, $role)) {
            return response()->json([
                'error' => 'You can only edit your assigned role template unless faculty grants permission.',
            ], 403);
        }

        $data = $request->validate([
            'customizations' => ['sometimes', 'array'],
            'layout' => ['sometimes', 'array'],
            'selected_template' => ['sometimes', 'nullable', 'string', 'max:20'],
            'publish' => ['sometimes', 'boolean'],
            'label' => ['sometimes', 'nullable', 'string', 'max:120'],
            'snapshot' => ['sometimes', 'boolean'],
        ]);

        $template = HotelTemplateBuilder::ensureTemplate($membership, $role);
        $saved = HotelTemplateBuilder::save(
            $template,
            $data,
            $user,
            (bool) ($data['publish'] ?? false),
            array_key_exists('snapshot', $data) ? (bool) $data['snapshot'] : true,
            $data['label'] ?? null
        );

        // Explicit saves are important work; autosave is deliberately not logged.
        ActivityLog::record(
            $user,
            ActivityLog::WEBSITE_CUSTOMIZED,
            'Saved website customizations for the ' . $role . ' module'
                . (($data['publish'] ?? false) ? ' and published them to the team.' : '.')
        );

        // Only publishing is worth telling faculty about — autosave and draft saves
        // fire constantly and would bury the feed.
        if ($data['publish'] ?? false) {
            Notifier::sitePublished(
                $user,
                (string) $membership->group_name,
                (int) $membership->faculty_id,
                HotelTemplateBuilder::ROLES[$role] ?? $role
            );
        }

        return response()->json([
            'success' => true,
            'template' => HotelTemplateBuilder::payload($saved, true),
        ]);
    }

    public function autosave(Request $request, string $role)
    {
        [$user, $membership, $error] = $this->resolveContext($request, $role);
        if ($error) {
            return $error;
        }

        if (!HotelTemplateBuilder::canEdit($user, $membership, $role)) {
            return response()->json(['error' => 'Forbidden'], 403);
        }

        $data = $request->validate([
            'customizations' => ['sometimes', 'array'],
            'layout' => ['sometimes', 'array'],
            'selected_template' => ['sometimes', 'nullable', 'string', 'max:20'],
        ]);

        $template = HotelTemplateBuilder::ensureTemplate($membership, $role);
        $saved = HotelTemplateBuilder::autosave($template, $data, $user);

        return response()->json([
            'success' => true,
            'template' => HotelTemplateBuilder::payload($saved, true),
        ]);
    }

    public function versions(Request $request, string $role)
    {
        [$user, $membership, $error] = $this->resolveContext($request, $role);
        if ($error) {
            return $error;
        }

        if (!HotelTemplateBuilder::canView($user, $membership, $role)) {
            return response()->json(['error' => 'Forbidden'], 403);
        }

        $template = HotelTemplateBuilder::ensureTemplate($membership, $role);
        $versions = TeamRoleTemplateVersion::where('team_role_template_id', $template->team_role_template_id)
            ->orderByDesc('version')
            ->limit(30)
            ->get()
            ->map(fn ($v) => [
                'version' => $v->version,
                'label' => $v->label,
                'is_published' => (bool) $v->is_published,
                'created_at' => optional($v->created_at)->toIso8601String(),
                'created_by' => $v->created_by,
            ]);

        return response()->json([
            'role' => $role,
            'current_version' => $template->version,
            'versions' => $versions,
            'can_edit' => HotelTemplateBuilder::canEdit($user, $membership, $role),
        ]);
    }

    public function restore(Request $request, string $role, int $version)
    {
        [$user, $membership, $error] = $this->resolveContext($request, $role);
        if ($error) {
            return $error;
        }

        if (!HotelTemplateBuilder::canEdit($user, $membership, $role)) {
            return response()->json(['error' => 'Forbidden'], 403);
        }

        $template = HotelTemplateBuilder::ensureTemplate($membership, $role);
        $restored = HotelTemplateBuilder::restoreVersion($template, $version, $user);

        ActivityLog::record(
            $user,
            ActivityLog::TEMPLATE_RESTORED,
            'Restored version ' . $version . ' of the ' . $role . ' website template.'
        );

        return response()->json([
            'success' => true,
            'template' => HotelTemplateBuilder::payload($restored, true),
        ]);
    }

    /** Faculty: list / toggle cross-role edit grants for a team member. */
    public function facultyGrants(Request $request)
    {
        $faculty = $request->user()?->faculty;
        if (!$faculty) {
            return response()->json(['error' => 'Faculty only'], 403);
        }

        $data = $request->validate([
            'group_name' => ['required', 'string', 'max:255'],
            'student_id' => ['required', 'integer', Rule::exists('user_information', 'user_information_id')->where('user_type', UserInformation::TYPE_STUDENT)],
        ]);

        $student = Student::whereKey($data['student_id'])
            ->where('faculty_id', $faculty->user_information_id)
            ->firstOrFail();

        $membership = StudentGroup::where('faculty_id', $faculty->user_information_id)
            ->where('group_name', $data['group_name'])
            ->where('student_id', $student->user_information_id)
            ->first();

        if (!$membership) {
            return response()->json(['error' => 'Student is not on that team'], 422);
        }

        $grants = \App\Models\TeamTemplateEditGrant::where('faculty_id', $faculty->user_information_id)
            ->where('group_name', $data['group_name'])
            ->where('student_id', $student->user_information_id)
            ->pluck('role')
            ->all();

        return response()->json([
            'roles' => HotelTemplateBuilder::ROLES,
            'granted' => $grants,
            'owned' => $membership->roles()->pluck('role')->all(),
        ]);
    }

    public function facultyGrantStore(Request $request)
    {
        $faculty = $request->user()?->faculty;
        if (!$faculty) {
            return response()->json(['error' => 'Faculty only'], 403);
        }

        $data = $request->validate([
            'group_name' => ['required', 'string', 'max:255'],
            'student_id' => ['required', 'integer', Rule::exists('user_information', 'user_information_id')->where('user_type', UserInformation::TYPE_STUDENT)],
            'role' => ['required', 'string'],
            'grant' => ['required', 'boolean'],
        ]);

        if (!HotelTemplateBuilder::isValidRole($data['role'])) {
            return response()->json(['error' => 'Invalid role'], 422);
        }

        $student = Student::whereKey($data['student_id'])
            ->where('faculty_id', $faculty->user_information_id)
            ->firstOrFail();

        $membership = StudentGroup::where('faculty_id', $faculty->user_information_id)
            ->where('group_name', $data['group_name'])
            ->where('student_id', $student->user_information_id)
            ->first();

        if (!$membership) {
            return response()->json(['error' => 'Student is not on that team'], 422);
        }

        // Dummy membership object for grant helpers (needs faculty_id + group_name + group_id)
        $ctx = new StudentGroup([
            'faculty_id' => $faculty->user_information_id,
            'group_name' => $data['group_name'],
            'student_id' => $student->user_information_id,
            'group_id' => $membership->group_id,
        ]);

        if ($data['grant']) {
            HotelTemplateBuilder::grantEdit($ctx, $student, $data['role'], $request->user());
        } else {
            HotelTemplateBuilder::revokeEdit($ctx, $student, $data['role']);
        }

        ActivityLog::record(
            $request->user(),
            ActivityLog::PERMISSION_GRANTED,
            ($data['grant'] ? 'Granted' : 'Revoked') . ' ' . $data['role']
                . ' edit access for a member of team "' . $data['group_name'] . '".'
        );

        return response()->json(['success' => true]);
    }
}
