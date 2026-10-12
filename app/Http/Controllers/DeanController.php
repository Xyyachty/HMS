<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use App\Events\StudentApproved;
use App\Models\ActivityLog;
use App\Models\Faculty;
use App\Models\Student;
use App\Models\StudentGroup;
use App\Models\Task;
use App\Models\User;
use App\Rules\PhilippineMobile;
use App\Support\Notifier;
use PhpOffice\PhpSpreadsheet\IOFactory;

class DeanController extends Controller
{
    public function dashboard()
    {
        $totalStudents = Student::count();
        $totalFaculty = Faculty::count();
        $pendingFaculty = User::where('role', 'faculty')
            ->where('status', 'pending')
            ->count();

        // A team is a (faculty, name) pair. Counted as DISTINCT rows rather than
        // a CONCAT of the two: the old raw SQL quoted its separator with double
        // quotes, which PostgreSQL reads as an identifier rather than a string.
        $totalTeams = StudentGroup::query()
            ->select('faculty_id', 'group_name')
            ->distinct()
            ->get()
            ->count();

        $studentsThisMonth = Student::where('created_at', '>=', now()->startOfMonth())->count();
        $studentsLastMonth = Student::whereBetween('created_at', [
            now()->subMonth()->startOfMonth(),
            now()->subMonth()->endOfMonth(),
        ])->count();

        if ($studentsLastMonth > 0) {
            $studentTrend = (int) round((($studentsThisMonth - $studentsLastMonth) / $studentsLastMonth) * 100);
        } else {
            $studentTrend = $studentsThisMonth > 0 ? 100 : 0;
        }

        $teamsThisMonth = StudentGroup::query()
            ->where('created_at', '>=', now()->startOfMonth())
            ->select('faculty_id', 'group_name')
            ->distinct()
            ->get()
            ->count();

        $recentActivity = Task::with(['faculty.user', 'student.user', 'assignedTo'])
            ->orderByDesc('updated_at')
            ->take(5)
            ->get();

        // Newly: the student and faculty accounts added most recently.
        $newAccounts = User::whereIn('role', ['student', 'faculty'])
            ->latest()
            ->take(5)
            ->get();

        $roleLabels = [
            'front_desk' => 'Front Desk',
            'restaurant_management' => 'Restaurant',
            'room_management' => 'Room Mgmt',
            'maintenance' => 'Maintenance',
            'housekeeping' => 'Housekeeping',
        ];

        // One pass over every task in the system feeds the Recent list and the
        // completion figure on the Teams card.
        $allTasks = Task::whereIn('status', ['active', 'archived'])
            ->get(['task_id', 'title', 'faculty_id', 'group_name', 'role', 'status', 'created_at']);

        $completedTasks = $allTasks->where('status', 'archived')->count();
        $completionRate = $allTasks->count() > 0
            ? (int) round(($completedTasks / $allTasks->count()) * 100)
            : 0;

        // Faculty display names, resolved once so the Recent list can label a
        // task with the instructor who owns it.
        $facultyNames = Faculty::with('user')
            ->get()
            ->mapWithKeys(function ($faculty) {
                $user = $faculty->user;
                $name = trim(implode(' ', array_filter([
                    $user?->first_name,
                    $user?->last_name,
                ]))) ?: ($user?->name ?? 'Faculty');

                return [$faculty->user_information_id => $name];
            });

        // Recent: the open tasks handed out most recently.
        $recentTasks = $allTasks
            ->where('status', 'active')
            ->sortByDesc('created_at')
            ->take(5)
            ->values()
            ->map(function ($task) use ($facultyNames) {
                $task->faculty_name = $facultyNames[$task->faculty_id] ?? null;

                return $task;
            });

        $teamProgress = self::teamProgress($facultyNames);
        $customizationPercent = self::averagePercent($teamProgress);
        $completedTeams = $teamProgress->where('complete', true)->count();

        return view('dean.dashboard', compact(
            'totalStudents',
            'totalFaculty',
            'pendingFaculty',
            'totalTeams',
            'studentTrend',
            'teamsThisMonth',
            'recentActivity',
            'roleLabels',
            'newAccounts',
            'recentTasks',
            'completedTasks',
            'completionRate',
            'teamProgress',
            'customizationPercent',
            'completedTeams'
        ));
    }

    /**
     * Customization progress for every team in every block, keyed "faculty_id|team".
     *
     * Faculty-approved required tasks out of all required (SimulationPhase::progress),
     * the same figure the faculty dashboard shows, so a team is "complete" only once
     * faculty approved every one, never on student submissions alone.
     */
    private static function teamProgress($facultyNames = null)
    {
        $facultyNames ??= Faculty::with('user')->get()->mapWithKeys(fn ($f) => [
            $f->user_information_id => trim(implode(' ', array_filter([$f->user?->first_name, $f->user?->last_name]))) ?: ($f->user?->name ?? 'Faculty'),
        ]);

        return StudentGroup::with('student.facultyClass.faculty')
            ->get()
            ->groupBy(fn ($member) => $member->faculty_id . '|' . $member->group_name)
            ->map(function ($members, $key) use ($facultyNames) {
                $first = $members->first();
                $phase = \App\Support\SimulationPhase::progress($first->group_name, (int) $first->faculty_id);
                $block = $members->map(fn ($m) => $m->student?->facultyClass?->block_letter)->filter()->first();

                return [
                    'key'      => $key,
                    'name'     => (string) $first->group_name,
                    'block'    => $block ? 'Block ' . $block : null,
                    'faculty'  => $facultyNames[$first->faculty_id] ?? null,
                    'total'    => $phase['required'],
                    'done'     => $phase['approved'],
                    'percent'  => $phase['percent'],
                    'unlocked' => $phase['unlocked'],
                    'complete' => $phase['required'] > 0 && $phase['approved'] >= $phase['required'],
                ];
            })
            ->sortBy(fn ($team) => [$team['block'] ?? 'ZZZ', mb_strtolower($team['name'])]);
    }

    private static function averagePercent($teamProgress): int
    {
        return $teamProgress->isEmpty() ? 0 : (int) round($teamProgress->avg('percent'));
    }

    private static function completionSummary($teamProgress): string
    {
        $done = $teamProgress->where('complete', true)->count();

        return 'Average across ' . $teamProgress->count() . ' ' . \Illuminate\Support\Str::plural('team', $teamProgress->count())
            . ' · ' . $done . ' completed · ' . ($teamProgress->count() - $done) . ' in progress';
    }

    /**
     * The completion indicators, polled by partials.team-completion-live so the dean
     * dashboard and Teams Overview catch a faculty approval without a reload.
     */
    public function teamProgressLive()
    {
        abort_unless(auth()->user()?->role === 'dean', 403);

        $teams = self::teamProgress();

        return response()->json([
            'percent' => self::averagePercent($teams),
            'summary' => self::completionSummary($teams),
            'teams' => $teams->map(fn ($team) => [
                'done' => $team['done'],
                'total' => $team['total'],
                'percent' => $team['percent'],
                'pill' => view('partials.team-completion', ['done' => $team['done'], 'total' => $team['total']])->render(),
            ]),
        ]);
    }

    public function users()
    {
        $users = User::with(['faculty.classes', 'student.facultyClass.faculty'])->latest()->get();
        $availableBlocks = Faculty::availableBlocks();
        $systemBlocks = Faculty::existingClassLetters();

        return view('dean.usermanagement', compact('users', 'availableBlocks', 'systemBlocks'));
    }

    public function usersLive()
    {
        $users = User::with(['faculty.classes', 'student.facultyClass.faculty'])->latest()->get()->map(function ($user) {
            $displayName = trim(implode(' ', array_filter([
                $user->last_name ?? null,
                $user->first_name ?? null,
                $user->middle_name ?? null,
            ])));

            $displayName = $displayName !== '' ? $displayName : ($user->name ?? 'User');
            $phone = $user->phone_number ?? ($user->faculty->phone_number ?? null);
            $username = '';
            $emailDomain = 'hms.edu';
            if (!empty($user->email)) {
                $parts = explode('@', $user->email, 2);
                $username = $parts[0];
                if (!empty($parts[1])) {
                    $emailDomain = $parts[1];
                }
            }

            $block = null;
            $blockLabel = null;
            if ($user->role === 'faculty') {
                $block = $user->faculty->block ?? null;
                $blockLabel = $user->faculty?->handledBlocksLabel();
            } elseif ($user->role === 'student') {
                // The block the student sits in, read off their faculty's block (FacultyClass::block_letter)
                $block = $user->student?->facultyClass?->block_letter ?? null;
            }

            return [
                'id' => $user->user_id,
                'name' => $displayName,
                'first_name' => $user->first_name,
                'middle_name' => $user->middle_name,
                'last_name' => $user->last_name,
                'username' => $username,
                'email_domain' => $emailDomain,
                'email' => $user->email,
                'phone_number' => $phone,
                'role' => $user->role,
                'block' => $block ? strtoupper((string) $block) : null,
                'block_label' => $blockLabel ?? Faculty::blockLabel($block),
                'status' => $user->status ?? ($user->faculty->status ?? 'active'),
                'joined' => optional($user->created_at)->format('M d, Y'),
            ];
        });

        return response()->json($users);
    }

    public function faculties()
    {
        $faculties = Faculty::with(['user', 'studentGroups.student.user', 'studentGroups.roles'])
            ->latest()
            ->get();

        $availableBlocks = Faculty::availableBlocks();

        $completedTasks = \App\Models\Task::with(['faculty.user', 'student.user', 'assignedTo', 'feedbackBy'])
            ->where('status', 'archived')
            ->orderByDesc('updated_at')
            ->get();
        $compliedGroups = self::compliedGroups($completedTasks);

        $roleLabels = [
            'front_desk' => 'Front Desk',
            'restaurant_management' => 'Restaurant',
            'room_management' => 'Rooms',
            'maintenance' => 'Maintenance',
            'housekeeping' => 'Housekeeping',
        ];

        // Every task each team has been given, at any stage — the dean monitors
        // work in progress, not only what has been handed in. Read-only: nothing
        // on the Teams page writes to a task.
        $allTasks = Task::with(['student.user', 'assignedTo'])
            ->withoutSimulation()
            ->orderBy('task_id')
            ->get()
            ->groupBy('faculty_id');

        $teamActivityByFacultyGroup = [];
        foreach ($faculties as $faculty) {
            $facultyId = (int) $faculty->user_information_id;
            $facultyTasks = $allTasks->get($facultyId, collect());
            $groups = $faculty->studentGroups
                ? $faculty->studentGroups->groupBy('group_name')
                : collect();

            foreach ($groups as $groupName => $members) {
                $memberStudentIds = $members->pluck('student_id')->filter()->map(fn ($id) => (int) $id)->unique()->all();
                $memberRoles = $members
                    ->flatMap(function ($m) {
                        $fromRelation = $m->roles->pluck('role');
                        return $fromRelation->isNotEmpty() ? $fromRelation : collect([$m->role])->filter();
                    })
                    ->filter()
                    ->unique()
                    ->values()
                    ->all();

                $teamActivityByFacultyGroup[$facultyId][$groupName] = $facultyTasks
                    ->filter(function ($task) use ($groupName, $memberStudentIds, $memberRoles) {
                        // A row that names its team belongs to that team only. Case-
                        // insensitive because group_name is citext in the database.
                        if (filled($task->group_name)) {
                            return strcasecmp((string) $task->group_name, (string) $groupName) === 0;
                        }
                        // Rows from before tasks named a team: same fallback as Task::scopeForTeam().
                        if ($task->student_id && in_array((int) $task->student_id, $memberStudentIds, true)) {
                            return true;
                        }

                        return in_array($task->role, $memberRoles, true);
                    })
                    ->map(fn ($task) => self::teamTaskRow($task, $roleLabels) + [
                        // The team's live site, read-only, as its students last saved it.
                        // The hotel concept is a proposal, not a site change, so it has none.
                        'preview_url' => $task->is_hotel_concept ? null : route('faculty.teams.preview', [
                            'faculty' => $facultyId,
                            'group' => $groupName,
                            'role' => $task->role,
                        ]),
                    ])
                    ->sortBy(fn ($row) => [$row['step'] ?? PHP_INT_MAX, $row['title'], $row['role']])
                    ->values()
                    ->all();
            }
        }

        // The hotel concepts per team, so the list names what each one proposed. Keyed
        // by faculty then group because group names repeat across faculty, and each
        // entry is a list because a team proposes two — until one is decided, when
        // it drops to the winner alone, same rule HotelConceptDesk::visibleConcepts()
        // applies everywhere else.
        $conceptsByFacultyGroup = [];
        $rawConceptsByFacultyGroup = [];
        foreach (\App\Models\HotelConcept::orderBy('slot')->get() as $concept) {
            $rawConceptsByFacultyGroup[(int) $concept->faculty_id][$concept->group_name][] = $concept;
        }
        foreach ($rawConceptsByFacultyGroup as $facultyId => $groups) {
            foreach ($groups as $groupName => $concepts) {
                $conceptsByFacultyGroup[$facultyId][$groupName] =
                    \App\Support\HotelConceptDesk::visibleConcepts(collect($concepts));
            }
        }

        $teamProgress = self::teamProgress();

        return view('dean.faculties', compact(
            'teamProgress',
            'faculties',
            'compliedGroups',
            'teamActivityByFacultyGroup',
            'roleLabels',
            'availableBlocks',
            'conceptsByFacultyGroup'
        ));
    }

    /**
     * One task as the dean's Team Details modal lists it.
     *
     * Status follows the same signals the rest of the app reads: 'archived' is
     * handed in, feedback_at on an archived row is faculty's approval, feedback
     * on an active row is a send-back (Task::needs_revision), and a ticked
     * activity is what separates In Progress from Not Started.
     */
    private static function teamTaskRow(Task $task, array $roleLabels): array
    {
        $step = \App\Support\TaskChecklist::stepForTitle((string) $task->title);
        $activities = $task->activityList();
        $done = $task->activitiesDoneCount();

        [$status, $statusLabel] = match (true) {
            $task->status === 'archived' && $task->feedback_at !== null => ['completed', 'Completed'],
            $task->status === 'archived' => ['submitted', 'Submitted'],
            $task->needs_revision => ['needs_revision', 'Needs Revision'],
            $done > 0 => ['in_progress', 'In Progress'],
            default => ['not_started', 'Not Started'],
        };

        $user = $task->student?->user ?? $task->assignedTo;
        $studentName = $user
            ? (trim(implode(' ', array_filter([$user->last_name, $user->first_name]))) ?: (string) $user->name)
            : null;

        return [
            'step' => $step,
            'code' => $step === null ? null : 'TASK ' . str_pad((string) ($step + 1), 2, '0', STR_PAD_LEFT),
            'title' => (string) $task->title,
            'role' => (string) $task->role,
            'role_label' => $roleLabels[$task->role] ?? $task->role,
            'student' => $studentName,
            'due_date' => optional($task->due_date)->format('M d, Y'),
            'status' => $status,
            'status_label' => $statusLabel,
            'progress_done' => $done,
            'progress_total' => count($activities),
        ];
    }

    /**
     * The Complied tab: every handed-in task, grouped by team, in words a
     * non-technical reader follows. "Approved" only once faculty approved it;
     * handed in but not yet checked is "Waiting for faculty".
     *
     * Handed-in time is the snapshot taken at submission (submitted_version_id),
     * not updated_at, which an approval moves too. Times are campus time.
     */
    private static function compliedGroups($tasks)
    {
        $handedInAt = \App\Models\TeamRoleTemplateVersion::whereIn(
            'team_role_template_version_id',
            $tasks->pluck('submitted_version_id')->filter()->unique()->all()
        )->pluck('created_at', 'team_role_template_version_id');

        $blocks = StudentGroup::with('student.facultyClass.faculty')->get()
            ->groupBy(fn ($m) => $m->faculty_id . '|' . mb_strtolower((string) $m->group_name))
            ->map(fn ($members) => $members->map(fn ($m) => $m->student?->facultyClass?->block_letter)->filter()->first());

        $name = function ($user, bool $lastFirst = true) {
            if (!$user) {
                return null;
            }
            $parts = $lastFirst ? [$user->last_name, $user->first_name] : [$user->first_name, $user->last_name];
            $full = trim(implode(' ', array_filter($parts)));
            if ($lastFirst && $user->last_name && $user->first_name) {
                $full = $user->last_name . ', ' . $user->first_name;
            }

            return $full !== '' ? $full : $user->name;
        };
        $when = fn ($at) => $at ? \Illuminate\Support\Carbon::parse($at)->timezone('Asia/Manila')->format('M j, Y \a\t g:i A') : null;

        return $tasks
            ->map(function (Task $task) use ($handedInAt, $name, $when) {
                $handedIn = $handedInAt[$task->submitted_version_id] ?? $task->updated_at;
                $late = $task->due_date && $handedIn && \Illuminate\Support\Carbon::parse($handedIn)->gt($task->due_date);

                return [
                    'group_key'   => $task->faculty_id . '|' . mb_strtolower((string) $task->group_name),
                    'team'        => $task->group_name ?: null,
                    'faculty_id'  => $task->faculty_id,
                    'faculty'     => $name($task->faculty?->user, false) ?? 'Faculty',
                    'title'       => (string) $task->title,
                    'department'  => $task->role_label,
                    'student'     => $name($task->student?->user ?? $task->assignedTo) ?? 'A student',
                    'handed_in'   => $when($handedIn),
                    'handed_in_ts'=> $handedIn ? \Illuminate\Support\Carbon::parse($handedIn)->timestamp : 0,
                    'timing'      => $task->due_date ? ($late ? 'late' : 'on_time') : null,
                    'approved'    => $task->feedback_at !== null,
                    'approved_by' => $name($task->feedbackBy, false),
                    'approved_at' => $when($task->feedback_at),
                ];
            })
            ->groupBy('group_key')
            ->map(function ($rows, $key) use ($blocks) {
                $first = $rows->first();
                $block = $blocks[$key] ?? null;

                return [
                    'team'     => $first['team'] ?? 'No team named',
                    'block'    => $block ? 'Block ' . $block : null,
                    'faculty'  => $first['faculty'],
                    'approved' => $rows->where('approved', true)->count(),
                    'waiting'  => $rows->where('approved', false)->count(),
                    // Waiting first: that is the work still needing someone.
                    'rows'     => $rows->sortBy(fn ($r) => [$r['approved'] ? 1 : 0, -$r['handed_in_ts']])->values(),
                ];
            })
            ->sortBy(fn ($g) => [$g['block'] ?? 'ZZZ', mb_strtolower($g['team'])])
            ->values();
    }

    /**
     * Read-only review of one submitted task, where the dean's notification
     * lands. Same payload the faculty review dialog reads; the verdict stays
     * with the faculty, so there is no feedback form here.
     */
    public function reviewTask(Task $task)
    {
        abort_unless(auth()->user()?->role === 'dean', 403);

        $facultyId = (int) $task->faculty_id;
        $review = app(FacultyController::class)->reviewPayload($task, $facultyId, ['faculty' => $facultyId]);

        return view('dean.task-review', compact('review'));
    }

    public function storeFaculty(Request $request)
    {
        $validated = $request->validate([
            'first_name' => ['required', 'string', 'max:255'],
            'middle_name' => ['nullable', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'phone_number' => ['nullable', 'string', new PhilippineMobile()],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        /* Active, whatever arrives. An account created switched off is one
           nobody can sign in to; deactivating is done to an account that
           exists, on the update form. */
        $status = 'active';

        /* The block is not asked for. It is always the next free class letter,
           so there was nothing for the dean to decide and picking the one
           already taken stopped the account being created at all. It can still
           be changed afterwards, where changing it is the point. */
        $block = Faculty::nextAvailableBlock();

        $fullName = trim(implode(' ', array_filter([
            $validated['first_name'],
            $validated['middle_name'] ?? null,
            $validated['last_name'],
        ])));

        $user = User::create([
            'name' => $fullName,
            'first_name' => $validated['first_name'],
            'middle_name' => User::cleanOptional($validated['middle_name'] ?? null),
            'last_name' => $validated['last_name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => 'faculty',
            'email_verified_at' => now(),
        ]);

        Faculty::create([
            'user_id' => $user->user_id,
            'phone_number' => User::normalizePhone($validated['phone_number'] ?? null),
            'status' => $status,
            'block' => $block,
        ]);

        ActivityLog::recordFor(
            ActivityLog::ACCOUNT_CREATED,
            'Created faculty account for ' . $fullName . ' (block ' . $block . ').'
        );

        Notifier::accountCreated(auth()->user(), $user, 'faculty');

        return redirect()->route('dean.faculties')
            ->with('success', 'Faculty account created successfully — assigned ' . Faculty::blockLabel($block) . '.');
    }

    public function storeUser(Request $request)
    {
        $validated = $request->validate([
            'first_name' => ['required', 'string', 'max:255'],
            'middle_name' => ['nullable', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'phone_number' => ['nullable', 'string', new PhilippineMobile()],
            'role' => ['required', 'in:faculty,student'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        // Active, whatever arrives - see storeFaculty().
        $status = 'active';

        $fullName = trim(implode(' ', array_filter([
            $validated['first_name'],
            $validated['middle_name'] ?? null,
            $validated['last_name'],
        ])));

        $userData = [
            'name' => $fullName,
            'first_name' => $validated['first_name'],
            'middle_name' => User::cleanOptional($validated['middle_name'] ?? null),
            'last_name' => $validated['last_name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => $validated['role'],
            'email_verified_at' => now(),
        ];

        $userData['status'] = $status;
        $userData['phone_number'] = User::normalizePhone($validated['phone_number'] ?? null);

        $user = User::create($userData);

        if ($validated['role'] === 'faculty') {
            // Assigned, not asked for - see storeFaculty().
            Faculty::create([
                'user_id' => $user->user_id,
                'phone_number' => User::normalizePhone($validated['phone_number'] ?? null),
                'status' => $status,
                'block' => Faculty::nextAvailableBlock(),
            ]);
        }

        $message = $validated['role'] === 'faculty'
            ? 'Faculty account created successfully.'
            : 'Student account created successfully.';

        ActivityLog::recordFor(
            ActivityLog::ACCOUNT_CREATED,
            'Created ' . $validated['role'] . ' account for ' . $fullName . ' (' . $validated['email'] . ').'
        );

        Notifier::accountCreated(auth()->user(), $user, $validated['role']);

        return redirect()->route('dean.users')->with('success', $message);
    }

    public function updateUser(Request $request, User $user)
    {
        $rules = [
            'status' => ['required', 'in:active,inactive'],
        ];

        if ($user->role === 'faculty') {
            $selectable = Faculty::selectableBlocksForFaculty(
                $user->faculty?->user_information_id,
                $user->faculty?->block
            );
            $rules['block'] = [
                'required',
                'string',
                'in:' . implode(',', $selectable ?: ['__none__']),
            ];
        }

        $validated = $request->validate($rules);

        $user->status = $validated['status'];

        $user->save();

        if ($user->role === 'faculty' && $user->faculty) {
            $user->faculty->status = $validated['status'];
            $block = strtoupper($validated['block']);
            if (!Faculty::isValidBlock($block)) {
                return redirect()->route('dean.users')->withErrors(['block' => 'Invalid block selected.']);
            }
            $user->faculty->block = $block;
            $user->faculty->save();
        }

        ActivityLog::recordFor(
            ActivityLog::ACCOUNT_UPDATED,
            'Updated ' . ($user->role ?: 'user') . ' account ' . ($user->name ?? $user->email)
                . ' — status set to ' . $validated['status'] . '.'
        );

        return redirect()->route('dean.users')->with('success', 'User updated successfully.');
    }

    /**
     * The same Reports page faculty see, across every faculty's teams: the
     * Overview, Student Reports, Team Reports and Activity Reports.
     */
    public function reports(Request $request)
    {
        ActivityLog::recordFor(ActivityLog::REPORT_GENERATED, 'Generated the dean performance report.');

        return view('dean.reports', \App\Support\ReportDesk::build(null, $request->only(['team', 'task', 'role'])));
    }

    public function bulkUpload(Request $request)
    {
        $request->validate([
            'file' => ['required', 'file', 'mimes:xlsx,xls,csv', 'max:10240'],
        ]);

        $file = $request->file('file');
        $spreadsheet = IOFactory::load($file->getPathname());
        $worksheet = $spreadsheet->getActiveSheet();
        $rows = $worksheet->toArray();

        if (empty($rows)) {
            return redirect()->route('dean.users')->withErrors(['file' => 'The uploaded file is empty.']);
        }

        $headers = array_map('strtolower', array_map('trim', $rows[0]));
        $requiredColumns = ['first_name', 'last_name', 'email', 'role'];
        
        foreach ($requiredColumns as $col) {
            if (!in_array($col, $headers)) {
                return redirect()->route('dean.users')->withErrors(['file' => "Missing required column: {$col}"]);
            }
        }

        $successCount = 0;
        $errorCount = 0;
        $errors = [];

        for ($i = 1; $i < count($rows); $i++) {
            $row = $rows[$i];
            if (empty(array_filter($row))) {
                continue;
            }

            $data = array_combine($headers, $row);
            
            $validator = Validator::make($data, [
                'first_name' => ['required', 'string', 'max:255'],
                'last_name' => ['required', 'string', 'max:255'],
                'middle_name' => ['nullable', 'string', 'max:255'],
                'email' => ['required', 'email', 'max:255', 'unique:users,email'],
                'phone_number' => ['nullable', 'string', 'max:30'],
                'role' => ['required', 'in:faculty,student'],
                'block' => ['nullable', 'string', 'in:' . implode(',', Faculty::existingClassLetters() ?: ['A'])],
                'status' => ['nullable', 'in:active,inactive'],
                'password' => ['nullable', 'string'],
            ]);

            if ($validator->fails()) {
                $errorCount++;
                $errors[] = "Row " . ($i + 1) . ": " . $validator->errors()->first();
                continue;
            }

            $validated = $validator->validated();
            $fullName = trim(implode(' ', array_filter([
                $validated['first_name'],
                $validated['middle_name'] ?? null,
                $validated['last_name'],
            ])));

            $password = !empty($validated['password']) ? $validated['password'] : 'password';

            $userData = [
                'name' => $fullName,
                'first_name' => $validated['first_name'],
                'middle_name' => User::cleanOptional($validated['middle_name'] ?? null),
                'last_name' => $validated['last_name'],
                'email' => $validated['email'],
                'password' => Hash::make($password),
                'role' => $validated['role'],
                'email_verified_at' => now(),
            ];

            $userData['status'] = $validated['status'] ?? 'active';
            $userData['phone_number'] = User::cleanOptional($validated['phone_number'] ?? null);

            try {
                if ($validated['role'] === 'faculty') {
                    $block = strtoupper(trim((string) ($validated['block'] ?? '')));
                    $available = Faculty::availableBlocks();
                    if ($block === '' || !in_array($block, $available, true)) {
                        if (empty($available)) {
                            $errorCount++;
                            $errors[] = "Row " . ($i + 1) . ": No available blocks left for faculty.";
                            continue;
                        }
                        $block = $available[0];
                    }
                }

                $user = User::create($userData);

                if ($validated['role'] === 'faculty') {
                    Faculty::create([
                        'user_id' => $user->user_id,
                        'phone_number' => User::cleanOptional($validated['phone_number'] ?? null),
                        'status' => $validated['status'] ?? 'active',
                        'block' => $block,
                    ]);
                }

                $successCount++;
            } catch (\Exception $e) {
                $errorCount++;
                $errors[] = "Row " . ($i + 1) . ": " . $e->getMessage();
            }
        }

        $message = "{$successCount} users imported successfully.";
        if ($errorCount > 0) {
            $message .= " {$errorCount} rows failed.";
        }

        if ($successCount > 0) {
            ActivityLog::recordFor(
                ActivityLog::ACCOUNT_CREATED,
                'Bulk imported ' . $successCount . ' user account(s)'
                    . ($errorCount > 0 ? ', ' . $errorCount . ' row(s) failed' : '') . '.'
            );
        }

        if ($errorCount > 0 && count($errors) <= 10) {
            session()->flash('upload_errors', $errors);
        }

        return redirect()->route('dean.users')->with('success', $message);
    }

    public function activityLogs()
    {
        // One section per faculty instead of repeating the name on every row.
        // Newest first, so groupBy keeps the most recently active faculty on top.
        // ponytail: loads every finished task; page by faculty if this reaches thousands.
        $tasks = Task::with('faculty.user')
            ->where('status', 'archived')
            ->orderByDesc('updated_at')
            ->get();

        $byFaculty = $tasks->groupBy('faculty_id');

        return view('dean.activitylogs', compact('tasks', 'byFaculty'));
    }

    public function approveUser(User $user)
    {
        if ($user->role !== 'student') {
            return redirect()->route('dean.users')->withErrors(['status' => 'Only student accounts can be approved.']);
        }

        $user->status = 'active';
        $user->save();

        $user->load('student');
        event(new StudentApproved($user, $user->student));

        ActivityLog::recordFor(
            ActivityLog::ACCOUNT_APPROVED,
            'Approved student account ' . ($user->name ?? $user->email) . '.'
        );

        return redirect()->route('dean.users')->with('success', 'Student account approved successfully.');
    }

}
