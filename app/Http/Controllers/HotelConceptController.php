<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\HotelConcept;
use App\Models\HotelConceptRevision;
use App\Models\StudentGroup;
use App\Models\User;
use App\Support\HotelConceptDesk;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

/**
 * Front Desk's first task: propose the hotel's concept.
 *
 * The concept belongs to the team, not to the member who typed it. Front Desk
 * proposes the first version, then every member may improve it, and Front Desk
 * hands it to faculty. Each save leaves a revision behind, so the team and their
 * faculty can see who changed what, and when.
 *
 * Who may write at any moment is decided by HotelConceptDesk, which also owns
 * every status change; this controller owns the reads and the edit history.
 */
class HotelConceptController extends Controller
{
    /** The role that owns this task. Kept as an alias for existing callers. */
    public const OWNING_ROLE = HotelConceptDesk::OWNING_ROLE;

    /**
     * The concept for one team, with its history newest-first.
     *
     * Shared by the student dashboard, the student history endpoint and the
     * faculty modal so all three read the same rows the same way.
     */
    public static function forTeam(?string $groupName, ?int $facultyId, int $historyLimit = 50): array
    {
        if (!$groupName || !$facultyId) {
            return ['concept' => null, 'history' => collect()];
        }

        $concept = HotelConcept::with(['creator', 'editor', 'submitter', 'reviewer'])
            ->where('group_name', $groupName)
            ->where('faculty_id', $facultyId)
            ->first();

        $history = $concept
            ? $concept->revisions()->with('user')->latest('created_at')->latest('hotel_concept_revision_id')->take($historyLimit)->get()
            : collect();

        return ['concept' => $concept, 'history' => $history];
    }

    /**
     * Create or update the authenticated student's team concept.
     *
     * The gate reads the stored concept first, because who may write depends on
     * what state it is in: Front Desk alone proposes the first version, the whole
     * team improves it afterwards, and nobody edits it while faculty holds it.
     */
    public function store(Request $request)
    {
        $authUser = auth()->user();
        $student = $authUser?->student;

        $membership = $student
            ? StudentGroup::with('roles')->where('student_id', $student->user_information_id)->first()
            : null;

        if (!$membership) {
            return $this->refuse($request, 'You are not on a team yet, so there is no concept to propose.');
        }

        $roles = $membership->roles->pluck('role')->all();
        $existing = self::forTeam($membership->group_name, (int) $membership->faculty_id, 1)['concept'];

        if (!HotelConceptDesk::canEdit($existing, $roles)) {
            return $this->refuse($request, HotelConceptDesk::editRefusal($existing, $roles));
        }

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:150'],
            'description' => ['required', 'string', 'max:5000'],
            'hotel_type' => ['required', Rule::in(array_keys(HotelConcept::HOTEL_TYPES))],
        ]);

        $saved = DB::transaction(function () use ($validated, $membership, $authUser, $roles) {
            // Locked for the length of the save: two Front Desk members hitting
            // Save at once must not both read "no concept yet" and insert one.
            $concept = HotelConcept::where('group_name', $membership->group_name)
                ->where('faculty_id', $membership->faculty_id)
                ->lockForUpdate()
                ->first();

            $isNew = $concept === null;
            $changes = [];

            // The gate above read outside the lock. A teammate who submitted in the
            // meantime must win, or this save would edit a concept faculty is
            // already reading.
            if ($concept && !HotelConceptDesk::canEdit($concept, $roles)) {
                return null;
            }

            if ($isNew) {
                $concept = new HotelConcept([
                    'group_name' => $membership->group_name,
                    'faculty_id' => $membership->faculty_id,
                    'group_id' => $membership->group_id,
                    'created_by' => $authUser->user_id,
                ]);
            } else {
                foreach (array_keys(HotelConcept::TRACKED_FIELDS) as $field) {
                    if ((string) $concept->{$field} !== (string) $validated[$field]) {
                        $changes[$field] = ['from' => $concept->{$field}, 'to' => $validated[$field]];
                    }
                }

                // Nothing moved: no revision, no log, no misleading "edited" entry.
                if ($changes === []) {
                    return false;
                }
            }

            $concept->fill([
                'title' => $validated['title'],
                'description' => $validated['description'],
                'hotel_type' => $validated['hotel_type'],
                'updated_by' => $authUser->user_id,
            ]);
            // A team created before groups existed can still be missing this.
            $concept->group_id = $concept->group_id ?: $membership->group_id;
            $concept->save();

            HotelConceptRevision::create([
                'hotel_concept_id' => $concept->hotel_concept_id,
                'user_id' => $authUser->user_id,
                'editor_name' => self::displayName($authUser),
                'action' => $isNew ? HotelConceptRevision::CREATED : HotelConceptRevision::UPDATED,
                'field_changes' => $changes,
                'title' => $concept->title,
                'description' => $concept->description,
                'hotel_type' => $concept->hotel_type,
            ]);

            ActivityLog::record(
                $authUser,
                $isNew ? ActivityLog::CONCEPT_CREATED : ActivityLog::CONCEPT_UPDATED,
                ($isNew ? 'Proposed the hotel concept "' : 'Updated the hotel concept "')
                    . $concept->title . '" for team "' . $membership->group_name . '"'
                    . ($isNew
                        ? '.'
                        : ' — changed ' . implode(', ', array_map(
                            fn ($field) => HotelConcept::TRACKED_FIELDS[$field] ?? $field,
                            array_keys($changes)
                        )) . '.')
            );

            return true;
        });

        // null means a teammate submitted while this form was open.
        if ($saved === null) {
            return $this->refuse(
                $request,
                'A teammate submitted this concept to your faculty while you were editing, so your changes were not saved.'
            );
        }

        $message = $saved
            ? 'Hotel concept saved.'
            : 'No changes to save — the concept is already up to date.';

        // The dashboard saves over fetch and repaints the header, the task card and
        // the history from this payload, so it hands back stored rows, not input.
        if ($request->expectsJson()) {
            return response()->json(array_merge(
                self::payload(self::forTeam($membership->group_name, (int) $membership->faculty_id), $roles),
                ['saved' => $saved, 'message' => $message]
            ));
        }

        return back()->with('success', $message);
    }

    /**
     * Hand the team's concept to faculty for review.
     *
     * Front Desk owns this step — they own the task — even though every member may
     * have edited what is being handed in.
     */
    public function submit(Request $request)
    {
        $authUser = auth()->user();
        $student = $authUser?->student;

        $membership = $student
            ? StudentGroup::with('roles')->where('student_id', $student->user_information_id)->first()
            : null;

        if (!$membership) {
            return $this->refuse($request, 'You are not on a team yet, so there is no concept to submit.');
        }

        $roles = $membership->roles->pluck('role')->all();

        // Locked for the same reason as the save: two members hitting Submit at
        // once must not both write a submission revision.
        $result = DB::transaction(function () use ($membership, $authUser, $roles) {
            $concept = HotelConcept::where('group_name', $membership->group_name)
                ->where('faculty_id', $membership->faculty_id)
                ->lockForUpdate()
                ->first();

            if (!HotelConceptDesk::canSubmit($concept, $roles)) {
                if (!$concept) {
                    return 'There is no hotel concept yet — propose one before submitting it.';
                }

                if (!in_array(HotelConceptDesk::OWNING_ROLE, $roles, true)) {
                    return 'Only the Front Desk members of this team can submit the concept to your faculty.';
                }

                return HotelConceptDesk::status($concept) === HotelConceptDesk::STATUS_APPROVED
                    ? 'Your faculty already approved this concept.'
                    : 'This concept is already with your faculty for review.';
            }

            HotelConceptDesk::submit($concept, $membership, $authUser);

            return true;
        });

        if ($result !== true) {
            return $this->refuse($request, (string) $result);
        }

        $message = 'Hotel concept submitted to your faculty for review.';

        if ($request->expectsJson()) {
            return response()->json(array_merge(
                self::payload(self::forTeam($membership->group_name, (int) $membership->faculty_id), $roles),
                ['saved' => true, 'message' => $message]
            ));
        }

        return back()->with('success', $message);
    }

    /** A refusal the dashboard can read either as JSON or as a flashed error. */
    private function refuse(Request $request, string $message)
    {
        if ($request->expectsJson()) {
            return response()->json(['message' => $message], 403);
        }

        return back()->withErrors(['hotel_concept' => $message]);
    }

    /** The authenticated student's own team concept + history, as JSON. */
    public function history()
    {
        $student = auth()->user()?->student;
        $membership = $student
            ? StudentGroup::with('roles')->where('student_id', $student->user_information_id)->first()
            : null;

        if (!$membership) {
            return response()->json([
                'concept' => null,
                'can_edit' => false,
                'can_submit' => false,
                'history' => [],
            ]);
        }

        return response()->json(self::payload(
            self::forTeam($membership->group_name, (int) $membership->faculty_id),
            $membership->roles->pluck('role')->all()
        ));
    }

    /** One of the faculty's own teams — used by the Team Details modal. */
    public function facultyHistory(string $groupName)
    {
        $facultyId = auth()->user()?->faculty?->user_information_id;
        if (!$facultyId) {
            return response()->json(['error' => 'Only faculty can read this.'], 403);
        }

        // A faculty reads their own teams only; group names are not unique across faculty.
        $ownsTeam = StudentGroup::where('faculty_id', $facultyId)
            ->where('group_name', $groupName)
            ->exists();

        if (!$ownsTeam) {
            return response()->json(['error' => 'That team is not yours.'], 403);
        }

        return response()->json(self::payload(self::forTeam($groupName, (int) $facultyId)));
    }

    /**
     * Any team, for the dean.
     *
     * The dean oversees every faculty, so the team is addressed by faculty and
     * group name together — group names repeat across faculty.
     */
    public function deanHistory(Request $request)
    {
        if (auth()->user()?->role !== 'dean') {
            return response()->json(['error' => 'Only the dean can read this.'], 403);
        }

        $validated = $request->validate([
            'faculty_id' => ['required', 'integer'],
            'group_name' => ['required', 'string'],
        ]);

        return response()->json(self::payload(
            self::forTeam($validated['group_name'], (int) $validated['faculty_id'])
        ));
    }

    /**
     * Wire shape shared by the JSON endpoints.
     *
     * $viewerRoles is the team roles of whoever is asking, so the student dashboard
     * can repaint its Edit and Submit buttons from the same response that repaints
     * the concept. Faculty and the dean pass nothing: they never edit it, and both
     * flags come back false.
     */
    public static function payload(array $team, array $viewerRoles = []): array
    {
        $concept = $team['concept'];

        return [
            'concept' => $concept ? [
                'title' => $concept->title,
                'description' => $concept->description,
                'hotel_type' => $concept->hotel_type,
                'hotel_type_label' => $concept->hotel_type_label,
                'status' => HotelConceptDesk::status($concept),
                'status_label' => HotelConceptDesk::statusLabel($concept),
                'revision_count' => (int) $concept->revision_count,
                'faculty_feedback' => $concept->faculty_feedback,
                'submitted_at' => optional($concept->submitted_at)->format('M d, Y g:i A'),
                'submitted_by' => $concept->submitter ? self::displayName($concept->submitter) : null,
                'reviewed_at' => optional($concept->reviewed_at)->format('M d, Y g:i A'),
                'reviewed_by' => $concept->reviewer ? self::displayName($concept->reviewer) : null,
                'updated_at' => optional($concept->updated_at)->format('M d, Y g:i A'),
                'updated_by' => $concept->editor ? self::displayName($concept->editor) : null,
                'created_by' => $concept->creator ? self::displayName($concept->creator) : null,
            ] : null,
            'can_edit' => HotelConceptDesk::canEdit($concept, $viewerRoles),
            'can_submit' => HotelConceptDesk::canSubmit($concept, $viewerRoles),
            'history' => $team['history']->map->toPortalArray()->values(),
        ];
    }

    /** Last First Middle, the same shape the portals print elsewhere. */
    public static function displayName(?User $user): string
    {
        if (!$user) {
            return 'Unknown member';
        }

        $name = trim(implode(' ', array_filter([
            $user->last_name ?? null,
            $user->first_name ?? null,
            $user->middle_name ?? null,
        ])));

        return $name !== '' ? $name : ($user->name ?? 'Unknown member');
    }
}
