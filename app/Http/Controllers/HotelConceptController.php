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
 * Front Desk's first task: propose the team's two hotel concepts.
 *
 * The concepts belong to the team, not to the member who typed them. Front Desk
 * proposes each first version, then every member may improve either, and Front
 * Desk hands both to faculty at once. Every save leaves a revision behind, so the
 * team and their faculty can see who changed what, and when.
 *
 * Who may write at any moment is decided by HotelConceptDesk, which also owns
 * every status change; this controller owns the reads and the edit history.
 */
class HotelConceptController extends Controller
{
    /** The role that owns this task. Kept as an alias for existing callers. */
    public const OWNING_ROLE = HotelConceptDesk::OWNING_ROLE;

    /** History rows kept per concept. */
    private const HISTORY_LIMIT = 50;

    /**
     * A team's concepts, each with its own history newest-first.
     *
     * Shared by the student dashboard, the student history endpoint, the faculty
     * review dialog and the dean modal, so all four read the same rows the same
     * way. The histories are loaded per concept because that is how they are read —
     * each concept's card shows its own.
     */
    public static function forTeam(?string $groupName, ?int $facultyId, int $historyLimit = self::HISTORY_LIMIT): array
    {
        $concepts = HotelConceptDesk::conceptsFor($groupName, $facultyId);

        $histories = [];
        foreach ($concepts as $concept) {
            $histories[(int) $concept->slot] = $concept->revisions()
                ->with('user')
                ->latest('created_at')
                ->latest('hotel_concept_revision_id')
                ->take($historyLimit)
                ->get();
        }

        return ['concepts' => $concepts, 'histories' => $histories];
    }

    /** One slot's concept, or null when nobody has written it yet. */
    public static function conceptAt(array $team, int $slot): ?HotelConcept
    {
        return $team['concepts']->firstWhere('slot', $slot);
    }

    /**
     * Create or update one of the authenticated student's team concepts.
     *
     * The slot says which of the two is being written. The gate reads that slot's
     * stored concept first, because who may write depends on what state it is in:
     * Front Desk alone proposes each first version, the whole team improves it
     * afterwards, and nobody edits it while faculty holds it.
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

        $validated = $request->validate([
            'slot' => ['required', 'integer', Rule::in(HotelConceptDesk::SLOTS)],
            'title' => ['required', 'string', 'max:150'],
            'description' => ['required', 'string', 'max:5000'],
            'hotel_type' => ['required', Rule::in(array_keys(HotelConcept::HOTEL_TYPES))],
        ]);

        $slot = (int) $validated['slot'];
        $roles = $membership->roles->pluck('role')->all();
        $existing = self::conceptAt(
            self::forTeam($membership->group_name, (int) $membership->faculty_id, 1),
            $slot
        );

        if (!HotelConceptDesk::canEdit($existing, $roles)) {
            return $this->refuse($request, HotelConceptDesk::editRefusal($existing, $roles));
        }

        $saved = DB::transaction(function () use ($validated, $slot, $membership, $authUser, $roles) {
            // Locked for the length of the save: two Front Desk members hitting Save
            // on the same slot must not both read "nothing here yet" and insert.
            $concept = HotelConcept::where('group_name', $membership->group_name)
                ->where('faculty_id', $membership->faculty_id)
                ->where('slot', $slot)
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
                    'slot' => $slot,
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
                ($isNew ? 'Proposed ' : 'Updated ')
                    . HotelConceptDesk::slotLabel($slot) . ' "'
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
            ? HotelConceptDesk::slotLabel($slot) . ' saved.'
            : 'No changes to save — ' . HotelConceptDesk::slotLabel($slot) . ' is already up to date.';

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
     * Hand both of the team's concepts to faculty for review.
     *
     * Front Desk owns this step — they own the task — even though every member may
     * have edited what is being handed in. Both go at once, because the two exist
     * so faculty can weigh them against each other; a concept already approved is
     * left where it is.
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
            $concepts = HotelConcept::where('group_name', $membership->group_name)
                ->where('faculty_id', $membership->faculty_id)
                ->orderBy('slot')
                ->lockForUpdate()
                ->get();

            if (!HotelConceptDesk::canSubmit($concepts, $roles)) {
                return HotelConceptDesk::submitRefusal($concepts, $roles);
            }

            $count = HotelConceptDesk::submittableConcepts($concepts)->count();
            HotelConceptDesk::submit($concepts, $membership, $authUser);

            return $count;
        });

        if (!is_int($result)) {
            return $this->refuse($request, (string) $result);
        }

        $message = $result === 1
            ? 'Hotel concept submitted to your faculty for review.'
            : 'Both hotel concepts submitted to your faculty for review.';

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

    /** The authenticated student's own team concepts + histories, as JSON. */
    public function history()
    {
        $student = auth()->user()?->student;
        $membership = $student
            ? StudentGroup::with('roles')->where('student_id', $student->user_information_id)->first()
            : null;

        if (!$membership) {
            return response()->json(self::payload(['concepts' => collect(), 'histories' => []]));
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

        // The Team Details tab posts verdicts through the existing task-feedback
        // route, so it needs the concept task's id alongside the concepts.
        $taskId = HotelConceptDesk::teamTasks($groupName, (int) $facultyId)->first()?->task_id;

        return response()->json(array_merge(
            self::payload(self::forTeam($groupName, (int) $facultyId)),
            ['task_id' => $taskId]
        ));
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
     * One entry per slot, always both of them, whether or not a concept has been
     * written there — the portals render the empty slot as an invitation to fill
     * it, so they need to be told it exists.
     *
     * $viewerRoles is the team roles of whoever is asking, so the student dashboard
     * can repaint each slot's Edit button and the shared Submit button from the same
     * response that repaints the concepts. Faculty and the dean pass nothing: they
     * never edit, and every flag comes back false.
     */
    public static function payload(array $team, array $viewerRoles = []): array
    {
        $concepts = $team['concepts'] ?? collect();
        $histories = $team['histories'] ?? [];

        $slots = [];
        foreach (HotelConceptDesk::SLOTS as $slot) {
            $concept = $concepts->firstWhere('slot', $slot);
            $history = $histories[$slot] ?? collect();

            $slots[] = [
                'slot' => $slot,
                'slot_label' => HotelConceptDesk::slotLabel($slot),
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
                // Faculty state, not viewer state — true for any caller once both
                // slots exist and nobody has been chosen yet.
                'can_review' => HotelConceptDesk::canReview($concepts, $slot),
                'history' => collect($history)->map->toPortalArray()->values(),
            ];
        }

        return [
            'slots' => $slots,
            // One button for the pair: they are handed in together.
            'can_submit' => HotelConceptDesk::canSubmit($concepts, $viewerRoles),
            'all_slots_filled' => HotelConceptDesk::allSlotsFilled($concepts),
            'decided' => HotelConceptDesk::isDecided($concepts),
            'approved_slot' => HotelConceptDesk::approvedConcept($concepts)?->slot,
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
