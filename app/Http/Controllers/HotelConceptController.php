<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\HotelConcept;
use App\Models\HotelConceptRevision;
use App\Models\StudentGroup;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

/**
 * Front Desk's first task: propose the hotel's concept.
 *
 * The concept belongs to the team, not to the member who typed it — every member
 * reads it, Front Desk members write it, and each save leaves a revision behind so
 * the team and their faculty can see who changed what, and when.
 */
class HotelConceptController extends Controller
{
    /** The role that owns this task. */
    public const OWNING_ROLE = 'front_desk';

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

        $concept = HotelConcept::with(['creator', 'editor'])
            ->where('group_name', $groupName)
            ->where('faculty_id', $facultyId)
            ->first();

        $history = $concept
            ? $concept->revisions()->with('user')->latest('created_at')->latest('hotel_concept_revision_id')->take($historyLimit)->get()
            : collect();

        return ['concept' => $concept, 'history' => $history];
    }

    /** Create or update the authenticated student's team concept. */
    public function store(Request $request)
    {
        $authUser = auth()->user();
        $student = $authUser?->student;

        $membership = $student
            ? StudentGroup::with('roles')->where('student_id', $student->student_id)->first()
            : null;

        if (!$membership) {
            return $this->refuse($request, 'You are not on a team yet, so there is no concept to propose.');
        }

        $roles = $membership->roles->pluck('role')->all();
        if (!in_array(self::OWNING_ROLE, $roles, true)) {
            return $this->refuse($request, 'Only the Front Desk team can propose or edit the hotel concept.');
        }

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:150'],
            'description' => ['required', 'string', 'max:5000'],
            'hotel_type' => ['required', Rule::in(array_keys(HotelConcept::HOTEL_TYPES))],
        ]);

        $saved = DB::transaction(function () use ($validated, $membership, $authUser) {
            // Locked for the length of the save: two Front Desk members hitting
            // Save at once must not both read "no concept yet" and insert one.
            $concept = HotelConcept::where('group_name', $membership->group_name)
                ->where('faculty_id', $membership->faculty_id)
                ->lockForUpdate()
                ->first();

            $isNew = $concept === null;
            $changes = [];

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

        $message = $saved
            ? 'Hotel concept saved.'
            : 'No changes to save — the concept is already up to date.';

        // The dashboard saves over fetch and repaints the header, the task card and
        // the history from this payload, so it hands back stored rows, not input.
        if ($request->expectsJson()) {
            return response()->json(array_merge(
                self::payload(self::forTeam($membership->group_name, (int) $membership->faculty_id)),
                ['saved' => $saved, 'message' => $message]
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
            ? StudentGroup::where('student_id', $student->student_id)->first()
            : null;

        if (!$membership) {
            return response()->json(['concept' => null, 'history' => []]);
        }

        return response()->json(self::payload(
            self::forTeam($membership->group_name, (int) $membership->faculty_id)
        ));
    }

    /** One of the faculty's own teams — used by the Team Details modal. */
    public function facultyHistory(string $groupName)
    {
        $facultyId = auth()->user()?->faculty?->faculty_id;
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

    /** Wire shape shared by the JSON endpoints. */
    private static function payload(array $team): array
    {
        $concept = $team['concept'];

        return [
            'concept' => $concept ? [
                'title' => $concept->title,
                'description' => $concept->description,
                'hotel_type' => $concept->hotel_type,
                'hotel_type_label' => $concept->hotel_type_label,
                'updated_at' => optional($concept->updated_at)->format('M d, Y g:i A'),
                'updated_by' => $concept->editor ? self::displayName($concept->editor) : null,
                'created_by' => $concept->creator ? self::displayName($concept->creator) : null,
            ] : null,
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
