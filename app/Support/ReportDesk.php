<?php

namespace App\Support;

use App\Models\Student;
use App\Models\StudentGroup;
use App\Models\Task;

/**
 * Everything the Reports page shows: the Overview figures, Student Reports,
 * Team Reports and Activity Reports.
 *
 * Faculty and dean read the same page. A faculty sees their own teams; the dean
 * passes no faculty and sees every team. Because two faculties can each have a
 * team with the same name, a team is keyed on faculty and name together.
 */
class ReportDesk
{
    public const ROLE_LABELS = [
        'front_desk' => 'Front Desk',
        'restaurant_management' => 'Restaurant',
        'room_management' => 'Room',
        'maintenance' => 'Maintenance',
        'housekeeping' => 'Housekeeping',
    ];

    /**
     * @param int|null $facultyId One faculty's teams, or null for every team.
     * @param array{team?: ?string, task?: ?string, role?: ?string} $filters
     *        team is a "facultyId|team name" key, task a task title, role a role key.
     * @return array<string, mixed> The variables the reports partial reads.
     */
    public static function build(?int $facultyId, array $filters = []): array
    {
        $roleLabels = self::ROLE_LABELS;
        $scope = fn ($query) => $facultyId ? $query->where('faculty_id', $facultyId) : $query;
        $teamKey = fn ($facultyIdOfRow, $name) => (int) $facultyIdOfRow . '|' . $name;

        $fullRoster = $scope(StudentGroup::with(['roles', 'student.user', 'faculty.user']))->get();

        $fullTasks = $scope(Task::with(['student.user', 'assignedTo']))
            ->orderByDesc('updated_at')
            ->get();

        // Done means approved: handed in and signed off, not just handed in.
        $isApproved = fn (Task $task) => $task->status === 'archived' && $task->feedback_at !== null;

        /*
         * Filter choices, read off everything in scope so picking one filter
         * never empties the lists of the others.
         */
        $multipleFaculties = $fullRoster->pluck('faculty_id')->unique()->count() > 1;
        $teamOptions = $fullRoster->filter(fn ($m) => filled($m->group_name))
            ->unique(fn ($m) => $teamKey($m->faculty_id, $m->group_name))
            ->mapWithKeys(function ($m) use ($teamKey, $multipleFaculties) {
                $facultyName = self::personName($m->faculty?->user);
                $label = $multipleFaculties && $facultyName !== ''
                    ? $m->group_name . ' — ' . $facultyName
                    : $m->group_name;

                return [$teamKey($m->faculty_id, $m->group_name) => $label];
            })
            ->sort()
            ->all();
        $taskOptions = $fullTasks->pluck('title')->filter()->unique()->sort()->values()->all();
        $roleOptions = $roleLabels;

        // Unknown values are dropped rather than trusted, so a stale link shows everything.
        $filters = [
            'team' => array_key_exists((string) ($filters['team'] ?? ''), $teamOptions) ? (string) $filters['team'] : null,
            'task' => in_array((string) ($filters['task'] ?? ''), $taskOptions, true) ? (string) $filters['task'] : null,
            'role' => array_key_exists((string) ($filters['role'] ?? ''), $roleOptions) ? (string) $filters['role'] : null,
        ];
        $isFiltered = array_filter($filters) !== [];

        $rosterRows = $fullRoster;
        $allTasks = $fullTasks;

        if ($filters['team']) {
            [$teamFacultyId, $teamName] = explode('|', $filters['team'], 2);
            $inTeam = fn ($m) => (int) $m->faculty_id === (int) $teamFacultyId
                && strcasecmp((string) $m->group_name, $teamName) === 0;
            $rosterRows = $rosterRows->filter($inTeam);
            $teamStudentIds = $rosterRows->pluck('student_id')->filter()->map(fn ($id) => (int) $id)->all();

            // A task that names its team belongs to that team; older rows that name
            // no team belong to it when one of its members did them.
            $allTasks = $allTasks->filter(fn (Task $task) => (int) $task->faculty_id === (int) $teamFacultyId
                && (filled($task->group_name)
                    ? strcasecmp((string) $task->group_name, $teamName) === 0
                    : in_array((int) $task->student_id, $teamStudentIds, true)));
        }
        if ($filters['role']) {
            $rosterRows = $rosterRows->filter(fn ($m) => $m->roles->pluck('role')->contains($filters['role']));
            $allTasks = $allTasks->filter(fn (Task $task) => $task->role === $filters['role']);
        }
        if ($filters['task']) {
            $allTasks = $allTasks->filter(fn (Task $task) => $task->title === $filters['task']);
        }
        $rosterRows = $rosterRows->values();
        $allTasks = $allTasks->values();

        /*
         * Team Reports: the approved tasks, grouped by the team of the student
         * who did them. Team lookups read the full roster, so a filter never
         * moves a task to another team or drops members from the team's modal.
         */
        $membershipByStudentId = $fullRoster->groupBy('student_id');
        $teamMembersByKey = $fullRoster->groupBy(fn ($m) => $teamKey($m->faculty_id, $m->group_name ?? 'Unassigned'));

        $buckets = [];

        foreach ($allTasks->filter($isApproved) as $task) {
            $studentId = $task->student_id ? (int) $task->student_id : null;
            if (!$studentId && $task->assigned_to) {
                $studentId = Student::where('user_id', $task->assigned_to)->value('user_information_id');
                $studentId = $studentId ? (int) $studentId : null;
            }

            $membership = $studentId
                ? ($membershipByStudentId->get($studentId)?->first())
                : null;

            $teamName = $membership?->group_name ?: 'Unassigned';
            $key = $teamKey($membership?->faculty_id ?? $task->faculty_id, $teamName);

            if (!isset($buckets[$key])) {
                $buckets[$key] = [
                    'id' => $key,
                    'team_name' => $teamName,
                    'faculty_name' => $membership ? self::personName($membership->faculty?->user) : '',
                    'page_roles' => [],
                    'assigned_at' => $task->created_at,
                    'completed_at' => $task->updated_at,
                    'tasks' => [],
                    'members' => [],
                ];
            }

            $roleKey = (string) ($task->role ?? '');
            if ($roleKey !== '') {
                $buckets[$key]['page_roles'][$roleKey] = $roleLabels[$roleKey] ?? $roleKey;
            }

            if ($task->created_at && (!$buckets[$key]['assigned_at'] || $task->created_at->lt($buckets[$key]['assigned_at']))) {
                $buckets[$key]['assigned_at'] = $task->created_at;
            }
            if ($task->updated_at && (!$buckets[$key]['completed_at'] || $task->updated_at->gt($buckets[$key]['completed_at']))) {
                $buckets[$key]['completed_at'] = $task->updated_at;
            }

            $buckets[$key]['tasks'][] = [
                'title' => $task->title,
                'description' => $task->description,
                'student_name' => self::personName($task->student?->user ?? $task->assignedTo) ?: '—',
                'role' => $roleKey,
                'role_label' => $roleLabels[$roleKey] ?? $roleKey,
                'due_date' => optional($task->due_date)->format('M d, Y g:i A'),
                'completed_at' => optional($task->updated_at)->format('M d, Y'),
            ];
        }

        foreach ($buckets as $key => &$bucket) {
            $members = $teamMembersByKey->get($key, collect());
            $bucket['members'] = $members->map(function ($m) use ($roleLabels) {
                $roles = $m->roles->pluck('role')->filter()->values();
                if ($roles->isEmpty() && $m->role) {
                    $roles = collect([$m->role]);
                }

                return [
                    'name' => self::personName($m->student?->user) ?: 'Member',
                    'roles' => $roles->map(fn ($r) => $roleLabels[$r] ?? $r)->values()->all(),
                ];
            })->values()->all();

            $bucket['page_name'] = !empty($bucket['page_roles'])
                ? implode(', ', array_values($bucket['page_roles']))
                : '—';
            $bucket['assigned_date'] = optional($bucket['assigned_at'])->format('M d, Y') ?? '—';
            $bucket['date_completed'] = optional($bucket['completed_at'])->format('M d, Y') ?? '—';
            $bucket['completed_sort'] = optional($bucket['completed_at'])->timestamp ?? 0;
            $bucket['task_count'] = count($bucket['tasks']);
            unset($bucket['page_roles'], $bucket['assigned_at'], $bucket['completed_at']);
        }
        unset($bucket);

        $teamReports = collect($buckets)
            ->sortByDesc('completed_sort')
            ->map(function ($row) {
                unset($row['completed_sort']);
                return $row;
            })
            ->values();

        /*
         * Overview figures, counted off the rosters and the task rows already in
         * memory rather than re-queried per card.
         */
        // Filtered, the count is the students the filter leaves, not the whole class.
        $totalStudents   = $isFiltered
            ? $rosterRows->pluck('student_id')->filter()->unique()->count()
            : $scope(Student::query())->count();
        $totalTeams      = $rosterRows->filter(fn ($m) => $m->group_name)
            ->map(fn ($m) => $teamKey($m->faculty_id, $m->group_name))
            ->unique()
            ->count();
        $totalActivities = $allTasks->count();
        $doneActivities  = $allTasks->filter($isApproved)->count();
        $overallRate     = $totalActivities > 0
            ? (int) round(($doneActivities / $totalActivities) * 100)
            : 0;

        // The window the data actually covers, so the header states a real period
        // rather than offering a picker nothing filters on.
        $reportFrom = $allTasks->min('created_at');
        $reportTo   = $allTasks->max('updated_at');

        // How many students hold each role across every team.
        $roleParticipation = collect($roleLabels)->map(function ($label, $key) use ($rosterRows) {
            return [
                'role'  => $key,
                'label' => $label,
                'count' => $rosterRows->filter(fn ($m) => $m->roles->pluck('role')->contains($key))->count(),
            ];
        })->values();

        /*
         * Per-student completion. Faculty address most tasks to a department
         * rather than a person, so a row counts toward a student when it names
         * them outright or belongs to a role they hold on their own team — the
         * same rule the student's own dashboard uses.
         */
        $studentPerformance = $rosterRows->map(function ($membership) use ($allTasks, $isApproved) {
            $user  = $membership->student?->user;
            $roles = $membership->roles->pluck('role')->filter()->all();

            $mine = $allTasks->filter(function (Task $task) use ($membership, $user, $roles) {
                if ($task->student_id && (int) $task->student_id === (int) $membership->student_id) {
                    return true;
                }
                if ($task->assigned_to && $user && (int) $task->assigned_to === (int) $user->user_id) {
                    return true;
                }

                return (int) $task->faculty_id === (int) $membership->faculty_id
                    && $task->group_name === $membership->group_name
                    && in_array($task->role, $roles, true);
            });

            $total = $mine->count();
            $done  = $mine->filter($isApproved)->count();

            return [
                'name'    => self::personName($user) ?: 'Student',
                'team'    => $membership->group_name ?? 'Unassigned',
                'user'    => $user,
                'total'   => $total,
                'done'    => $done,
                'percent' => $total > 0 ? (int) round(($done / $total) * 100) : 0,
            ];
        })
            // Filtered by task, a student with no row of that task has nothing to report.
            ->filter(fn ($row) => !$filters['task'] || $row['total'] > 0)
            ->sortByDesc(fn ($row) => [$row['percent'], $row['done']])
            ->values();

        /* The latest completed work across every team: handed in and approved. A
           task still being worked on or waiting on review is not completed, and a
           report that lists it as such is a to-do list. */
        $teamByStudentId = $fullRoster->keyBy('student_id');
        $recentActivities = $allTasks
            ->filter($isApproved)
            ->take(6)
            ->map(function (Task $task) use ($teamByStudentId, $roleLabels) {
                $user = $task->student?->user ?? $task->assignedTo;

                return [
                    'date'       => optional($task->updated_at)->format('M d, Y'),
                    'time'       => optional($task->updated_at)->format('g:i A'),
                    'student'    => self::personName($user) ?: 'Unclaimed',
                    'user'       => $user,
                    'team'       => $task->group_name ?: ($teamByStudentId->get((int) $task->student_id)?->group_name ?? '—'),
                    'activity'   => $task->title,
                    'role_label' => $roleLabels[$task->role] ?? $task->role,
                    'status'     => 'Completed',
                ];
            })
            ->values();

        return compact(
            'teamReports',
            'totalStudents',
            'totalTeams',
            'totalActivities',
            'doneActivities',
            'overallRate',
            'reportFrom',
            'reportTo',
            'roleParticipation',
            'studentPerformance',
            'recentActivities',
            'filters',
            'isFiltered',
            'teamOptions',
            'taskOptions',
            'roleOptions'
        );
    }

    /** Last, first, middle — the order every report table lists people in. */
    private static function personName(mixed $user): string
    {
        if (!$user) {
            return '';
        }

        return trim(implode(' ', array_filter([
            $user->last_name,
            $user->first_name,
            $user->middle_name,
        ]))) ?: (string) ($user->name ?? '');
    }
}
