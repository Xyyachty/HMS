<?php

namespace App\Support;

use App\Models\FacultyClass;
use App\Models\HotelComplaint;
use App\Models\HotelConcept;
use App\Models\HotelDineInTable;
use App\Models\HotelRoomInspection;
use App\Models\Student;
use App\Models\StudentGroup;
use App\Models\Task;
use App\Models\User;
use App\Models\UserInformation;
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
 *   Dean    — org-level events only (enrollment, a block opening, teams formed).
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

    /** The user account behind a faculty's user_information_id, or null when unlinked. */
    public static function facultyUserId(?int $facultyId): ?int
    {
        if (!$facultyId) {
            return null;
        }

        $userId = DB::table('user_information')
            ->where('user_information_id', $facultyId)
            ->where('user_type', UserInformation::TYPE_FACULTY)
            ->value('user_id');

        return $userId ? (int) $userId : null;
    }

    /** Student user ids for one team. */
    public static function teamUserIds(string $groupName, int $facultyId): array
    {
        return Student::whereIn(
            'user_information_id',
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

        return Student::whereIn('user_information_id', $studentIds)
            ->pluck('user_id')
            ->filter()
            ->map(fn ($id) => (int) $id)
            ->all();
    }

    /** Student user ids from user_information_id values. */
    public static function userIdsForStudents(array $studentIds): array
    {
        if ($studentIds === []) {
            return [];
        }

        return Student::whereIn('user_information_id', $studentIds)
            ->pluck('user_id')
            ->filter()
            ->map(fn ($id) => (int) $id)
            ->all();
    }

    /* ── Events ─────────────────────────────────────────────────────────── */

    /** The new student's own welcome. Split out so bulk imports can reuse it. */
    public static function studentWelcomed(?User $actor, User $studentUser, ?string $className): void
    {
        $className = $className ?: 'a block';

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

    /** A student account was created and seated in a block. */
    public static function studentAdded(
        ?User $actor,
        User $studentUser,
        string $studentName,
        ?FacultyClass $class,
        int $facultyId
    ): void {
        $className = $class?->name ?? 'a block';

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
            'The previous block reached capacity, so ' . $class->name
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
     * A team handed their hotel concepts in. Faculty owns the verdict, so they are
     * the audience — this is the inbound half of the concept workflow.
     *
     * One notification for the pair, not one each: they arrive together and are
     * reviewed side by side, so two rows would just be noise in the bell.
     *
     * @param  \Illuminate\Support\Collection<int, HotelConcept>  $concepts
     */
    public static function conceptsSubmitted(?User $actor, $concepts, StudentGroup $membership): void
    {
        $titles = collect($concepts)
            ->map(fn (HotelConcept $concept) => HotelConceptDesk::slotLabel($concept->slot) . ' "' . $concept->title . '"')
            ->implode(' and ');

        static::push(
            array_filter([static::facultyUserId($membership->faculty_id)]),
            UserNotification::CONCEPT_SUBMITTED,
            count($concepts) === 1
                ? 'Hotel concept submitted for review'
                : 'Hotel concepts submitted for review',
            'Team ' . $membership->group_name . ' submitted ' . $titles . ' for your review.',
            route('faculty.role', ['tab' => 'teams']),
            $actor
        );
    }

    /**
     * Faculty approved one concept or sent it back.
     *
     * The whole team hears it, not only the Front Desk member who submitted: every
     * member may edit the concept, so a "needs revision" is work for all of them.
     * Named by slot, because the other concept may have gone the other way.
     *
     * $notSelected is passed only on approval, when the decision also settled the
     * sibling concept — the team is told the outcome of the choice, not just the
     * one verdict.
     */
    public static function conceptReviewed(
        ?User $actor,
        HotelConcept $concept,
        StudentGroup $membership,
        bool $revise,
        ?HotelConcept $notSelected = null
    ): void {
        $which = HotelConceptDesk::slotLabel($concept->slot) . ' "' . $concept->title . '"';

        $body = $revise
            ? 'Feedback on ' . $which . ': ' . (string) $concept->faculty_feedback
            : $which . ' was approved by your faculty as your official hotel concept.'
                . ($notSelected
                    ? ' ' . HotelConceptDesk::slotLabel($notSelected->slot) . ' "' . $notSelected->title . '" was not selected.'
                    : '');

        static::push(
            static::teamUserIds((string) $membership->group_name, (int) $membership->faculty_id),
            UserNotification::CONCEPT_REVIEWED,
            $revise
                ? 'Changes requested on ' . HotelConceptDesk::slotLabel($concept->slot)
                : HotelConceptDesk::slotLabel($concept->slot) . ' approved as your hotel concept',
            $body,
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
     * Complaints are part of the hotel simulation's own workflow (the department
     * list already shows them) and must not raise a dashboard notification.
     */
    public static function complaintFiled(?User $actor, HotelComplaint $complaint): void
    {
        // Intentionally no-op.
    }

    /**
     * Complaints are part of the hotel simulation's own workflow (the department
     * list already shows them) and must not raise a dashboard notification.
     */
    public static function complaintResolved(?User $actor, HotelComplaint $complaint): void
    {
        // Intentionally no-op.
    }

    /**
     * Complaints are part of the hotel simulation's own workflow (the department
     * list already shows them) and must not raise a dashboard notification.
     */
    public static function complaintReassigned(?User $actor, HotelComplaint $complaint, string $from): void
    {
        // Intentionally no-op.
    }

    /**
     * The Front Desk seated a guest at a table. Restaurant Management is the only
     * audience — they are the ones about to take the order.
     */
    public static function tableAssigned(?User $actor, HotelDineInTable $table): void
    {
        static::push(
            static::teamRoleUserIds($table->group_name, (int) $table->faculty_id, ['restaurant_management']),
            UserNotification::TABLE_ASSIGNED,
            'Guest seated · ' . $table->name,
            ($table->guest_name ? $table->guest_name . ' ' : 'A guest ')
                . '(party of ' . $table->party_size . ') is waiting to order.',
            route('students.restaurant.manage', ['nav' => 'manage-tables']),
            $actor
        );
    }

    /**
     * A stay ended and the room needs the post-checkout pass. No actor: check-out can
     * be Front Desk settling the bill or a room-status flip, and either way this is
     * addressed to Housekeeping, not credited to whoever triggered it.
     */
    public static function roomAwaitingInspection(HotelRoomInspection $inspection): void
    {
        static::push(
            static::teamRoleUserIds($inspection->group_name, (int) $inspection->faculty_id, ['housekeeping']),
            UserNotification::ROOM_INSPECTION,
            'Room ready to inspect · ' . $inspection->room_name,
            ($inspection->guest_name ? $inspection->guest_name . ' checked out. ' : 'A guest checked out. ')
                . 'The room needs a housekeeping pass before it can be sold again.',
            route('students.housekeeping.inspections'),
            null
        );
    }

    /**
     * Maintenance closed every issue this inspection raised. Housekeeping does the
     * final look before the room goes back on sale — PROCEDURES.md's "conduct a final
     * inspection after repair".
     */
    public static function roomAwaitingReinspection(HotelRoomInspection $inspection): void
    {
        static::push(
            static::teamRoleUserIds($inspection->group_name, (int) $inspection->faculty_id, ['housekeeping']),
            UserNotification::ROOM_INSPECTION,
            'Repair done, re-inspect · ' . $inspection->room_name,
            'Maintenance closed the reported issue. Give the room a final look before marking it ready.',
            route('students.housekeeping.inspections'),
            null
        );
    }

    /**
     * Housekeeping cleared the room. The Front Desk is the only audience — they are
     * the ones who sell it next.
     */
    public static function roomReadyForNextGuest(HotelRoomInspection $inspection): void
    {
        static::push(
            static::teamRoleUserIds($inspection->group_name, (int) $inspection->faculty_id, ['front_desk']),
            UserNotification::ROOM_READY,
            'Room ready · ' . $inspection->room_name,
            'Housekeeping finished inspecting and cleaning. The room is available for the next guest.',
            route('students.frontdesk.verify-guest'),
            null
        );
    }

    /**
     * Housekeeping found something missing from the room. The Front Desk hears about
     * it separately from the maintenance complaint itself — they are the ones who
     * would decide whether to follow up with the guest who checked out.
     */
    public static function inspectionItemsMissing(?User $actor, HotelRoomInspection $inspection, HotelComplaint $complaint): void
    {
        static::push(
            static::teamRoleUserIds($inspection->group_name, (int) $inspection->faculty_id, ['front_desk']),
            UserNotification::ROOM_INSPECTION,
            'Items missing · ' . $inspection->room_name,
            'Housekeeping reported missing items after ' . ($inspection->guest_name ?: 'the last guest') . '\'s stay: '
                . $complaint->details,
            route('students.frontdesk.verify-guest'),
            $actor
        );
    }
}
