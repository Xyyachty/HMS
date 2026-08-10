<?php

namespace App\Support;

use App\Models\FacultyClass;
use App\Models\HotelComplaint;
use App\Models\Student;
use App\Models\StudentGroup;
use App\Models\Task;
use App\Models\User;
use App\Models\UserNotification;
use Illuminate\Support\Facades\DB;

/**
 * Fan-out for the in-app notification bell.
 *
 * Every "who should hear about this" rule lives here, not in the controllers,
 * so the audience for an event is decided once. Rows are written per recipient
 * at event time — the bell then only ever reads its own user's rows.
 *
 * Audience model:
 *   Dean    — org-level events only (enrollment, a class opening, teams formed).
 *             Never per-task chatter, which would drown the feed.
 *   Faculty — everything inside their own faculty.
 *   Student — what is addressed to them or to their team.
 *
 * Nothing here may throw: a notification failure must not roll back the action
 * the user actually asked for.
 */
class Notifier
{
    /**
     * Insert one row per recipient. The actor is dropped — nobody needs telling
     * about what they just did themselves.
     */
    public static function push(
        array $userIds,
        string $type,
        string $title,
        ?string $body = null,
        ?string $url = null,
        ?User $actor = null
    ): void {
        try {
            $actorId = $actor?->user_id;

            $userIds = collect($userIds)
                ->filter()
                ->map(fn ($id) => (int) $id)
                ->unique()
                ->reject(fn ($id) => $actorId && $id === (int) $actorId)
                ->values();

            if ($userIds->isEmpty()) {
                return;
            }

            $now = now();
            $rows = $userIds->map(fn ($id) => [
                'user_id' => $id,
                'actor_id' => $actorId,
                'type' => $type,
                'title' => mb_substr(trim($title), 0, 255),
                'body' => $body === null ? null : mb_substr(trim($body), 0, 500),
                'url' => $url,
                'read_at' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ])->all();

            // Chunked: a faculty-wide task assignment can touch every student.
            foreach (array_chunk($rows, 200) as $chunk) {
                DB::table('user_notifications')->insert($chunk);
            }
        } catch (\Throwable $e) {
            report($e);
        }
    }

    /* ── Audience resolvers ─────────────────────────────────────────────── */

    /** Every dean account. Deans administer all faculties, so they share a feed. */
    public static function deanUserIds(): array
    {
        return User::where('role', 'dean')
            ->pluck('user_id')
            ->map(fn ($id) => (int) $id)
            ->all();
    }

    /** The user account behind a faculties.faculty_id, or null when unlinked. */
    public static function facultyUserId(?int $facultyId): ?int
    {
        if (!$facultyId) {
            return null;
        }

        $userId = DB::table('faculties')->where('faculty_id', $facultyId)->value('user_id');

        return $userId ? (int) $userId : null;
    }

    /** Student user ids for one team. */
    public static function teamUserIds(string $groupName, int $facultyId): array
    {
        return Student::whereIn(
            'student_id',
            StudentGroup::where('group_name', $groupName)
                ->where('faculty_id', $facultyId)
                ->pluck('student_id')
        )
            ->pluck('user_id')
            ->filter()
            ->map(fn ($id) => (int) $id)
            ->all();
    }

    /**
     * Student user ids for the members of one team who hold any of the given roles.
     * Used to address a department rather than the whole team.
     */
    public static function teamRoleUserIds(string $groupName, int $facultyId, array $roles): array
    {
        if ($roles === []) {
            return [];
        }

        $studentIds = StudentGroup::where('group_name', $groupName)
            ->where('faculty_id', $facultyId)
            ->whereHas('roles', fn ($query) => $query->whereIn('role', $roles))
            ->pluck('student_id');

        return Student::whereIn('student_id', $studentIds)
            ->pluck('user_id')
            ->filter()
            ->map(fn ($id) => (int) $id)
            ->all();
    }

    /** Student user ids from students.student_id values. */
    public static function userIdsForStudents(array $studentIds): array
    {
        if ($studentIds === []) {
            return [];
        }

        return Student::whereIn('student_id', $studentIds)
            ->pluck('user_id')
            ->filter()
            ->map(fn ($id) => (int) $id)
            ->all();
    }

    /* ── Events ─────────────────────────────────────────────────────────── */

    /** The new student's own welcome. Split out so bulk imports can reuse it. */
    public static function studentWelcomed(?User $actor, User $studentUser, ?string $className): void
    {
        $className = $className ?: 'a class';

        static::push(
            [$studentUser->user_id],
            UserNotification::STUDENT_ADDED,
            'Welcome to ' . $className,
            'Your student account is ready. You have been enrolled in ' . $className . '.',
            route('students.dashboard'),
            $actor
        );
    }

    /**
     * A dean provisioned an account directly. Only the new account holder is
     * told — the dean did it themselves, and no faculty owns the row yet.
     */
    public static function accountCreated(?User $actor, User $newUser, string $role): void
    {
        $home = $role === 'faculty'
            ? route('faculty.dashboard')
            : route('students.dashboard');

        static::push(
            [$newUser->user_id],
            UserNotification::STUDENT_ADDED,
            'Your account is ready',
            'A ' . $role . ' account was created for you. Sign in to get started.',
            $home,
            $actor
        );
    }

    /** One summary line for faculty + dean after a bulk import. */
    public static function studentsImported(?User $actor, int $facultyId, int $count): void
    {
        if ($count < 1) {
            return;
        }

        static::push(
            array_merge(
                array_filter([static::facultyUserId($facultyId)]),
                static::deanUserIds()
            ),
            UserNotification::STUDENT_ADDED,
            $count . ' student(s) imported',
            'A bulk import added ' . $count . ' student account(s).',
            route('faculty.students'),
            $actor
        );
    }

    /** A student account was created and seated in a class. */
    public static function studentAdded(
        ?User $actor,
        User $studentUser,
        string $studentName,
        ?FacultyClass $class,
        int $facultyId
    ): void {
        $className = $class?->name ?? 'a class';

        static::studentWelcomed($actor, $studentUser, $className);

        // Faculty owner + dean oversight.
        static::push(
            array_merge(
                array_filter([static::facultyUserId($facultyId)]),
                static::deanUserIds()
            ),
            UserNotification::STUDENT_ADDED,
            'New student enrolled',
            $studentName . ' was added to ' . $className . '.',
            route('faculty.students', array_filter(['class' => $class?->letter])),
            $actor
        );
    }

    /** A class filled up and the next letter opened for enrollment. */
    public static function classOpened(?User $actor, int $facultyId, FacultyClass $class): void
    {
        static::push(
            array_merge(
                array_filter([static::facultyUserId($facultyId)]),
                static::deanUserIds()
            ),
            UserNotification::CLASS_OPENED,
            $class->name . ' is now open',
            'The previous class reached capacity, so ' . $class->name
                . ' was opened and will receive new students.',
            route('faculty.students', array_filter(['class' => $class->letter])),
            // No actor filter: whoever enrolled the last student still wants to know.
            null
        );
    }

    /** One or more teams were formed. */
    public static function teamCreated(?User $actor, string $groupName, int $facultyId, array $studentIds): void
    {
        static::push(
            static::userIdsForStudents($studentIds),
            UserNotification::TEAM_CREATED,
            'You joined team ' . $groupName,
            'Your faculty placed you on team "' . $groupName . '". Open your dashboard to see your role.',
            route('students.dashboard'),
            $actor
        );

        static::push(
            static::deanUserIds(),
            UserNotification::TEAM_CREATED,
            'New team created',
            'Team "' . $groupName . '" was created with ' . count($studentIds) . ' member(s).',
            route('dean.dashboard'),
            $actor
        );
    }

    /** Students were added to a team that already existed. */
    public static function teamMembersAdded(?User $actor, string $groupName, int $facultyId, array $studentIds): void
    {
        $newUserIds = static::userIdsForStudents($studentIds);

        static::push(
            $newUserIds,
            UserNotification::TEAM_MEMBER_ADDED,
            'You joined team ' . $groupName,
            'Your faculty added you to team "' . $groupName . '".',
            route('students.dashboard'),
            $actor
        );

        // Existing team mates hear about the newcomers.
        $existing = array_diff(static::teamUserIds($groupName, $facultyId), $newUserIds);
        static::push(
            $existing,
            UserNotification::TEAM_MEMBER_ADDED,
            'New teammate on ' . $groupName,
            count($studentIds) . ' student(s) joined your team.',
            route('students.dashboard'),
            $actor
        );
    }

    /**
     * Faculty published task assignments. One notification per student, listing
     * how many tasks landed on them — not one per task row, which fans out to
     * four rows per team and would spam the bell.
     */
    public static function tasksAssigned(?User $actor, array $tasksPerUserId): void
    {
        foreach ($tasksPerUserId as $userId => $count) {
            static::push(
                [$userId],
                UserNotification::TASK_ASSIGNED,
                $count === 1 ? 'New task assigned' : $count . ' new tasks assigned',
                'Your faculty assigned you ' . $count . ' task(s). Check your dashboard for details.',
                route('students.dashboard'),
                $actor
            );
        }
    }

    /** A student submitted a task for review. */
    public static function taskSubmitted(?User $actor, Task $task, ?string $studentName = null): void
    {
        $who = $studentName ?: ($actor?->name ?? 'A student');

        static::push(
            array_filter([static::facultyUserId($task->faculty_id)]),
            UserNotification::TASK_SUBMITTED,
            'Task submitted for review',
            $who . ' submitted "' . $task->title . '" (' . $task->role_label . ').',
            route('faculty.tasks'),
            $actor
        );
    }

    /** Faculty approved a submission or sent it back with feedback. */
    public static function taskFeedback(?User $actor, Task $task, bool $revise): void
    {
        // Submitting fills assigned_to, but rows created before that still carry
        // only student_id. Falling back keeps the verdict from going nowhere.
        $recipient = $task->assigned_to
            ?: ($task->student_id ? Student::whereKey($task->student_id)->value('user_id') : null);

        static::push(
            array_filter([$recipient]),
            UserNotification::TASK_FEEDBACK,
            $revise ? 'Changes requested on your task' : 'Your task was approved',
            $revise
                ? 'Feedback on "' . $task->title . '": ' . (string) $task->feedback
                : '"' . $task->title . '" was approved by your faculty.',
            route('students.dashboard'),
            $actor
        );
    }

    /**
     * A team published their hotel site. Faculty owns the review, so they are the
     * audience — and unlike everything else in their feed this is student-initiated,
     * which is the point: a faculty member is dropped from their own actions, so
     * without inbound events like this and taskSubmitted their bell stays empty.
     */
    public static function sitePublished(
        ?User $actor,
        string $groupName,
        int $facultyId,
        ?string $roleLabel = null
    ): void {
        $who = $actor?->name ?: 'A student';
        $where = $roleLabel ? ' (' . $roleLabel . ')' : '';

        static::push(
            array_filter([static::facultyUserId($facultyId)]),
            UserNotification::SITE_PUBLISHED,
            'Team ' . $groupName . ' published their site',
            $who . ' published team "' . $groupName . '" hotel website' . $where . '.',
            route('faculty.role'),
            $actor
        );
    }

    /**
     * The Front Desk recorded a guest complaint. Only the department it was routed
     * to hears about it — the whole team can already see the list, and a complaint
     * about one room is not news to the other four roles.
     */
    public static function complaintFiled(?User $actor, HotelComplaint $complaint): void
    {
        $role = HotelComplaintAccess::DEPARTMENT_ROLES[$complaint->department] ?? null;
        if (!$role) {
            return;
        }

        static::push(
            static::teamRoleUserIds($complaint->group_name, (int) $complaint->faculty_id, [$role]),
            UserNotification::COMPLAINT_FILED,
            ($complaint->priority === 'Urgent' ? 'Urgent complaint · ' : 'New complaint · ')
                . 'Room ' . $complaint->room_number,
            $complaint->category . ' — ' . $complaint->details,
            route('students.' . $complaint->department . '.complaints'),
            $actor
        );
    }

    /**
     * A department closed a complaint out. The Front Desk took it from the guest, so
     * they are the ones who need to know the answer to give back.
     */
    public static function complaintResolved(?User $actor, HotelComplaint $complaint): void
    {
        $resolved = $complaint->status === 'Resolved';

        static::push(
            static::teamRoleUserIds($complaint->group_name, (int) $complaint->faculty_id, ['front_desk']),
            UserNotification::COMPLAINT_UPDATED,
            ($resolved ? 'Complaint resolved · ' : 'Complaint cancelled · ') . 'Room ' . $complaint->room_number,
            $complaint->departmentLabel() . ' marked "' . $complaint->category . '" as '
                . $complaint->status . '.'
                . ($complaint->resolution_note ? ' Note: ' . $complaint->resolution_note : ''),
            route('students.frontdesk.complaints'),
            $actor
        );
    }

    /**
     * A mis-routed complaint was handed to the other department. Told to the new
     * owners only; the ones who passed it on already know.
     */
    public static function complaintReassigned(?User $actor, HotelComplaint $complaint, string $from): void
    {
        $role = HotelComplaintAccess::DEPARTMENT_ROLES[$complaint->department] ?? null;
        if (!$role) {
            return;
        }

        $fromLabel = HotelComplaint::DEPARTMENTS[$from] ?? ucfirst($from);

        static::push(
            static::teamRoleUserIds($complaint->group_name, (int) $complaint->faculty_id, [$role]),
            UserNotification::COMPLAINT_FILED,
            'Complaint reassigned · Room ' . $complaint->room_number,
            $fromLabel . ' passed "' . $complaint->category . '" to your department.',
            route('students.' . $complaint->department . '.complaints'),
            $actor
        );
    }
}
