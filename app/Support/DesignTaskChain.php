<?php

namespace App\Support;

use App\Models\Task;
use App\Models\User;

/**
 * Design work that only makes sense once the step before it has been approved.
 *
 * Most tasks are handed out by the faculty from the checklist, in whatever order
 * they choose. A few are not: they ask a team to build on something faculty has
 * already accepted, and handing them out earlier asks for work that would have to
 * be redone. Room Management cannot describe its room categories — the picture,
 * the price, what the stay includes, how many rooms there are — until the
 * categories themselves exist and faculty has agreed they are the right ones.
 *
 * So the follow-up is not on the checklist at all. It is created here, once, when
 * the task before it is approved, and lands on the same student who did that work.
 *
 * Design stage only. The ops-stage tasks are about running the hotel that this
 * work describes, and they are assigned on the faculty's own judgement.
 */
class DesignTaskChain
{
    /**
     * The task that unlocks the next one, by the title it carries.
     *
     * Matched on title because that is what a task row actually holds: rows are
     * copied out of TaskChecklist at assignment time and keep no reference to the
     * entry they came from. Both wordings of the room-category step are listed, so
     * a team assigned the older one is not left with the follow-up unreachable.
     */
    public const TRIGGERS = [
        'room_management' => [
            'create your room categories',
            'build your room types',
        ],
    ];

    /** What that approval hands out, per role. */
    public const NEXT = [
        'room_management' => [
            'title' => 'Detail Every Room Category',
            'description' => "Now that your categories are approved, give each one what a guest needs to choose it: a photo of the room, the nightly rate, a description in your own words, what the stay includes, and how many rooms of that category the hotel has. Open the Rooms page in the builder and edit each category in turn.\n\nActivities:\n"
                . "1. Upload a photograph of a room in each category.\n"
                . "2. Set the nightly rate for every one.\n"
                . "3. Write what the stay includes, one item per line.\n"
                . "4. Say how many rooms of that category the hotel has.",
            'priority' => 'high',
        ],
    ];

    /**
     * Hand out the follow-up to a task faculty has just approved, if there is one.
     *
     * Idempotent by design: it answers null when the follow-up already exists for
     * the team, so approving a resubmission, or two faculty approving two members'
     * copies of the same task, cannot deal the same work twice.
     *
     * @return Task|null the task created, or null when nothing was unlocked.
     */
    public static function unlockNext(Task $approved, ?User $facultyUser = null): ?Task
    {
        $role = (string) $approved->role;
        $next = self::NEXT[$role] ?? null;
        if (!$next || !self::isTrigger($role, (string) $approved->title)) {
            return null;
        }

        // A task with no team would land on every team holding the role, which is
        // the bug per-team tasks were introduced to fix.
        if (!filled($approved->group_name)) {
            return null;
        }

        $already = Task::where('faculty_id', $approved->faculty_id)
            ->where('role', $role)
            ->whereRaw('LOWER(group_name) = LOWER(?)', [(string) $approved->group_name])
            ->whereRaw('LOWER(title) = LOWER(?)', [$next['title']])
            ->exists();

        if ($already) {
            return null;
        }

        return Task::create([
            'faculty_id' => $approved->faculty_id,
            'group_name' => $approved->group_name,
            'group_id' => $approved->group_id,
            'role' => $role,
            // The student whose work was approved is the one who carries it on. An
            // unclaimed row stays unclaimed, so whoever holds the role picks it up.
            'student_id' => $approved->student_id,
            'assigned_to' => $approved->assigned_to,
            'title' => $next['title'],
            'description' => $next['description'],
            'priority' => $next['priority'],
            'status' => 'active',
            // The approved work is where this one starts from, so the faculty review
            // of the follow-up compares against what they just accepted.
            'previous_version_id' => $approved->submitted_version_id,
        ]);
    }

    /** Whether this title is one of the steps that unlocks a follow-up. */
    public static function isTrigger(string $role, string $title): bool
    {
        $titles = self::TRIGGERS[$role] ?? [];

        return in_array(mb_strtolower(trim($title)), $titles, true);
    }

    /** Whether this title is one handed out by an approval rather than by faculty. */
    public static function isUnlocked(string $role, string $title): bool
    {
        $next = self::NEXT[$role] ?? null;

        return $next !== null && mb_strtolower(trim($title)) === mb_strtolower($next['title']);
    }
}
