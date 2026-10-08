<?php

namespace App\Support;

use App\Models\Group;
use App\Models\HotelAmenity;
use App\Models\HotelMenuCategory;
use App\Models\HotelMenuItem;
use App\Models\HotelRoomCategory;
use App\Models\StudentGroup;
use App\Models\Task;
use App\Models\TeamRoleTemplate;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Schema;

/**
 * The two phases a team moves through: Hotel Customization, then Hotel Simulation.
 *
 * The simulation opens only on faculty's word. Every task on the checklist
 * (TaskChecklist) is required, and each must carry an approval that still
 * stands — a task faculty never assigned counts as Not Started, so it holds the
 * lock as surely as one sent back. Ticked steps and submissions move nothing
 * here; only an approval does. Faculty sending an approved task back is what
 * withdraws it, and the lock returns with it, because this is computed on every
 * read rather than stored.
 *
 * Opening the door also needs faculty to have confirmed who runs which desk
 * (simulation_roles_confirmed_at). The Simulation role is stored apart from the
 * Customization seat, so the seat a student customized under is never rewritten.
 */
class SimulationPhase
{
    public const STATUS_LABELS = [
        'not_started' => 'Not Started',
        'in_progress' => 'In Progress',
        'pending' => 'Pending Review',
        'revision' => 'Revision Required',
        'approved' => 'Approved',
    ];

    /** Which copy of a task speaks for it when a role's work fans out to several rows. */
    private const RANK = ['not_started' => 0, 'in_progress' => 1, 'revision' => 2, 'pending' => 3, 'approved' => 4];

    /** @var array<string, array> per-request memo, keyed by team */
    private static array $memo = [];

    /** Where one task row stands, in the five states the workflow names. */
    public static function taskState(Task $task): string
    {
        return match (true) {
            $task->status === 'archived' && $task->feedback_at !== null => 'approved',
            $task->status === 'archived' => 'pending',
            $task->needs_revision => 'revision',
            $task->activitiesDoneCount() > 0 => 'in_progress',
            default => 'not_started',
        };
    }

    /**
     * The team's approval progress, and whether its simulation is open.
     *
     * @return array{
     *     required: int, approved: int, pending: int, revision: int, in_progress: int,
     *     not_started: int, percent: int, unlocked: bool, roles_confirmed: bool,
     *     can_start: bool, items: list<array{role: string, title: string, state: string, label: string}>
     * }
     */
    public static function progress(?string $groupName, ?int $facultyId): array
    {
        $key = $facultyId . '|' . $groupName;
        if (isset(self::$memo[$key])) {
            return self::$memo[$key];
        }

        $counts = array_fill_keys(array_keys(self::STATUS_LABELS), 0);
        $items = [];

        if ($groupName && $facultyId) {
            $rows = Task::where('faculty_id', $facultyId)
                ->where('group_name', $groupName)
                ->get()
                ->groupBy(fn (Task $t) => $t->role . '|' . mb_strtolower(trim((string) $t->title)));

            foreach (TaskChecklist::all() as $role => $entries) {
                foreach ($entries as $entry) {
                    $title = $entry['title'];

                    if (TaskChecklist::isConceptTitle($title)) {
                        $state = self::conceptState($groupName, $facultyId);
                    } else {
                        $state = 'not_started';
                        foreach ($rows->get($role . '|' . mb_strtolower($title), []) as $row) {
                            $rowState = self::taskState($row);
                            if (self::RANK[$rowState] > self::RANK[$state]) {
                                $state = $rowState;
                            }
                        }
                    }

                    $counts[$state]++;
                    $items[] = ['role' => $role, 'title' => $title, 'state' => $state, 'label' => self::STATUS_LABELS[$state]];
                }
            }
        }

        $required = count($items);
        $unlocked = $required > 0 && $counts['approved'] === $required;
        $confirmed = self::rolesConfirmed($groupName, $facultyId);

        return self::$memo[$key] = $counts + [
            'required' => $required,
            // Floored, so a team one approval short never reads 100%.
            'percent' => $required ? (int) floor($counts['approved'] / $required * 100) : 0,
            'unlocked' => $unlocked,
            'roles_confirmed' => $confirmed,
            'can_start' => $unlocked && $confirmed,
            'items' => $items,
        ];
    }

    /** Forget the memo after a write that moves a team's progress in this request. */
    public static function forget(): void
    {
        self::$memo = [];
    }

    public static function canStart(?StudentGroup $membership): bool
    {
        return $membership
            && self::progress($membership->group_name, (int) $membership->faculty_id)['can_start'];
    }

    /** The concept is approved on its own rows, not on the task row. */
    private static function conceptState(string $groupName, int $facultyId): string
    {
        $statuses = HotelConceptDesk::conceptsFor($groupName, $facultyId)->pluck('status')->all();

        return match (true) {
            in_array(HotelConceptDesk::STATUS_APPROVED, $statuses, true) => 'approved',
            in_array(HotelConceptDesk::STATUS_SUBMITTED, $statuses, true) => 'pending',
            in_array(HotelConceptDesk::STATUS_NEEDS_REVISION, $statuses, true) => 'revision',
            $statuses !== [] => 'in_progress',
            default => 'not_started',
        };
    }

    /*
    | Simulation roles
    */

    /** Whether the columns from the add_simulation_role_columns migration exist yet. */
    public static function supported(): bool
    {
        static $has = null;

        return $has ??= Schema::hasColumn('student_groups', 'simulation_role')
            && Schema::hasColumn('groups', 'simulation_roles_confirmed_at');
    }

    /**
     * The one Simulation seat this member holds: what faculty set, or else the
     * key of their Customization seat — the Housekeeping & Maintenance seat
     * defaults to Housekeeping, Room Management to Room & Maintenance.
     */
    public static function seatFor(?StudentGroup $membership): ?string
    {
        if (!$membership) {
            return null;
        }

        $set = self::supported() ? $membership->simulation_role : null;
        if ($set && isset(HotelTemplateBuilder::SIMULATION_ROLES[$set])) {
            return $set;
        }

        $seats = $membership->roles->pluck('role')->filter()->values()->all();

        return $seats[0] ?? null;
    }

    public static function rolesConfirmed(?string $groupName, ?int $facultyId): bool
    {
        if (!$groupName || !$facultyId || !self::supported()) {
            return false;
        }

        return Group::where('group_name', $groupName)
            ->where('faculty_id', $facultyId)
            ->whereNotNull('simulation_roles_confirmed_at')
            ->exists();
    }

    /*
    | Edited after approval
    */

    /**
     * When each role's part of the site last changed, for flagging approved work
     * that was edited after faculty accepted it. A hint for faculty, who decide
     * whether to send it back; nothing is withdrawn automatically. Room rows are
     * left out — their status moves with every check-in.
     *
     * @return array<string, Carbon|null>
     */
    public static function lastEdits(string $groupName, int $facultyId): array
    {
        $team = fn ($query) => $query->where('group_name', $groupName)->where('faculty_id', $facultyId);
        $latest = fn (...$times) => collect($times)->filter()->map(fn ($t) => Carbon::parse($t))->max();

        $templates = $team(TeamRoleTemplate::query())
            ->get(['role', 'updated_at'])
            ->mapWithKeys(fn ($t) => [$t->role => $t->updated_at])
            ->all();

        return [
            'front_desk' => $templates['front_desk'] ?? null,
            'room_management' => $latest(
                $templates['room_management'] ?? null,
                $team(HotelRoomCategory::query())->max('updated_at')
            ),
            'restaurant_management' => $latest(
                $templates['restaurant_management'] ?? null,
                $team(HotelMenuCategory::query())->max('updated_at'),
                $team(HotelMenuItem::query())->max('updated_at')
            ),
            'housekeeping' => $latest(
                $templates['housekeeping'] ?? null,
                $team(HotelAmenity::query())->max('updated_at')
            ),
        ];
    }

    /** Whether this approved task's department page changed after the approval. */
    public static function editedSinceApproval(Task $task, array $lastEdits): bool
    {
        $edited = $lastEdits[$task->role] ?? null;

        return self::taskState($task) === 'approved'
            && !$task->is_hotel_concept
            && $edited
            && Carbon::parse($edited)->gt($task->feedback_at);
    }
}
