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
 * The hotel concept's lifecycle: who may write it, and every state change.
 *
 * The concept is the team's first assignment, and it moves through four states:
 *
 *   draft          Front Desk proposed it; the whole team improves it
 *   submitted      handed to faculty; nobody edits until they answer
 *   needs_revision sent back with feedback; the whole team edits again
 *   approved       final, read-only
 *
 * This class exists because each transition touches two places at once — the
 * concept row and the team's task rows — and three unrelated callers need the
 * same transition: the student submit endpoint, the faculty feedback endpoint,
 * and the team-creation hooks that seed the task. Splitting that across them is
 * how the concept's status and the task's status would drift apart.
 *
 * Reads and the create/update path stay in HotelConceptController, which owns the
 * revision history.
 */
class HotelConceptDesk
{
    /** The role that proposes the concept and submits it. */
    public const OWNING_ROLE = 'front_desk';

    /**
     * Marks the seeded task row. Ordinary assignments leave tasks.kind null; a
     * marker column rather than a title match, because titles are free text.
     */
    public const TASK_KIND = 'hotel_concept';
    public const TASK_TITLE = 'Propose a Hotel Concept';
    public const TASK_DESCRIPTION = 'Propose the hotel your team will build: its name, its type and what makes it different. Front Desk writes the first version, then the whole team can improve it before Front Desk submits it to your faculty.';

    public const STATUS_DRAFT = 'draft';
    public const STATUS_SUBMITTED = 'submitted';
    public const STATUS_NEEDS_REVISION = 'needs_revision';
    public const STATUS_APPROVED = 'approved';

    /** The badge each state prints, and the only accepted values. */
    public const STATUSES = [
        self::STATUS_DRAFT => 'Draft',
        self::STATUS_SUBMITTED => 'Awaiting faculty review',
        self::STATUS_NEEDS_REVISION => 'Needs revision',
        self::STATUS_APPROVED => 'Approved',
    ];

    /** States in which the concept is open for edits. */
    private const EDITABLE_STATUSES = [self::STATUS_DRAFT, self::STATUS_NEEDS_REVISION];

    public static function status(?HotelConcept $concept): string
    {
        return $concept ? ((string) $concept->status ?: self::STATUS_DRAFT) : self::STATUS_DRAFT;
    }

    public static function statusLabel(?HotelConcept $concept): string
    {
        return self::STATUSES[self::status($concept)] ?? self::STATUSES[self::STATUS_DRAFT];
    }

    /**
     * Who may write the concept.
     *
     * Front Desk alone proposes the first version — that is their task. Once it
     * exists the concept belongs to the team, so every member may improve it,
     * which is the point of the workflow. Submitting closes it to everyone until
     * faculty answers, so they review a version that cannot move under them.
     */
    public static function canEdit(?HotelConcept $concept, array $roleKeys): bool
    {
        if (!$concept) {
            return in_array(self::OWNING_ROLE, $roleKeys, true);
        }

        return in_array(self::status($concept), self::EDITABLE_STATUSES, true);
    }

    /** Front Desk owns the handover, and only an open concept can be handed over. */
    public static function canSubmit(?HotelConcept $concept, array $roleKeys): bool
    {
        return $concept
            && in_array(self::OWNING_ROLE, $roleKeys, true)
            && in_array(self::status($concept), self::EDITABLE_STATUSES, true);
    }

    /** Why an edit was refused, so the dashboard can say something useful. */
    public static function editRefusal(?HotelConcept $concept, array $roleKeys): string
    {
        if (!$concept) {
            return 'Only the Front Desk members of this team can propose the hotel concept.';
        }

        return self::status($concept) === self::STATUS_APPROVED
            ? 'Your faculty approved this concept, so it is final and can no longer be edited.'
            : 'This concept is with your faculty for review, so it is locked until they respond.';
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

        $exists = Task::where('kind', self::TASK_KIND)
            ->where('student_id', $membership->student_id)
            ->exists();

        if ($exists) {
            return;
        }

        Task::create([
            'faculty_id' => $membership->faculty_id,
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
     * Hand the concept to faculty.
     *
     * The revision written here snapshots the exact text faculty will judge, so
     * the history says what was submitted even after a later revision changes it.
     */
    public static function submit(HotelConcept $concept, StudentGroup $membership, User $actor): void
    {
        DB::transaction(function () use ($concept, $membership, $actor) {
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

            // The whole team's rows close together: one concept, one submission.
            self::teamTasks((string) $membership->group_name, (int) $membership->faculty_id)
                ->each(function (Task $task) {
                    $task->status = 'archived';
                    // Feedback from the last round would otherwise render as
                    // "needs revision" on a row that has just been submitted.
                    $task->feedback = null;
                    $task->save();
                });

            ActivityLog::record(
                $actor,
                ActivityLog::CONCEPT_SUBMITTED,
                'Submitted the hotel concept "' . $concept->title . '" of team "'
                    . $membership->group_name . '" to faculty for review.'
            );
        });

        Notifier::conceptSubmitted($actor, $concept, $membership);
    }

    /**
     * Record the faculty verdict.
     *
     * Approving leaves the task archived and the concept read-only. Sending it
     * back reopens both, and counts the round on each side so the team's card and
     * the faculty's review dialog agree on how many times it has come back.
     */
    public static function review(
        HotelConcept $concept,
        StudentGroup $membership,
        User $faculty,
        bool $revise,
        ?string $feedback
    ): void {
        DB::transaction(function () use ($concept, $membership, $faculty, $revise, $feedback) {
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

            // Stamp every member's row, not just the one faculty happened to open:
            // the verdict is on the team's concept, and each Front Desk member's
            // task card has to read the same way.
            self::teamTasks((string) $membership->group_name, (int) $membership->faculty_id)
                ->each(function (Task $task) use ($faculty, $feedback, $revise) {
                    $task->fill([
                        'feedback' => $feedback ?: null,
                        'feedback_at' => now(),
                        'feedback_by' => $faculty->user_id,
                    ]);

                    if ($revise) {
                        // 'active' with feedback is how this app spells "needs
                        // revision" — see Task::getNeedsRevisionAttribute().
                        $task->status = 'active';
                        $task->revision_count = (int) $task->revision_count + 1;
                    }

                    $task->save();
                });

            ActivityLog::record(
                $faculty,
                ActivityLog::CONCEPT_REVIEWED,
                ($revise ? 'Requested changes on' : 'Approved')
                    . ' the hotel concept "' . $concept->title . '" of team "'
                    . $membership->group_name . '".'
            );
        });

        Notifier::conceptReviewed($faculty, $concept, $membership, $revise);
    }
}
