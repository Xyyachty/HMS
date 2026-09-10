<?php

namespace App\Support;

use App\Models\ActivityLog;
use App\Models\StudentGroup;
use App\Models\Task;

/**
 * The three reports the dean reads the simulation through.
 *
 * A task is one shared step of the project and the activities inside it are one
 * role's share each (see TaskChecklist), so a report about "TASK 01" is a report
 * about a group of rows: one per role, fanned out per member holding it. That
 * grouping is what this class does — the screens then only draw it.
 *
 * Everything is read from the rows themselves. Nothing here writes, and nothing
 * is invented: a team with no completed work simply does not appear.
 */
class DeanReportDesk
{
    public const ROLE_LABELS = [
        'front_desk' => 'Front Desk',
        'room_management' => 'Room Management',
        'restaurant_management' => 'Restaurant',
        'housekeeping' => 'Housekeeping',
        'maintenance' => 'Maintenance',
    ];

    /** 'archived' is this app's "handed in" — see the tasks.complete route. */
    private const SUBMITTED = 'archived';

    /**
     * Completed work, one row per team and task.
     *
     * A row is listed once any of its activities has been handed in, because the
     * tab is about work that has been done rather than work that exists. The badge
     * then says how far it got: every activity approved reads Completed, all in
     * and waiting reads Submitted, part-way reads In Progress, and anything still
     * open past its due date reads Overdue.
     *
     * @return array<int, array<string, mixed>>
     */
    public static function completedTasks(): array
    {
        $tasks = Task::with(['student.user', 'assignedTo', 'faculty.user'])
            ->orderBy('task_id')
            ->get();

        if ($tasks->isEmpty()) {
            return [];
        }

        $teams = self::teamIndex();
        $stepByTitle = self::stepByTitle();
        // Read once: a row written before tasks named their team falls back to the
        // team its student is on, and asking per row was a query per task.
        $teamByStudentId = StudentGroup::get()
            ->mapWithKeys(fn ($m) => [(int) $m->student_id => (string) $m->group_name]);

        $groups = [];

        foreach ($tasks as $task) {
            $teamName = (string) ($task->group_name ?: $teamByStudentId->get((int) $task->student_id, ''));
            if ($teamName === '') {
                // A row belonging to no team is a row no team's report can carry.
                continue;
            }

            /* Grouped by the step, not by the title. A task is one shared step and
               the activities inside it are one role's share each — Front Desk
               proposing the hotel concepts while Room Management sets up its
               categories is one TASK 01, not two. A title that is not on the
               checklist (a one-off a faculty wrote) has no step, so it groups under
               itself and reports alone. */
            $step = $stepByTitle[mb_strtolower((string) $task->title)] ?? null;
            $bucket = $step !== null ? 'step:' . $step : 'title:' . mb_strtolower((string) $task->title);
            $key = ((int) $task->faculty_id) . '::' . mb_strtolower($teamName) . '::' . $bucket;

            if (!isset($groups[$key])) {
                $team = $teams[self::teamKey((int) $task->faculty_id, $teamName)] ?? null;
                $groups[$key] = [
                    'id' => $key,
                    'team_name' => $team['name'] ?? $teamName,
                    'faculty_name' => $team['faculty_name'] ?? self::facultyName($task),
                    'member_count' => $team['member_count'] ?? 0,
                    'members' => $team['members'] ?? [],
                    'task_no' => $step !== null ? $step + 1 : null,
                    'task_title' => $step !== null ? 'TASK ' . str_pad((string) ($step + 1), 2, '0', STR_PAD_LEFT) : (string) $task->title,
                    'activity_titles' => [],
                    'roles' => [],
                    'activities' => [],
                    'assigned_at' => null,
                    'completed_at' => null,
                    'submitted_count' => 0,
                    'approved_count' => 0,
                ];
            }

            $group = &$groups[$key];
            $roleKey = (string) ($task->role ?? '');
            $submitted = $task->status === self::SUBMITTED;
            $approved = $submitted && $task->feedback_at !== null;

            if ($roleKey !== '') {
                $group['roles'][$roleKey] = ($group['roles'][$roleKey] ?? 0) + ($submitted ? 1 : 0);
            }

            /* A report is a record of work done. An activity still being worked on
               is not part of one: it is not listed, not counted, and not exported,
               and it turns up in the diary the team works from instead. */
            if (!$submitted) {
                if ($task->created_at && (!$group['assigned_at'] || $task->created_at->lt($group['assigned_at']))) {
                    $group['assigned_at'] = $task->created_at;
                }
                unset($group);
                continue;
            }

            $group['activity_titles'][(string) $task->title] = true;

            $group['activities'][] = [
                'role' => $roleKey,
                'role_label' => self::ROLE_LABELS[$roleKey] ?? $roleKey,
                'title' => (string) $task->title,
                'description' => self::firstParagraph((string) $task->description),
                'student_name' => self::personName($task->student?->user ?? $task->assignedTo) ?: 'Unclaimed',
                // Handed in either way; this says whether faculty has signed it off.
                'status' => $approved ? 'Approved' : 'Awaiting approval',
                'assigned_date' => optional($task->created_at)->format('M d, Y'),
                'submitted_date' => $submitted ? optional($task->updated_at)->format('M d, Y g:i A') : null,
                'reviewed_date' => optional($task->feedback_at)->format('M d, Y g:i A'),
                'feedback' => $task->feedback,
                'steps' => $task->activityList(),
                'has_comparison' => (bool) $task->submitted_version_id,
            ];

            $group['submitted_count']++;
            if ($approved) {
                $group['approved_count']++;
            }

            if ($task->created_at && (!$group['assigned_at'] || $task->created_at->lt($group['assigned_at']))) {
                $group['assigned_at'] = $task->created_at;
            }
            if ($submitted && $task->updated_at && (!$group['completed_at'] || $task->updated_at->gt($group['completed_at']))) {
                $group['completed_at'] = $task->updated_at;
            }

            unset($group);
        }

        $rows = [];

        foreach ($groups as $group) {
            if ($group['submitted_count'] === 0) {
                // Nothing handed in: this is work in the diary, not a report.
                continue;
            }

            $group['task_description'] = implode(' · ', array_keys($group['activity_titles']));
            $group['roles'] = self::roleBadges($group['roles']);
            $group['total_activities'] = array_sum(array_column($group['roles'], 'count'));
            $group['status'] = 'Completed';
            $group['assigned_date'] = optional($group['assigned_at'])->format('M d, Y') ?? '—';
            $group['completed_date'] = optional($group['completed_at'])->format('M d, Y') ?? '—';
            $group['assigned_sort'] = optional($group['assigned_at'])->timestamp ?? 0;
            $group['completed_sort'] = optional($group['completed_at'])->timestamp ?? 0;

            unset(
                $group['assigned_at'], $group['completed_at'], $group['activity_titles'],
                $group['submitted_count'], $group['approved_count']
            );

            $rows[] = $group;
        }

        usort($rows, fn ($a, $b) => $b['completed_sort'] <=> $a['completed_sort']);

        return array_values($rows);
    }

    /**
     * Every student who holds a role on a team, and how their work stands.
     *
     * Counted from their own task rows: assigned is every row that landed on them,
     * submitted is every row handed in, approved is the subset faculty accepted,
     * and pending is what is left. The percentage is approved over assigned —
     * handing work in is not the same as finishing it.
     *
     * @return array<int, array<string, mixed>>
     */
    public static function studentPerformance(): array
    {
        $memberships = StudentGroup::with(['roles', 'student.user'])->get();
        if ($memberships->isEmpty()) {
            return [];
        }

        $tasksByStudent = Task::all()->groupBy('student_id');

        $rows = [];

        foreach ($memberships as $membership) {
            $studentId = (int) ($membership->student_id ?? 0);
            if (!$studentId) {
                continue;
            }

            $student = $membership->student;
            $tasks = $tasksByStudent->get($studentId, collect());

            $assigned = $tasks->count();
            $submitted = $tasks->where('status', self::SUBMITTED)->count();
            $approved = $tasks->filter(fn (Task $t) => $t->status === self::SUBMITTED && $t->feedback_at !== null)->count();
            $pending = max(0, $assigned - $submitted);
            $percent = $assigned > 0 ? (int) round(($approved / $assigned) * 100) : 0;

            $roles = $membership->roles->pluck('role')->filter()->values();

            $rows[] = [
                'student_name' => self::personName($student?->user) ?: 'Student',
                'student_no' => $student?->student_number ?: '—',
                'team_name' => (string) ($membership->group_name ?: 'Unassigned'),
                'roles' => $roles->map(fn ($r) => ['key' => $r, 'label' => self::ROLE_LABELS[$r] ?? $r])->values()->all(),
                'assigned' => $assigned,
                'submitted' => $submitted,
                'approved' => $approved,
                'pending' => $pending,
                'percent' => $percent,
                'status' => self::performanceStatus($assigned, $submitted, $approved, $percent),
            ];
        }

        usort($rows, fn ($a, $b) => [$a['team_name'], $a['student_name']] <=> [$b['team_name'], $b['student_name']]);

        return $rows;
    }

    /**
     * The activity log, with the team each entry belongs to worked out from the
     * person who made it — the log stores who and what, never which team.
     *
     * @return array<int, array<string, mixed>>
     */
    public static function activityLogs(int $limit = 500): array
    {
        $logs = ActivityLog::with('user')
            ->orderByDesc('activity_log_id')
            ->limit($limit)
            ->get();

        if ($logs->isEmpty()) {
            return [];
        }

        $teamByUserId = StudentGroup::with('student')
            ->get()
            ->mapWithKeys(fn ($m) => [(int) ($m->student?->user_id ?? 0) => (string) $m->group_name])
            ->filter(fn ($name, $userId) => $userId > 0);

        return $logs->map(function (ActivityLog $log) use ($teamByUserId) {
            $description = (string) ($log->description ?? '');

            return [
                'at' => optional($log->created_at)->format('M d, Y g:i A') ?? '—',
                'at_sort' => optional($log->created_at)->timestamp ?? 0,
                'user_name' => self::personName($log->user) ?: 'System',
                'role' => self::ROLE_LABELS[$log->role] ?? ucfirst(str_replace('_', ' ', (string) $log->role)),
                'team_name' => $teamByUserId->get((int) $log->user_id, '—') ?: '—',
                'activity' => $log->activity_label,
                'activity_key' => (string) $log->activity,
                'description' => $description,
                // The task an entry is about, when it names one in quotes.
                'task' => self::quotedTitle($description),
                'result' => self::logResult((string) $log->activity),
            ];
        })->values()->all();
    }

    /* ── helpers ─────────────────────────────────────────────────────────── */

    /** @return array<string, array<string, mixed>> */
    private static function teamIndex(): array
    {
        $out = [];

        foreach (StudentGroup::with(['roles', 'student.user', 'faculty.user'])->get() as $membership) {
            $name = (string) ($membership->group_name ?? '');
            if ($name === '') {
                continue;
            }

            $key = self::teamKey((int) $membership->faculty_id, $name);

            if (!isset($out[$key])) {
                $out[$key] = [
                    'name' => $name,
                    'faculty_name' => self::personName($membership->faculty?->user) ?: '—',
                    'member_count' => 0,
                    'members' => [],
                ];
            }

            $out[$key]['member_count']++;
            $out[$key]['members'][] = [
                'name' => self::personName($membership->student?->user) ?: 'Member',
                'roles' => $membership->roles
                    ->pluck('role')
                    ->filter()
                    ->map(fn ($r) => self::ROLE_LABELS[$r] ?? $r)
                    ->values()
                    ->all(),
            ];
        }

        return $out;
    }

    private static function teamKey(int $facultyId, string $teamName): string
    {
        return $facultyId . '::' . mb_strtolower($teamName);
    }

    /** Task title (lowercased) => its zero-based step on the checklist. */
    private static function stepByTitle(): array
    {
        static $map = null;

        if ($map === null) {
            $map = [];
            foreach (TaskChecklist::allByStep() as $step => $tasks) {
                foreach ($tasks as $task) {
                    $map[mb_strtolower($task['title'])] = $step;
                }
            }
        }

        return $map;
    }

    /**
     * The badges a row carries: one per role with work in this task, counting the
     * activities of that role that were actually handed in.
     *
     * @return array<int, array{key: string, label: string, count: int}>
     */
    private static function roleBadges(array $counts): array
    {
        $out = [];

        /* Only the roles that actually handed something in. A role given the step
           and still working on it is not a badge saying "(0)" — it is a role with
           nothing to report yet, and the row's status is what says so. */
        foreach (array_keys(self::ROLE_LABELS) as $role) {
            if (($counts[$role] ?? 0) < 1) {
                continue;
            }
            $out[] = ['key' => $role, 'label' => self::ROLE_LABELS[$role], 'count' => (int) $counts[$role]];
        }

        // A role not on the standard list still reports under its own name.
        foreach ($counts as $role => $count) {
            if ($count > 0 && !array_key_exists($role, self::ROLE_LABELS)) {
                $out[] = ['key' => $role, 'label' => ucfirst(str_replace('_', ' ', $role)), 'count' => (int) $count];
            }
        }

        return $out;
    }

    private static function performanceStatus(int $assigned, int $submitted, int $approved, int $percent): string
    {
        if ($assigned === 0) {
            return 'No work yet';
        }
        if ($approved === $assigned) {
            return 'Completed';
        }
        if ($submitted === $assigned) {
            return 'Awaiting review';
        }
        if ($percent >= 50 || $submitted > 0) {
            return 'On track';
        }

        return 'Behind';
    }

    private static function logResult(string $activity): string
    {
        return match ($activity) {
            ActivityLog::TASK_SUBMITTED => 'Submitted',
            ActivityLog::EVALUATION_RECORDED => 'Reviewed',
            ActivityLog::TASK_CREATED => 'Assigned',
            ActivityLog::TASK_DELETED => 'Removed',
            ActivityLog::WEBSITE_CUSTOMIZED, ActivityLog::OUTPUT_UPLOADED => 'Saved',
            ActivityLog::LOGIN, ActivityLog::LOGOUT => 'Session',
            default => 'Recorded',
        };
    }

    /** The first thing between double quotes — how every log names a task. */
    private static function quotedTitle(string $description): ?string
    {
        return preg_match('/"([^"]{2,120})"/', $description, $m) ? $m[1] : null;
    }

    /** The description without the activities appended to it. */
    private static function firstParagraph(string $description): string
    {
        $cut = mb_strpos($description, "\n\n");

        return trim($cut === false ? $description : mb_substr($description, 0, $cut));
    }

    private static function personName(mixed $user): string
    {
        if (!$user) {
            return '';
        }

        $name = trim(implode(' ', array_filter([
            $user->last_name ?? null,
            $user->first_name ?? null,
            $user->middle_name ?? null,
        ])));

        return $name !== '' ? $name : (string) ($user->name ?? '');
    }

    private static function facultyName(Task $task): string
    {
        return self::personName($task->faculty?->user) ?: '—';
    }
}
