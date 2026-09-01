<?php

namespace App\Support;

use App\Models\ActivityLog;
use App\Models\HotelConcept;
use App\Models\HotelConceptRevision;
use App\Models\Student;
use App\Models\StudentGroup;
use App\Models\Task;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * The hotel concept lifecycle: who may write, and every state change.
 *
 * A team proposes two concepts, and each one moves through states of its own:
 *
 *   draft          proposed; Front Desk keeps improving it
 *   submitted      handed to faculty; still editable while they read
 *   needs_revision sent back with feedback; Front Desk edits again
 *   approved       the team's official concept, final and read-only
 *   not_selected   the other one, once faculty picked; still editable, never official
 *
 * Everything except approved stays open, because Front Desk is asked to keep
 * improving both proposals right up until faculty chooses between them. Approval
 * is the one irreversible step: it settles which concept the team builds.
 *
 * The states are per concept because faculty judges each one separately — one can
 * come back for another round while the other waits. What is shared is the
 * handover: Front Desk submits the pair in one action, because the point of two
 * concepts is that faculty compares them, and the choice itself, which lands on
 * both concepts at once.
 *
 * This class exists because each transition touches two places at once — the
 * concept rows and the team's task rows — and three unrelated callers need the
 * same transition: the student submit endpoint, the faculty feedback endpoint, and
 * the team-creation hooks that seed the task. Splitting that across them is how
 * the concepts' statuses and the task's status would drift apart.
 *
 * Reads and the create/update path stay in HotelConceptController, which owns the
 * revision history.
 */
class HotelConceptDesk
{
    /** The role that proposes the concepts and submits them. */
    public const OWNING_ROLE = 'front_desk';

    /**
     * The two concepts every team owes, addressed by slot.
     *
     * Slots rather than insertion order: "Concept 2 needs revision" has to keep
     * meaning the same row after either one is edited.
     */
    public const SLOTS = [1, 2];

    /**
     * Marks the seeded task row. Ordinary assignments leave tasks.kind null; a
     * marker column rather than a title match, because titles are free text.
     */
    public const TASK_KIND = 'hotel_concept';
    public const TASK_TITLE = 'Propose Two Hotel Concepts';
    public const TASK_DESCRIPTION = 'Propose two hotel concepts your team could build: for each one, its title, its type and what makes it different. Write both here, improve them as often as you like, then submit the pair to your faculty, who reviews each concept separately and approves one.';

    public const STATUS_DRAFT = 'draft';
    public const STATUS_SUBMITTED = 'submitted';
    public const STATUS_NEEDS_REVISION = 'needs_revision';
    public const STATUS_APPROVED = 'approved';
    public const STATUS_NOT_SELECTED = 'not_selected';

    /** The badge each state prints, and the only accepted values. */
    public const STATUSES = [
        self::STATUS_DRAFT => 'Draft',
        self::STATUS_SUBMITTED => 'With faculty for review',
        self::STATUS_NEEDS_REVISION => 'Needs revision',
        self::STATUS_APPROVED => 'Approved',
        self::STATUS_NOT_SELECTED => 'Not selected',
    ];

    /**
     * States in which a concept is open for edits.
     *
     * Front Desk keeps improving a concept while it sits with faculty and after it
     * comes back for revision. The choice closes both concepts at once: the
     * approved one because it is final, the other because it is out of the run.
     */
    private const EDITABLE_STATUSES = [
        self::STATUS_DRAFT,
        self::STATUS_SUBMITTED,
        self::STATUS_NEEDS_REVISION,
    ];

    public static function isValidSlot($slot): bool
    {
        return in_array((int) $slot, self::SLOTS, true);
    }

    public static function status(?HotelConcept $concept): string
    {
        return $concept ? ((string) $concept->status ?: self::STATUS_DRAFT) : self::STATUS_DRAFT;
    }

    public static function statusLabel(?HotelConcept $concept): string
    {
        return self::STATUSES[self::status($concept)] ?? self::STATUSES[self::STATUS_DRAFT];
    }

    /** "Concept 1", the name the portals print and the review dialog labels. */
    public static function slotLabel($slot): string
    {
        return 'Concept ' . (int) $slot;
    }

    /**
     * Who may write a concept.
     *
     * Front Desk alone, first version and every version after it. The concepts are
     * proposed on the Front Desk task row in the student's Tasks section, and a
     * teammate without the role never sees that row — so the gate has to say the
     * same thing, or the endpoint would still accept a write nobody can reach.
     *
     * Editing stays open while the pair sits with faculty and after one comes back
     * for revision. Faculty's choice ends it either way: the approved concept is
     * final, the other is out of the run.
     */
    public static function canEdit(?HotelConcept $concept, array $roleKeys): bool
    {
        if (!in_array(self::OWNING_ROLE, $roleKeys, true)) {
            return false;
        }

        return $concept
            ? in_array(self::status($concept), self::EDITABLE_STATUSES, true)
            : true;
    }

    /**
     * Whether the team can hand their concepts in.
     *
     * Both slots have to be filled: faculty is being asked to weigh two proposals
     * against each other, and one is not a choice. Beyond that there has to be
     * something new to hand in — a pair already sitting with faculty and untouched
     * since, or a pair already decided, submits nothing.
     */
    public static function canSubmit(Collection $concepts, array $roleKeys): bool
    {
        if (!in_array(self::OWNING_ROLE, $roleKeys, true)) {
            return false;
        }

        if (!self::allSlotsFilled($concepts) || self::isDecided($concepts)) {
            return false;
        }

        return self::submittableConcepts($concepts)->isNotEmpty();
    }

    /** Every slot has a concept in it. */
    public static function allSlotsFilled(Collection $concepts): bool
    {
        foreach (self::SLOTS as $slot) {
            if (!$concepts->firstWhere('slot', $slot)) {
                return false;
            }
        }

        return true;
    }

    /** The team's official concept, once faculty has chosen one. */
    public static function approvedConcept(Collection $concepts): ?HotelConcept
    {
        return $concepts->firstWhere('status', self::STATUS_APPROVED);
    }

    /** Whether faculty has already chosen — the point past which nothing about the pair can move. */
    public static function isDecided(Collection $concepts): bool
    {
        return self::approvedConcept($concepts) !== null;
    }

    /**
     * The concepts a portal should show.
     *
     * Before a decision, both — the point is to weigh them against each other.
     * After one, only the approved concept: the other stops being a proposal the
     * moment it loses, and leaving it on screen reads as if it were still in
     * play. The row itself is kept, history and all; this is only about what the
     * portals draw.
     */
    public static function visibleConcepts(Collection $concepts): Collection
    {
        $approved = self::approvedConcept($concepts);

        return $approved ? collect([$approved]) : $concepts->values();
    }

    /**
     * Whether faculty can act on this slot right now.
     *
     * Both proposals have to exist — faculty is choosing between two, not judging
     * one in isolation — and nobody has chosen yet. Once a concept is approved,
     * every verdict control disappears on both slots: the decision is final.
     */
    public static function canReview(Collection $concepts, int $slot): bool
    {
        if (!self::allSlotsFilled($concepts) || self::isDecided($concepts)) {
            return false;
        }

        return (bool) $concepts->firstWhere('slot', $slot);
    }

    /**
     * The concepts a submit would actually hand in.
     *
     * A concept already sitting with faculty is still open for edits, but
     * resubmitting it untouched would only reset its timestamps and write a
     * hollow "submitted" entry. Submit moves only what genuinely needs faculty's
     * attention again — new work, or a concept coming back from a revision
     * request. (not_selected never appears here: it only exists once a concept
     * has been chosen, and choosing ends submitting for the pair — see
     * isDecided().)
     */
    public static function submittableConcepts(Collection $concepts): Collection
    {
        return $concepts->filter(fn (HotelConcept $c) => in_array(self::status($c), [
            self::STATUS_DRAFT,
            self::STATUS_NEEDS_REVISION,
        ], true))->values();
    }

    /** Why an edit was refused, so the dashboard can say something useful. */
    public static function editRefusal(?HotelConcept $concept, array $roleKeys): string
    {
        if (!in_array(self::OWNING_ROLE, $roleKeys, true)) {
            return 'Only the Front Desk members of this team can write a hotel concept.';
        }

        if (!$concept) {
            return 'Only the Front Desk members of this team can propose a hotel concept.';
        }

        return self::status($concept) === self::STATUS_NOT_SELECTED
            ? 'Your faculty chose the other concept, so this one is closed.'
            : 'Your faculty approved this concept, so it is final and can no longer be edited.';
    }

    /** Why a submit was refused. */
    public static function submitRefusal(Collection $concepts, array $roleKeys): string
    {
        if (!in_array(self::OWNING_ROLE, $roleKeys, true)) {
            return 'Only the Front Desk members of this team can submit the concepts to your faculty.';
        }

        if (!self::allSlotsFilled($concepts)) {
            $missing = collect(self::SLOTS)
                ->reject(fn ($slot) => (bool) $concepts->firstWhere('slot', $slot))
                ->map(fn ($slot) => self::slotLabel($slot));

            return 'Propose both concepts before submitting — still empty: '
                . $missing->implode(', ') . '.';
        }

        return self::isDecided($concepts)
            ? 'Your faculty has already chosen a concept for this team.'
            : 'Your concepts are already with your faculty for review.';
    }

    /** A team's concepts, in slot order. */
    public static function conceptsFor(?string $groupName, ?int $facultyId): Collection
    {
        if (!$groupName || !$facultyId) {
            return collect();
        }

        return HotelConcept::with(['creator', 'editor', 'submitter', 'reviewer'])
            ->where('group_name', $groupName)
            ->where('faculty_id', $facultyId)
            ->orderBy('slot')
            ->get();
    }

    /**
     * The team's concept task rows.
     *
     * tasks carries no team column — a row is tied to one student, and the
     * membership implies the team — so the team is resolved through its members'
     * student ids, the same way FacultyController::role() does it.
     */
    public static function teamTasks(string $groupName, int $facultyId): Collection
    {
        $studentIds = StudentGroup::where('group_name', $groupName)
            ->where('faculty_id', $facultyId)
            ->pluck('student_id')
            ->filter()
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->all();

        if ($studentIds === []) {
            return collect();
        }

        return Task::where('kind', self::TASK_KIND)
            ->where('faculty_id', $facultyId)
            ->whereIn('student_id', $studentIds)
            ->get();
    }

    /** Seed the task for one Front Desk member. Safe to call repeatedly. */
    public static function ensureTaskFor(StudentGroup $membership): void
    {
        if (!$membership->student_id || !$membership->faculty_id) {
            return;
        }

        $roleKeys = $membership->relationLoaded('roles')
            ? StudentGroupSync::roleKeys($membership)
            : $membership->roles()->pluck('role')->all();

        if (!in_array(self::OWNING_ROLE, $roleKeys, true)) {
            return;
        }

        $existing = Task::where('kind', self::TASK_KIND)
            ->where('student_id', $membership->student_id)
            ->first();

        if ($existing) {
            // The task was seeded when a team owed one concept; the wording has to
            // catch up or the card asks for the wrong thing.
            if ($existing->title !== self::TASK_TITLE || $existing->description !== self::TASK_DESCRIPTION) {
                $existing->forceFill([
                    'title' => self::TASK_TITLE,
                    'description' => self::TASK_DESCRIPTION,
                ])->save();
            }

            // Seeded before tasks named their team, or the member has since moved.
            // Stamp it now so it reads as this team's rather than as everyone's.
            if (blank($existing->group_name) && filled($membership->group_name)) {
                $existing->forceFill([
                    'group_name' => $membership->group_name,
                    'group_id' => $membership->group_id,
                ])->save();
            }

            return;
        }

        Task::create([
            'faculty_id' => $membership->faculty_id,
            // Named like any other task so the team reads it as theirs and nobody
            // else picks it up, rather than being inferred from student_id.
            'group_name' => $membership->group_name,
            'group_id' => $membership->group_id,
            'student_id' => $membership->student_id,
            'assigned_to' => Student::whereKey($membership->student_id)->value('user_id'),
            'role' => self::OWNING_ROLE,
            'kind' => self::TASK_KIND,
            'title' => self::TASK_TITLE,
            'description' => self::TASK_DESCRIPTION,
            // No due date: this one is not on the faculty's schedule, it gates
            // everything else the team builds.
            'due_date' => null,
            'priority' => 'high',
            'status' => 'active',
        ]);
    }

    /** Seed the task for every Front Desk member of one team. */
    public static function ensureTasksForTeam(string $groupName, int $facultyId): void
    {
        $members = StudentGroup::with('roles')
            ->where('group_name', $groupName)
            ->where('faculty_id', $facultyId)
            ->get();

        foreach ($members as $member) {
            self::ensureTaskFor($member);
        }
    }

    /**
     * Hand every open concept to faculty, in one action.
     *
     * The revision written per concept snapshots the exact text faculty will
     * judge, so the history says what was submitted even after a later round
     * changes it.
     */
    public static function submit(Collection $concepts, StudentGroup $membership, User $actor): void
    {
        $submitting = self::submittableConcepts($concepts);

        DB::transaction(function () use ($submitting, $membership, $actor) {
            foreach ($submitting as $concept) {
                $concept->fill([
                    'status' => self::STATUS_SUBMITTED,
                    'submitted_at' => now(),
                    'submitted_by' => $actor->user_id,
                    // A verdict from the previous round no longer applies.
                    'faculty_feedback' => null,
                    'reviewed_at' => null,
                    'reviewed_by' => null,
                ])->save();

                HotelConceptRevision::create([
                    'hotel_concept_id' => $concept->hotel_concept_id,
                    'user_id' => $actor->user_id,
                    'editor_name' => \App\Http\Controllers\HotelConceptController::displayName($actor),
                    'action' => HotelConceptRevision::SUBMITTED,
                    'field_changes' => [],
                    'title' => $concept->title,
                    'description' => $concept->description,
                    'hotel_type' => $concept->hotel_type,
                ]);
            }

            self::syncTeamTasks($membership, $actor, false);

            ActivityLog::record(
                $actor,
                ActivityLog::CONCEPT_SUBMITTED,
                'Submitted ' . $submitting
                    ->map(fn (HotelConcept $c) => self::slotLabel($c->slot) . ' "' . $c->title . '"')
                    ->implode(' and ')
                    . ' of team "' . $membership->group_name . '" to faculty for review.'
            );
        });

        Notifier::conceptsSubmitted($actor, $submitting, $membership);
    }

    /**
     * Record the faculty verdict on one concept.
     *
     * One concept at a time by design: the team proposed two so that faculty could
     * take each on its own merits, approving one and sending the other back. But
     * approval is a choice between the two, not a verdict on one in isolation — so
     * approving a slot also settles its sibling as not selected, in the same
     * transaction, rather than leaving it to a verdict that may never come.
     */
    public static function review(
        HotelConcept $concept,
        StudentGroup $membership,
        User $faculty,
        bool $revise,
        ?string $feedback
    ): void {
        $notSelected = null;

        DB::transaction(function () use ($concept, $membership, $faculty, $revise, $feedback, &$notSelected) {
            $concept->fill([
                'status' => $revise ? self::STATUS_NEEDS_REVISION : self::STATUS_APPROVED,
                'faculty_feedback' => $feedback ?: null,
                'reviewed_at' => now(),
                'reviewed_by' => $faculty->user_id,
            ]);

            if ($revise) {
                $concept->revision_count = (int) $concept->revision_count + 1;
            }

            $concept->save();

            HotelConceptRevision::create([
                'hotel_concept_id' => $concept->hotel_concept_id,
                'user_id' => $faculty->user_id,
                'editor_name' => \App\Http\Controllers\HotelConceptController::displayName($faculty),
                'action' => $revise
                    ? HotelConceptRevision::CHANGES_REQUESTED
                    : HotelConceptRevision::APPROVED,
                'field_changes' => [],
                'title' => $concept->title,
                'description' => $concept->description,
                'hotel_type' => $concept->hotel_type,
            ]);

            if (!$revise) {
                $notSelected = self::conceptsFor((string) $membership->group_name, (int) $membership->faculty_id)
                    ->reject(fn (HotelConcept $c) => $c->hotel_concept_id === $concept->hotel_concept_id)
                    ->first();

                if ($notSelected) {
                    $notSelected->fill([
                        'status' => self::STATUS_NOT_SELECTED,
                        'reviewed_at' => now(),
                        'reviewed_by' => $faculty->user_id,
                    ])->save();

                    HotelConceptRevision::create([
                        'hotel_concept_id' => $notSelected->hotel_concept_id,
                        'user_id' => $faculty->user_id,
                        'editor_name' => \App\Http\Controllers\HotelConceptController::displayName($faculty),
                        'action' => HotelConceptRevision::NOT_SELECTED,
                        'field_changes' => [],
                        'title' => $notSelected->title,
                        'description' => $notSelected->description,
                        'hotel_type' => $notSelected->hotel_type,
                    ]);
                }
            }

            self::syncTeamTasks($membership, $faculty, true);

            ActivityLog::record(
                $faculty,
                ActivityLog::CONCEPT_REVIEWED,
                ($revise ? 'Requested changes on' : 'Approved')
                    . ' ' . self::slotLabel($concept->slot) . ' "' . $concept->title
                    . '" of team "' . $membership->group_name . '"'
                    . ($notSelected
                        ? ' as the official concept; ' . self::slotLabel($notSelected->slot) . ' not selected.'
                        : '.')
            );
        });

        Notifier::conceptReviewed($faculty, $concept, $membership, $revise, $notSelected);
    }

    /**
     * Point the team's task rows at where the concepts actually stand.
     *
     * The task is open until faculty has decided and closed once they have — being
     * editable no longer means there is something to do, since the not-selected
     * concept stays editable too. A decision, not an open slot, is what closes it.
     *
     * Every member's row is stamped, not just the one a faculty happened to open —
     * the concepts belong to the team, and each Front Desk member's card has to
     * read the same way.
     */
    private static function syncTeamTasks(StudentGroup $membership, User $actor, bool $isVerdict): void
    {
        $concepts = self::conceptsFor((string) $membership->group_name, (int) $membership->faculty_id);
        $decided = self::isDecided($concepts);

        // Feedback is named per concept: "revise Concept 2" is useless on a card
        // that cannot say which of the two it means.
        $notes = $concepts
            ->filter(fn (HotelConcept $c) => filled($c->faculty_feedback))
            ->map(fn (HotelConcept $c) => self::slotLabel($c->slot) . ' — '
                . self::statusLabel($c) . ': ' . $c->faculty_feedback)
            ->implode("\n\n");

        foreach (self::teamTasks((string) $membership->group_name, (int) $membership->faculty_id) as $task) {
            // 'active' with feedback is how this app spells "needs revision" — see
            // Task::getNeedsRevisionAttribute().
            $task->status = $decided ? 'archived' : 'active';
            $task->feedback = $notes !== '' ? $notes : null;

            if ($isVerdict) {
                $task->feedback_at = now();
                $task->feedback_by = $actor->user_id;
            }

            // Counts rounds the team was sent back, across both concepts, so the
            // card's "revision N" matches what actually happened.
            $task->revision_count = (int) $concepts->sum(fn (HotelConcept $c) => (int) $c->revision_count);

            $task->save();
        }
    }
}
