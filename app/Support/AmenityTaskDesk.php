<?php

namespace App\Support;

use App\Models\StudentGroup;
use App\Models\Task;
use App\Models\User;

/**
 * The task that opens the Amenities section for editing.
 *
 * The facilities list is Housekeeping's, but holding the role is no longer enough
 * to change it: faculty hands out "Customize Hotel Amenities" the way it hands out
 * every other piece of design work, and only the student it lands on may add,
 * edit, photograph or remove a facility. Everybody else on the team — Housekeeping
 * included — reads the same rows and sees the same site.
 *
 * Editing is open only while that task is live. Once it is submitted it is with
 * faculty, and once approved it is finished: both leave the list read-only, so a
 * team cannot rewrite the work faculty is reviewing or has already accepted. A
 * task sent back for changes is `active` again and reopens by itself.
 *
 * Ops work is deliberately outside this. Marking a facility out for repair, or
 * verifying the repair afterwards, is running the hotel rather than designing it,
 * and those routes keep the plain role check they always had.
 */
class AmenityTaskDesk
{
    /** The checklist entry, matched by title — a task row keeps no reference to it. */
    public const TASK_TITLE = 'Customize Hotel Amenities';

    public const ROLE = HotelAmenityAccess::MANAGE_ROLE;

    /** Nobody has been given the work yet. */
    public const STATE_UNASSIGNED = 'unassigned';

    /** Assigned and open: the assignee may edit. */
    public const STATE_ACTIVE = 'active';

    /** Submitted and with faculty. Read-only until it comes back or is approved. */
    public const STATE_SUBMITTED = 'submitted';

    /** Faculty accepted it. Locked. */
    public const STATE_APPROVED = 'approved';

    /**
     * The team's copy of the task, newest first.
     *
     * Scoped to the team rather than the student: the state of the section is the
     * team's fact, and a teammate has to be told why the buttons are missing.
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
     * Administrators are outside the workflow: the role exists to run and repair
     * the simulation, and locking it out of a team's data would leave nobody able
     * to unstick a team that has locked itself out.
     */
    public static function canCustomize(?StudentGroup $membership, ?User $user = null): bool
    {
        if (!$membership) {
            return false;
        }

        if (in_array('administrator', HotelAmenityAccess::roles($membership), true)) {
            return true;
        }

        $task = self::taskFor($membership);
        if (self::stateFor($task) !== self::STATE_ACTIVE) {
            return false;
        }

        $user = $user ?: auth()->user();
        if (!$user) {
            return false;
        }

        // An unclaimed row — faculty assigned the task to the team without naming a
        // member — is open to whoever holds the role, the same reading the rest of
        // the task screens give an unclaimed task.
        if (!$task->assigned_to && !$task->student_id) {
            return true;
        }

        return (int) $task->assigned_to === (int) $user->user_id
            || (int) $task->student_id === (int) ($user->student?->user_information_id ?? 0);
    }

    /**
     * Everything the Housekeeping screen needs to explain itself: whether the
     * buttons are there, and the one sentence saying why when they are not.
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
        if ($editable) {
            return '';
        }

        if ($state === self::STATE_UNASSIGNED) {
            return 'Your faculty has not assigned "' . self::TASK_TITLE . '" yet. '
                . 'The amenities below are read-only until they do.';
        }

        if ($state === self::STATE_SUBMITTED) {
            return '"' . self::TASK_TITLE . '" has been submitted and is with your faculty. '
                . 'The amenities are read-only until it is approved or sent back.';
        }

        if ($state === self::STATE_APPROVED) {
            return '"' . self::TASK_TITLE . '" is approved and closed, so the amenities are locked.';
        }

        return '"' . self::TASK_TITLE . '" is assigned to '
            . ($assignee ?: 'another member of your team')
            . '. You can see their work here but only they can change it.';
    }
}
