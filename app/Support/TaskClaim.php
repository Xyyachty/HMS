<?php

namespace App\Support;

use App\Models\StudentGroup;
use App\Models\Task;
use App\Models\User;

/**
 * Who may work a task row now, as opposed to who it was handed to.
 *
 * Rows fan out one per member holding the role at assignment time, and the name
 * on a row is what keeps a student from submitting a teammate's copy of the same
 * work. That name goes stale, though: roles are reshuffled inside a team long
 * after tasks are handed out, and the row stays pointing at whoever held the role
 * that day. The student who holds it now can see the row — the dashboard lists by
 * role — but could neither submit it nor tick a step on it, and nobody could:
 * the named student no longer holds the role either, so the work sat unreachable
 * with no way back short of faculty deleting and reassigning it.
 *
 * A row is therefore workable by the student named on it, by anyone holding the
 * role while the row is unclaimed, and by anyone holding the role when the named
 * student has since given it up. A teammate who shares the role with a member who
 * still holds it is refused exactly as before.
 */
class TaskClaim
{
    /**
     * Whether the student a row was handed to still holds the role it is for.
     *
     * A row with no student named is not stale — it is unclaimed, which the
     * callers already allow.
     */
    public static function isStale(Task $task): bool
    {
        if (!$task->student_id && !$task->assigned_to) {
            return false;
        }

        $assignee = StudentGroup::with('roles')
            ->when($task->student_id, fn ($q) => $q->where('student_id', $task->student_id))
            ->when(!$task->student_id, fn ($q) => $q->whereIn(
                'student_id',
                \App\Models\Student::where('user_id', $task->assigned_to)->select('user_information_id')
            ))
            ->when(filled($task->group_name), fn ($q) => $q->whereRaw(
                'LOWER(group_name) = LOWER(?)',
                [(string) $task->group_name]
            ))
            ->first();

        // Left the team altogether, or is still on it without the role.
        return !$assignee || !$assignee->roles->contains('role', $task->role);
    }

    /**
     * Whether this student may submit the row or tick its steps.
     *
     * $membership is the student's own team row; the role check itself is left to
     * the caller, which has already made it to get this far.
     */
    public static function mayWork(Task $task, User $user, ?StudentGroup $membership = null): bool
    {
        $studentId = $user->student?->user_information_id;

        $mine = ((int) $task->assigned_to === (int) $user->user_id && $task->assigned_to)
            || ($task->student_id && (int) $task->student_id === (int) $studentId);

        if ($mine) {
            return true;
        }

        if (!self::isStale($task)) {
            return false;
        }

        /* Stale, so the row is open again — but only to this team's holders of the
           role, not to anyone who happens to ask. The callers check both before
           they get here; checked again because a support class that answers true
           to a stranger is one wrong caller away from a hole. */
        $membership = $membership ?: StudentGroup::with('roles')
            ->where('student_id', $studentId)
            ->first();

        if (!$membership) {
            return false;
        }

        if (filled($task->group_name)
            && strcasecmp((string) $task->group_name, (string) $membership->group_name) !== 0) {
            return false;
        }

        return $membership->roles->contains('role', $task->role);
    }
}
