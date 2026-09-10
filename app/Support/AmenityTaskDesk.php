<?php

namespace App\Support;

use App\Models\StudentGroup;
use App\Models\Task;
use App\Models\User;

/**
 * Where "Customize Hotel Amenities" stands for a team.
 *
 * It reports; it does not gate. The facilities list belongs to Housekeeping and
 * holding that role is the whole test for changing it — this class exists so the
 * screen can say whether the task has been handed out, is with faculty, or is
 * approved, which is the part a student cannot read off the table itself.
 *
 * It did gate, briefly: only the student the task landed on could add a facility,
 * and only while it was open. That left every team whose faculty had not assigned
 * it unable to add anything at all, which is backwards — the checklist is what the
 * work is reviewed against, not permission to begin it.
 */
class AmenityTaskDesk
{
    /** The checklist entry, matched by title — a task row keeps no reference to it. */
    public const TASK_TITLE = 'Customize Hotel Amenities';

    public const ROLE = HotelAmenityAccess::MANAGE_ROLE;

    /** Nobody has been given the work yet. */
    public const STATE_UNASSIGNED = 'unassigned';

    /** Assigned and open. */
    public const STATE_ACTIVE = 'active';

    /** Submitted and with faculty. */
    public const STATE_SUBMITTED = 'submitted';

    /** Faculty accepted it. */
    public const STATE_APPROVED = 'approved';

    /**
     * The team's copy of the task, newest first.
     *
     * Scoped to the team rather than the student: how far this work has got is the
     * team's fact, and every member of it reads the same line.
     */
    public static function taskFor(?StudentGroup $membership): ?Task
    {
        if (!$membership || !filled($membership->group_name)) {
            return null;
        }

        return Task::where('faculty_id', $membership->faculty_id)
            ->where('role', self::ROLE)
            ->whereRaw('LOWER(group_name) = LOWER(?)', [(string) $membership->group_name])
            ->whereRaw('LOWER(title) = LOWER(?)', [self::TASK_TITLE])
            ->orderByDesc('task_id')
            ->first();
    }

    /**
     * Where the work stands, in the words the screens use.
     *
     * 'archived' is this app's "submitted" — see the tasks.complete route. What
     * separates a submission from an approval is feedback_at: faculty stamps it
     * when they review, and a review that asked for changes puts the row back to
     * 'active' instead of leaving it archived.
     */
    public static function stateFor(?Task $task): string
    {
        if (!$task) {
            return self::STATE_UNASSIGNED;
        }

        if ($task->status === 'archived') {
            return $task->feedback_at ? self::STATE_APPROVED : self::STATE_SUBMITTED;
        }

        return self::STATE_ACTIVE;
    }

    /**
     * Whether this user may change the facilities right now.
     *
     * Holding the role is the whole test. It once also required this task to be
     * assigned and to be theirs; that left every team whose faculty had not handed
     * it out unable to add a single facility, which is not what the checklist is
     * for. The task still tracks and reviews the work — it does not hold the door.
     */
    public static function canCustomize(?StudentGroup $membership, ?User $user = null): bool
    {
        return $membership !== null && HotelAmenityAccess::canManage($membership);
    }

    /**
     * What the Housekeeping screen prints above the table: whether this member may
     * edit, and where the task has got to.
     *
     * @return array{state: string, editable: bool, assignee: string|null, title: string, message: string}
     */
    public static function payload(?StudentGroup $membership, ?User $user = null): array
    {
        $task = self::taskFor($membership);
        $state = self::stateFor($task);
        $editable = self::canCustomize($membership, $user);
        $assignee = $task?->assignedTo?->name;

        return [
            'state' => $state,
            'editable' => $editable,
            'assignee' => $assignee,
            'title' => self::TASK_TITLE,
            'message' => self::message($state, $editable, $assignee),
        ];
    }

    private static function message(string $state, bool $editable, ?string $assignee): string
    {
        /* Not a refusal any more — the buttons are there either way. This says
           where the work stands, which is the part a student cannot see from the
           table itself. */
        if ($state === self::STATE_UNASSIGNED) {
            return '';
        }

        if ($state === self::STATE_SUBMITTED) {
            return '"' . self::TASK_TITLE . '" has been submitted and is with your faculty. '
                . 'Anything you change now is not part of what they are reviewing.';
        }

        if ($state === self::STATE_APPROVED) {
            return '"' . self::TASK_TITLE . '" is approved. You can still add and edit facilities.';
        }

        return '"' . self::TASK_TITLE . '" is assigned to '
            . ($assignee ?: 'your team')
            . '. Hand it in from your dashboard when the facilities are ready.';
    }
}
