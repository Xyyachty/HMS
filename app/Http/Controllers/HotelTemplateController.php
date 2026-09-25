<?php

namespace App\Http\Controllers;

use App\Exceptions\TemplateConflictException;
use App\Models\ActivityLog;
use App\Models\Task;
use App\Models\TeamRoleTemplateVersion;
use App\Support\HotelTemplateBuilder;
use App\Support\Notifier;
use App\Support\StudentGroupSync;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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

    /**
     * Run a write and turn its failure into an answer the builder can act on.
     *
     * A conflict comes back as 409 with the template as it now stands, so the
     * browser can rebase the student's edits on it. Anything else is logged and
     * comes back as 500 with a message that says nothing was changed, which is
     * true: every write runs in one transaction, so a failure rolls all of it back.
     *
     * @return array{0: mixed, 1: ?\Illuminate\Http\JsonResponse}
     */
    private function attemptWrite(callable $write): array
    {
        try {
            return [$write(), null];
        } catch (TemplateConflictException $e) {
            return [null, response()->json([
                'error' => 'A teammate saved changes to this page while you were editing. Your edits are still here — check them and save again.',
                'conflict' => true,
                'template' => HotelTemplateBuilder::payload($e->current->fresh(), true),
            ], 409)];
        } catch (ModelNotFoundException $e) {
            // A version that does not exist is a 404, not a failed save.
            throw $e;
        } catch (\Throwable $e) {
            report($e);

            return [null, response()->json([
                'error' => 'Your changes could not be saved, and nothing was changed on the server. They are still on your screen — try again in a moment.',
            ], 500)];
        }
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
                'error' => 'You can only edit your own assigned role template.',
            ], 403);
        }

        $data = $request->validate([
            'customizations' => ['sometimes', 'array'],
            'layout' => ['sometimes', 'array'],
            'selected_template' => ['sometimes', 'nullable', 'string', 'max:20'],
            'publish' => ['sometimes', 'boolean'],
            'label' => ['sometimes', 'nullable', 'string', 'max:120'],
            'snapshot' => ['sometimes', 'boolean'],
            'base_revision' => ['sometimes', 'nullable', 'integer', 'min:0'],
        ]);

        if (!empty($data['selected_template'])
            && !\App\Support\HotelConceptDesk::hasApprovedConcept($membership->group_name, (int) $membership->faculty_id)
        ) {
            return response()->json([
                'error' => 'Your faculty has not approved a hotel concept yet. Wait for approval before picking a Default Template.',
            ], 403);
        }

        $template = HotelTemplateBuilder::ensureTemplate($membership, $role);
        [$saved, $failed] = $this->attemptWrite(fn () => HotelTemplateBuilder::save(
            $template,
            $data,
            $user,
            (bool) ($data['publish'] ?? false),
            array_key_exists('snapshot', $data) ? (bool) $data['snapshot'] : true,
            $data['label'] ?? null
        ));
        if ($failed) {
            return $failed;
        }

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
            'base_revision' => ['sometimes', 'nullable', 'integer', 'min:0'],
        ]);

        $template = HotelTemplateBuilder::ensureTemplate($membership, $role);
        [$saved, $failed] = $this->attemptWrite(fn () => HotelTemplateBuilder::autosave($template, $data, $user));
        if ($failed) {
            return $failed;
        }

        return response()->json([
            'success' => true,
            'template' => HotelTemplateBuilder::payload($saved, true),
        ]);
    }

    /**
     * Hand this role's website work to faculty for review.
     *
     * Deliberately not the same action as saving. Autosave and Save keep a draft
     * and tell nobody; this one freezes the work as handed in, closes the role's
     * open tasks against that snapshot and notifies the faculty — the same thing
     * ticking a task off in the student dashboard does, done from the builder
     * where the work actually happens.
     *
     * The draft is written first, so a student who types and submits in one
     * breath submits what is on their screen rather than the last autosave.
     */
    public function submit(Request $request, string $role)
    {
        [$user, $membership, $error] = $this->resolveContext($request, $role);
        if ($error) {
            return $error;
        }

        if (!HotelTemplateBuilder::canEdit($user, $membership, $role)) {
            return response()->json([
                'error' => 'You can only submit your own assigned role template.',
            ], 403);
        }

        $data = $request->validate([
            'customizations' => ['sometimes', 'array'],
            'layout' => ['sometimes', 'array'],
            'base_revision' => ['sometimes', 'nullable', 'integer', 'min:0'],
        ]);

        $template = HotelTemplateBuilder::ensureTemplate($membership, $role);
        [$saved, $failed] = $this->attemptWrite(fn () => HotelTemplateBuilder::save($template, $data, $user, false, false, null));
        if ($failed) {
            return $failed;
        }

        $snapshotId = HotelTemplateBuilder::snapshotForReview(
            $saved,
            $user,
            'Submitted: ' . (HotelTemplateBuilder::ROLES[$role] ?? $role) . ' website'
        );

        // The team's own open rows for this role. The hotel concept is excluded
        // for the reason it is excluded everywhere else: it closes by submitting
        // the concepts themselves, not by handing in a website.
        $tasks = Task::where('faculty_id', $membership->faculty_id)
            ->where('role', $role)
            ->where('status', 'active')
            ->whereNull('kind')
            ->where(function ($q) use ($membership) {
                $q->whereRaw('LOWER(group_name) = LOWER(?)', [(string) $membership->group_name])
                    ->orWhereNull('group_name');
            })
            // Rows a teammate already holds are theirs to submit, not this
            // student's. An unclaimed row stays open to whoever hands it in.
            ->where(function ($q) use ($user, $membership) {
                $q->whereNull('assigned_to')->orWhere('assigned_to', $user->user_id);
            })
            ->where(function ($q) use ($membership) {
                $q->whereNull('student_id')->orWhere('student_id', $membership->student_id);
            })
            ->get();

        // Every task is handed in or none is: a failure halfway used to leave
        // some archived and the rest open, with no way to tell from the screen.
        // Faculty are told only once the whole hand-in has committed.
        try {
            DB::transaction(function () use ($tasks, $saved, $snapshotId, $membership, $user, $role) {
                foreach ($tasks as $task) {
                    $previousVersionId = $task->previous_version_id
                        ?: $task->submitted_version_id
                        ?: TeamRoleTemplateVersion::where('team_role_template_id', $saved->team_role_template_id)
                            ->when($snapshotId, fn ($q) => $q->where('team_role_template_version_id', '!=', $snapshotId))
                            ->orderByDesc('team_role_template_version_id')
                            ->value('team_role_template_version_id');

                    $task->update([
                        'status' => 'archived',
                        'student_id' => $membership->student_id,
                        'assigned_to' => $user->user_id,
                        'previous_version_id' => $previousVersionId,
                        'submitted_version_id' => $snapshotId ?: $task->submitted_version_id,
                    ]);

                    ActivityLog::record(
                        $user,
                        ActivityLog::TASK_SUBMITTED,
                        'Submitted task "' . $task->title . '" for the ' . $role . ' role from the website builder.'
                    );
                }
            });
        } catch (\Throwable $e) {
            report($e);

            return response()->json([
                'error' => 'Your work is saved, but the tasks could not be handed in. None of them were submitted — try Submit again.',
            ], 500);
        }

        foreach ($tasks as $task) {
            Notifier::taskSubmitted($user, $task, $user->name);
        }
        $submitted = $tasks->count();

        return response()->json([
            'success' => true,
            'submitted' => $submitted,
            // Nothing open to hand in is not a failure: the work is still frozen
            // and saved, so say what happened rather than reporting an error.
            'message' => $submitted > 0
                ? $submitted . ' task' . ($submitted === 1 ? '' : 's') . ' submitted for faculty review.'
                : 'Your work is saved and frozen for review. You have no open task for this role right now.',
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
        [$restored, $failed] = $this->attemptWrite(fn () => HotelTemplateBuilder::restoreVersion($template, $version, $user));
        if ($failed) {
            return $failed;
        }

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
}
