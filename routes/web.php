<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ActivityLogController;
use App\Http\Controllers\FacultyController;
use App\Http\Controllers\DeanController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\HotelTemplateController;
use App\Models\ActivityLog;
use App\Models\HotelFoodOrder;
use App\Models\HotelMenuItem;
use App\Models\HotelRoom;
use App\Models\StudentGroup;
use App\Models\Task;
use App\Models\GroupSettings;
use Illuminate\Http\Request;


/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return view('landingpage');
});

// Auth Routes
Route::get('/login', [AuthController::class, 'login'])->middleware('guest')->name('login');
Route::post('/login', [AuthController::class, 'authenticate'])->middleware('guest')->name('login.submit');
Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth')->name('logout');
Route::get('/forgot-password', [AuthController::class, 'forgotPassword'])->middleware('guest')->name('forgot-password');
Route::post('/forgot-password', [AuthController::class, 'forgotPasswordSubmit'])->middleware('guest')->name('forgot-password.submit');
Route::get('/forgot-password/check-email', [AuthController::class, 'checkForgotPasswordEmail'])
    ->middleware('guest')
    ->name('forgot-password.check-email');

// Dean Routes
Route::prefix('dean')->middleware('auth')->name('dean.')->group(function () {
    Route::get('/dashboard', [DeanController::class, 'dashboard'])->name('dashboard');
    Route::get('/users', [DeanController::class, 'users'])->name('users');
    Route::get('/users/live', [DeanController::class, 'usersLive'])->name('users.live');
    Route::post('/users', [DeanController::class, 'storeUser'])->name('users.store');
    Route::post('/users/bulk-upload', [DeanController::class, 'bulkUpload'])->name('users.bulk-upload');
    Route::put('/users/{user}', [DeanController::class, 'updateUser'])->name('users.update');
    Route::post('/users/{user}/approve', [DeanController::class, 'approveUser'])->name('users.approve');
    Route::get('/faculties', [DeanController::class, 'faculties'])->name('faculties');
    Route::post('/faculties', [DeanController::class, 'storeFaculty'])->name('faculties.store');
    Route::get('/reports', [DeanController::class, 'reports'])->name('reports');
    Route::get('/activity', [DeanController::class, 'activityLogs'])->name('activity');
    // Centralized activity log — one member at a time, gated by ActivityLogAccess
    Route::get('/activity/user/{user}', [ActivityLogController::class, 'forUser'])->name('activity.user');
});

// Faculty Routes
Route::prefix('faculty')->middleware('auth')->name('faculty.')->group(function () {
    Route::get('/dashboard', [FacultyController::class, 'dashboard'])->name('dashboard');
    Route::get('/profile', [FacultyController::class, 'profile'])->name('profile');
    Route::put('/profile', [FacultyController::class, 'updateProfile'])->name('profile.update');
    Route::get('/students', [FacultyController::class, 'students'])->name('students');
    Route::get('/students/live', [FacultyController::class, 'studentsLive'])->name('students.live');
    Route::post('/students', [FacultyController::class, 'storeStudent'])->name('students.store');
    Route::put('/students/{userId}', [FacultyController::class, 'updateStudent'])->name('students.update');
    Route::post('/students/bulk', [FacultyController::class, 'bulkImportStudents'])->name('students.bulk');
    Route::get('/role', [FacultyController::class, 'role'])->name('role');
    Route::post('/role/groups', [FacultyController::class, 'storeGroup'])->name('role.groups.store');
    Route::put('/role/groups/{groupName}', [FacultyController::class, 'updateGroup'])->name('role.groups.update');
    Route::get('/tasks', [FacultyController::class, 'tasks'])->name('tasks');
    Route::post('/tasks', [FacultyController::class, 'storeTask'])->name('tasks.store');
    Route::delete('/tasks/{task}', [FacultyController::class, 'destroyTask'])->name('tasks.destroy');
    // Review one submission: the student's actual work, plus feedback back to them.
    Route::get('/tasks/{task}/review', [FacultyController::class, 'reviewTask'])->name('tasks.review');
    Route::post('/tasks/{task}/feedback', [FacultyController::class, 'storeTaskFeedback'])->name('tasks.feedback');
    // Read-only render of a team's live site so faculty can see the work itself.
    Route::get('/teams/preview', [FacultyController::class, 'previewTeamSite'])->name('teams.preview');
    Route::get('/results', [FacultyController::class, 'results'])->name('results');
    Route::get('/reports', [FacultyController::class, 'reports'])->name('reports');
    Route::get('/activity', [FacultyController::class, 'activityLogs'])->name('activity');
    // Same centralized log; faculty only sees students they manage
    Route::get('/activity/user/{user}', [ActivityLogController::class, 'forUser'])->name('activity.user');

    Route::get('/templates/grants', [HotelTemplateController::class, 'facultyGrants'])->name('templates.grants');
    Route::post('/templates/grants', [HotelTemplateController::class, 'facultyGrantStore'])->name('templates.grants.store');
});

// Student Routes
Route::prefix('students')->middleware('auth')->name('students.')->group(function () {
    Route::get('/dashboard', function () {
        $authUser = auth()->user();
        $student  = $authUser?->student;

        $groupMembership = $student
            ? StudentGroup::with('student.user')
                ->where('student_id', $student->id)
                ->first()
            : null;

        $groupMembers = collect();
        $group        = null;
        $facultyId    = null;

        if ($groupMembership) {
            $facultyId  = $groupMembership->faculty_id;
            $groupName  = $groupMembership->group_name;
            $groupMembers = StudentGroup::with('student.user', 'roles')
                ->where('group_name', $groupName)
                ->get()
                ->map(function ($member) {
                    $user = $member->student?->user;
                    $displayName = trim(implode(' ', array_filter([
                        $user?->last_name  ?? null,
                        $user?->first_name ?? null,
                        $user?->middle_name ?? null,
                    ])));
                    $displayName = $displayName !== '' ? $displayName : ($user?->name ?? 'Student');

                    return (object) [
                        'id'         => $user?->id,
                        'student_id' => $member->student_id,
                        'name'       => $displayName,
                        'email'      => $user?->email,
                        'roles'      => $member->roles->pluck('role')->toArray(),
                    ];
                });

            $group = (object) [
                'name'    => $groupName,
                'members' => $groupMembers,
            ];
        }

        $membersByRole = collect();
        foreach ($groupMembers as $member) {
            foreach ($member->roles as $role) {
                if (!isset($membersByRole[$role])) {
                    $membersByRole[$role] = collect();
                }
                $membersByRole[$role]->push($member->name);
            }
        }
        $membersByRole = $membersByRole->map(fn($members) => $members->unique()->values());

        // ── Real tasks from faculty ──────────────────────────────────────
        $tasksByRole = collect();
        $taskCounts  = [
            'front_desk' => 0,
            'restaurant_management' => 0,
            'room_management' => 0,
            'maintenance' => 0,
            'housekeeping' => 0,
        ];
        $completedTasksCount = 0;
        $pendingTasksCount = 0;
        $completionRate = 0;
        $recentTasks = collect();

        if ($facultyId) {
            $allTasks = Task::where('faculty_id', $facultyId)
                ->where('status', 'active')
                ->orderBy('due_date')
                ->orderByPriority()
                ->get();

            $tasksByRole = $allTasks->groupBy('role');

            foreach (array_keys($taskCounts) as $role) {
                $taskCounts[$role] = $tasksByRole->get($role, collect())->count();
            }

            $completedTasksCount = Task::where('faculty_id', $facultyId)
                ->where('status', 'archived')
                ->count();

            $pendingTasksCount = Task::where('faculty_id', $facultyId)
                ->where('status', 'active')
                ->count();

            $totalAllTasks = $completedTasksCount + $pendingTasksCount;
            $completionRate = $totalAllTasks > 0 ? round(($completedTasksCount / $totalAllTasks) * 100) : 0;

            $recentTasks = Task::with(['student.user', 'assignedTo'])
                ->where('faculty_id', $facultyId)
                ->latest('updated_at')
                ->take(12)
                ->get();
        }

        $totalTasks   = array_sum($taskCounts);
        $studentRoles = $groupMembership ? $groupMembership->roles->pluck('role')->toArray() : [];
        $myRoleTasks  = $tasksByRole->filter(fn($tasks, $role) => in_array($role, $studentRoles))->flatten();

        $myCompletedTasks = $facultyId && $student
            ? Task::where('faculty_id', $facultyId)
                ->where('status', 'archived')
                ->where(function ($q) use ($student, $authUser, $studentRoles) {
                    $q->where('student_id', $student->id)
                        ->orWhere('assigned_to', $authUser->id);
                    if (!empty($studentRoles)) {
                        $q->orWhereIn('role', $studentRoles);
                    }
                })
                ->orderByDesc('updated_at')
                ->get()
                ->unique('id')
                ->values()
            : collect();

        // Centralized activity log — a student only ever sees their own rows.
        $myActivityLogs = $authUser
            ? \App\Support\ActivityLogAccess::logsFor($authUser, 100)
            : collect();

        $selfActivityLogs = $facultyId && $student
            ? Task::where('faculty_id', $facultyId)
                ->where(function ($q) use ($student, $authUser, $studentRoles) {
                    // Only this user's own history
                    $q->where('student_id', $student->id)
                        ->orWhere('assigned_to', $authUser->id);

                    // Still show active tasks for their roles so they can complete them
                    if (!empty($studentRoles)) {
                        $q->orWhere(function ($active) use ($studentRoles) {
                            $active->where('status', 'active')
                                ->whereIn('role', $studentRoles);
                        });
                    }
                })
                ->orderByDesc('updated_at')
                ->take(50)
                ->get()
                ->unique('id')
                ->values()
            : collect();

        // Used for teammate Activity modal + reports (not shown in Activity Logs nav)
        $teamActivityLogs = $facultyId
            ? Task::with(['student.user', 'assignedTo'])
                ->where('faculty_id', $facultyId)
                ->orderByDesc('updated_at')
                ->take(50)
                ->get()
            : collect();

        $studentDisplayName = trim(implode(' ', array_filter([
            $authUser?->first_name,
            $authUser?->last_name,
        ]))) ?: ($authUser?->name ?? 'Student');

        $studentClass = $student?->facultyClass;

        return view('students.dashboard', compact(
            'membersByRole', 'group', 'groupMembers',
            'tasksByRole', 'taskCounts', 'totalTasks',
            'studentRoles', 'myRoleTasks', 'completedTasksCount',
            'pendingTasksCount', 'completionRate', 'recentTasks',
            'myCompletedTasks', 'selfActivityLogs', 'teamActivityLogs',
            'myActivityLogs',
            'studentDisplayName', 'studentClass'
        ));
    })->name('dashboard');

    // My Activity — students read their own centralized log and nobody else's.
    Route::get('/activity/mine', [ActivityLogController::class, 'mine'])->name('activity.mine');

    // Team Activity — ActivityLogAccess limits a student to their own group mates.
    Route::get('/activity/user/{user}', [ActivityLogController::class, 'forUser'])->name('activity.user');

    Route::post('/tasks/{task}/complete', function (Task $task) {
        $authUser = auth()->user();
        $student = $authUser?->student;
        if (!$student) {
            abort(403);
        }

        $groupMembership = StudentGroup::with('roles')->where('student_id', $student->id)->first();
        if (!$groupMembership || (int) $task->faculty_id !== (int) $groupMembership->faculty_id) {
            abort(403);
        }

        $studentRoles = $groupMembership->roles->pluck('role')->toArray();
        if (!in_array($task->role, $studentRoles, true)) {
            return back()->withErrors(['task' => 'This task is not assigned to your role.']);
        }

        // Tasks fan out one row per member, so a role match alone is not enough —
        // without this a student could submit a teammate's row. Unclaimed rows
        // (no member held the role at assign time) stay open to the first submitter.
        $claimedByOther = ($task->assigned_to && (int) $task->assigned_to !== (int) $authUser->id)
            || ($task->student_id && (int) $task->student_id !== (int) $student->id);
        if ($claimedByOther) {
            return back()->withErrors(['task' => 'This task belongs to a teammate.']);
        }

        if ($task->status !== 'active') {
            return back()->withErrors(['task' => 'This task has already been submitted.']);
        }

        $task->update([
            'status' => 'archived',
            'student_id' => $student->id,
            'assigned_to' => $authUser->id,
        ]);

        ActivityLog::record(
            $authUser,
            ActivityLog::TASK_SUBMITTED,
            'Submitted task "' . $task->title . '" for the ' . $task->role . ' role.'
        );

        return back()->with('success', 'Task marked as completed.');
    })->name('tasks.complete');
    Route::get('/roommanagement', function () {
        $data = \App\Support\DepartmentTemplatePage::boot(auth()->user(), 'room_management');
        return view('students.roommanagement', $data);
    })->name('roommanagement');
    Route::get('/roommanagement/manage', function () {
        $data = \App\Support\DepartmentTemplatePage::boot(auth()->user(), 'room_management');
        return view('students.roommanagement.manage', $data);
    })->name('roommanagement.manage');
    Route::get('/frontdesk', function () {
        $data = \App\Support\DepartmentTemplatePage::boot(auth()->user(), 'front_desk');
        return view('students.frontdesk.frontdesk', $data);
    })->name('frontdesk');
    Route::get('/frontdesk/verify-guest', function () {
        $data = \App\Support\DepartmentTemplatePage::boot(auth()->user(), 'front_desk');
        return view('students.frontdesk.verify-guest', $data);
    })->name('frontdesk.verify-guest');

    Route::post('/group/presence', function (Request $request) {
        $authUser = auth()->user();
        if (!$authUser) {
            return response()->json(['error' => 'Unauthenticated'], 401);
        }

        $student = $authUser->student;
        $membership = \App\Support\StudentGroupSync::membershipForStudent($student?->id);

        \App\Support\StudentGroupSync::heartbeat($authUser, $membership);

        if (!$membership) {
            return response()->json([
                'online' => [(string) $authUser->id => true],
                'members' => [],
            ]);
        }

        $members = StudentGroup::with('student.user', 'roles')
            ->where('group_name', $membership->group_name)
            ->when($membership->faculty_id, fn ($q) => $q->where('faculty_id', $membership->faculty_id))
            ->get();

        $userIds = $members->map(fn ($m) => $m->student?->user?->id)->filter()->values()->all();
        $online = \App\Support\StudentGroupSync::onlineMap($userIds, $membership);
        $online[(string) $authUser->id] = true;

        $payload = $members->map(function ($m) use ($online) {
            $user = $m->student?->user;
            $uid = (int) ($user?->id ?? 0);
            $key = (string) $uid;
            $displayName = trim(implode(' ', array_filter([
                $user?->last_name,
                $user?->first_name,
                $user?->middle_name,
            ])));
            $displayName = $displayName !== '' ? $displayName : ($user?->name ?? 'Student');

            return [
                'id' => $uid,
                'name' => $displayName,
                'roles' => $m->roles->pluck('role')->values()->all(),
                'online' => (bool) ($online[$key] ?? $online[$uid] ?? false),
            ];
        })->values();

        return response()->json([
            'online' => $online,
            'members' => $payload,
        ]);
    })->name('group.presence');

    // Hotel website simulation auth (Staff redesign vs Customer book)
    Route::get('/hotel-auth/me', [\App\Http\Controllers\HotelSimulationAuthController::class, 'me'])->name('hotel-auth.me');
    Route::post('/hotel-auth/staff/login', [\App\Http\Controllers\HotelSimulationAuthController::class, 'staffLogin'])->name('hotel-auth.staff.login');
    Route::post('/hotel-auth/customer/signup', [\App\Http\Controllers\HotelSimulationAuthController::class, 'customerSignup'])->name('hotel-auth.customer.signup');
    Route::post('/hotel-auth/customer/login', [\App\Http\Controllers\HotelSimulationAuthController::class, 'customerLogin'])->name('hotel-auth.customer.login');
    Route::post('/hotel-auth/logout', [\App\Http\Controllers\HotelSimulationAuthController::class, 'logout'])->name('hotel-auth.logout');

    // Hotel website template builder (per role, team-synced)
    Route::get('/templates/{role}', [HotelTemplateController::class, 'show'])->name('templates.show');
    Route::get('/templates/{role}/sync', [HotelTemplateController::class, 'sync'])->name('templates.sync');
    Route::post('/templates/{role}/save', [HotelTemplateController::class, 'save'])->name('templates.save');
    Route::post('/templates/{role}/autosave', [HotelTemplateController::class, 'autosave'])->name('templates.autosave');
    Route::get('/templates/{role}/versions', [HotelTemplateController::class, 'versions'])->name('templates.versions');
    Route::post('/templates/{role}/versions/{version}/restore', [HotelTemplateController::class, 'restore'])->name('templates.restore');

    Route::get('/frontdesk/template/sync', function () {
        $authUser = auth()->user();
        $student = $authUser?->student;
        $membership = \App\Support\StudentGroupSync::membershipForStudent($student?->id);
        if (!$membership) {
            return response()->json(['error' => 'Group not found'], 404);
        }

        if ($authUser) {
            \App\Support\StudentGroupSync::heartbeat($authUser, $membership);
        }

        $settings = \App\Support\StudentGroupSync::settingsFor($membership);

        return response()->json([
            'selected_template' => $settings?->selected_template,
            'customizations' => $settings?->customizations ?? [],
            'is_published' => (bool) ($settings?->is_published),
            'version' => optional($settings?->updated_at)->timestamp ?? 0,
            'can_edit' => \App\Support\StudentGroupSync::canEditTemplate($membership),
            'updated_by_roles' => \App\Support\StudentGroupSync::roleKeys($membership),
        ]);
    })->name('frontdesk.template.sync');

    Route::get('/frontdesk/template/1', function (Request $request) {
        $customizations = [];
        $canEditTemplate = false;
        $authUser = auth()->user();
        $student = $authUser?->student;
        $roleKeys = array_keys(\App\Support\HotelTemplateBuilder::ROLES);
        $builderRole = $request->query('role', 'front_desk');
        if (!in_array($builderRole, $roleKeys, true)) {
            $builderRole = 'front_desk';
        }
        if ($student) {
            $groupMembership = StudentGroup::with('roles')->where('student_id', $student->id)->first();
            if ($groupMembership) {
                if ($request->query('save') === '1') {
                    if (!\App\Support\StudentGroupSync::canEditTemplate($groupMembership)) {
                        abort(403, 'Only Front Desk role can select a template.');
                    }
                    GroupSettings::updateOrCreate(
                        ['group_name' => $groupMembership->group_name, 'faculty_id' => $groupMembership->faculty_id],
                        ['selected_template' => '1']
                    );
                }
                $customizations = \App\Support\HotelTemplateBuilder::mergeTeamCustomizations(
                    (string) $groupMembership->group_name,
                    (int) $groupMembership->faculty_id
                );
                $canEditTemplate = \App\Support\HotelTemplateBuilder::canEdit($authUser, $groupMembership, $builderRole);
            }
        }

        $editablePages = $canEditTemplate
            ? \App\Support\HotelTemplateBuilder::editablePagesForRole($builderRole)
            : [];

        return view('students.template.1defaulttemplate', compact('customizations', 'canEditTemplate', 'editablePages', 'builderRole'));
    })->name('frontdesk.template.1');

    Route::get('/frontdesk/template/2', function (Request $request) {
        $customizations = [];
        $canEditTemplate = false;
        $authUser = auth()->user();
        $student = $authUser?->student;
        $roleKeys = array_keys(\App\Support\HotelTemplateBuilder::ROLES);
        $builderRole = $request->query('role', 'front_desk');
        if (!in_array($builderRole, $roleKeys, true)) {
            $builderRole = 'front_desk';
        }
        if ($student) {
            $groupMembership = StudentGroup::with('roles')->where('student_id', $student->id)->first();
            if ($groupMembership) {
                if ($request->query('save') === '1') {
                    if (!\App\Support\StudentGroupSync::canEditTemplate($groupMembership)) {
                        abort(403, 'Only Front Desk role can select a template.');
                    }
                    GroupSettings::updateOrCreate(
                        ['group_name' => $groupMembership->group_name, 'faculty_id' => $groupMembership->faculty_id],
                        ['selected_template' => '2']
                    );
                }
                $customizations = \App\Support\HotelTemplateBuilder::mergeTeamCustomizations(
                    (string) $groupMembership->group_name,
                    (int) $groupMembership->faculty_id
                );
                $canEditTemplate = \App\Support\HotelTemplateBuilder::canEdit($authUser, $groupMembership, $builderRole);
            }
        }

        $editablePages = $canEditTemplate
            ? \App\Support\HotelTemplateBuilder::editablePagesForRole($builderRole)
            : [];

        return view('students.template.2defaulttemplate', compact('customizations', 'canEditTemplate', 'editablePages', 'builderRole'));
    })->name('frontdesk.template.2');

    Route::post('/frontdesk/template/select', function (Request $request) {
        \Log::info('Template select request received', [
            'input' => $request->all(),
            'user_id' => auth()->id(),
        ]);

        $request->validate([
            'template' => 'required|in:1,2',
        ]);

        $authUser = auth()->user();
        $student = $authUser?->student;
        if (!$student) {
            \Log::warning('Template select: student not found', ['user_id' => auth()->id()]);
            return response()->json(['error' => 'Student not found'], 404);
        }

        $groupMembership = StudentGroup::with('roles')->where('student_id', $student->id)->first();
        if (!$groupMembership) {
            \Log::warning('Template select: group not found', ['student_id' => $student->id]);
            return response()->json(['error' => 'Group not found'], 404);
        }

        if (!\App\Support\StudentGroupSync::canEditTemplate($groupMembership)) {
            return response()->json([
                'error' => 'Only students with the Front Desk role can select or edit the hotel template. Ask your faculty to assign that role.',
            ], 403);
        }

        \Log::info('Template select: saving', [
            'group' => $groupMembership->group_name,
            'faculty' => $groupMembership->faculty_id,
            'template' => $request->template,
        ]);

        GroupSettings::updateOrCreate(
            [
                'group_name' => $groupMembership->group_name,
                'faculty_id' => $groupMembership->faculty_id,
            ],
            ['selected_template' => $request->template]
        );

        ActivityLog::record(
            $authUser,
            ActivityLog::WEBSITE_CUSTOMIZED,
            'Selected hotel website Template ' . $request->template . ' for team "' . $groupMembership->group_name . '".'
        );

        return response()->json([
            'success' => true,
            'template' => $request->template,
        ]);
    })->name('frontdesk.template.select');

    Route::post('/frontdesk/template/customizations', function (Request $request) {
        $request->validate([
            'customizations' => ['required', 'array'],
            'publish' => ['sometimes', 'boolean'],
        ]);

        $authUser = auth()->user();
        $student = $authUser?->student;
        if (!$student) {
            return response()->json(['error' => 'Student not found'], 404);
        }

        $groupMembership = StudentGroup::with('roles')->where('student_id', $student->id)->first();
        if (!$groupMembership) {
            return response()->json(['error' => 'Group not found'], 404);
        }

        if (!\App\Support\StudentGroupSync::canEditTemplate($groupMembership)) {
            return response()->json([
                'error' => 'Only students with the Front Desk role can edit the hotel template. Ask your faculty to change your roles.',
            ], 403);
        }

        $template = \App\Support\HotelTemplateBuilder::ensureTemplate($groupMembership, 'front_desk');
        $saved = \App\Support\HotelTemplateBuilder::save(
            $template,
            [
                'customizations' => $request->input('customizations', []),
            ],
            $authUser,
            $request->boolean('publish'),
            true,
            $request->boolean('publish') ? 'Published' : 'Front desk save'
        );

        if ($authUser) {
            \App\Support\StudentGroupSync::heartbeat($authUser, $groupMembership);
        }

        ActivityLog::record(
            $authUser,
            ActivityLog::WEBSITE_CUSTOMIZED,
            $request->boolean('publish')
                ? 'Published hotel website customizations to team "' . $groupMembership->group_name . '".'
                : 'Saved hotel website customizations.'
        );

        return response()->json([
            'success' => true,
            'published' => (bool) $saved->is_published,
            'version' => optional($saved->fresh()->updated_at)->timestamp,
        ]);
    })->name('frontdesk.template.customizations');

    Route::post('/frontdesk/template/media', function (Request $request) {
        $request->validate([
            'image' => ['required', 'image', 'max:5120'],
        ]);

        $authUser = auth()->user();
        $student = $authUser?->student;
        if (!$student) {
            return response()->json(['error' => 'Student not found'], 404);
        }

        $groupMembership = StudentGroup::with('roles')->where('student_id', $student->id)->first();
        if (!$groupMembership) {
            return response()->json(['error' => 'Group not found'], 404);
        }

        $canUpload = \App\Support\StudentGroupSync::canEditTemplate($groupMembership);
        if (!$canUpload) {
            foreach (array_keys(\App\Support\HotelTemplateBuilder::ROLES) as $role) {
                if (\App\Support\HotelTemplateBuilder::canEdit($authUser, $groupMembership, $role)) {
                    $canUpload = true;
                    break;
                }
            }
        }
        if (!$canUpload) {
            return response()->json([
                'error' => 'You do not have permission to upload template media.',
            ], 403);
        }

        $folder = 'hotel-media/' . $groupMembership->faculty_id . '/' . $groupMembership->group_name;
        $path = $request->file('image')->store($folder, \App\Support\HotelImageStore::disk());

        ActivityLog::record(
            $authUser,
            ActivityLog::OUTPUT_UPLOADED,
            'Uploaded template media "' . basename($path) . '".'
        );

        return response()->json([
            'success' => true,
            'path' => $path,
            'url' => asset('storage/' . $path),
        ]);
    })->name('frontdesk.template.media');

    Route::match(['get', 'post'], '/frontdesk/template/reservations', function (Request $request) {
        return response()->json(['ok' => true, 'notifications' => []]);
    })->name('frontdesk.template.reservations');

    // ── Hotel Rooms (shared between Room Management & Front Desk) ──────────
    Route::get('/hotel/rooms', function (Request $request) {
        $authUser = auth()->user();
        $student  = $authUser?->student;
        $membership = \App\Support\StudentGroupSync::membershipForStudent($student?->id);
        if (!$membership) {
            return response()->json(['rooms' => []]);
        }
        $rooms = HotelRoom::where('group_name', $membership->group_name)
            ->where('faculty_id', $membership->faculty_id)
            ->orderBy('id')
            ->get()
            ->map(fn ($r) => [
                'id'          => 'db-' . $r->id,
                'dbId'        => $r->id,
                'name'        => $r->name,
                'label'       => $r->category,
                'category'    => $r->category,
                'status'      => $r->status,
                'price'       => (int) $r->price,
                'desc'        => $r->description ?? '',
                'img'         => \App\Support\HotelImageStore::url($r->image),
                'reservation' => $r->reservation ?? null,
                'amenities'   => [['icon'=>'fa-bed','text'=>'Bed'],['icon'=>'fa-wifi','text'=>'WiFi']],
            ]);
        return response()->json(['rooms' => $rooms]);
    })->name('hotel.rooms.index');

    Route::patch('/hotel/rooms/{id}', function (Request $request, $id) {
        $authUser   = auth()->user();
        $student    = $authUser?->student;
        $membership = \App\Support\StudentGroupSync::membershipForStudent($student?->id);
        if (!$membership) {
            return response()->json(['error' => 'Group not found'], 404);
        }
        $room = HotelRoom::where('id', $id)
            ->where('group_name', $membership->group_name)
            ->where('faculty_id', $membership->faculty_id)
            ->firstOrFail();
        if ($request->has('status'))      $room->status      = $request->input('status');
        if ($request->has('reservation')) $room->reservation = $request->input('reservation');
        $room->save();
        return response()->json([
            'room' => [
                'id'          => 'db-' . $room->id,
                'dbId'        => $room->id,
                'name'        => $room->name,
                'label'       => $room->category,
                'category'    => $room->category,
                'status'      => $room->status,
                'price'       => (int) $room->price,
                'desc'        => $room->description ?? '',
                'img'         => \App\Support\HotelImageStore::url($room->image),
                'reservation' => $room->reservation ?? null,
                'amenities'   => [['icon'=>'fa-bed','text'=>'Bed'],['icon'=>'fa-wifi','text'=>'WiFi']],
            ],
        ]);
    })->name('hotel.rooms.update');

    Route::post('/hotel/rooms', function (Request $request) {
        $authUser = auth()->user();
        $student  = $authUser?->student;
        $membership = \App\Support\StudentGroupSync::membershipForStudent($student?->id);
        if (!$membership) {
            return response()->json(['error' => 'Group not found'], 404);
        }
        // The front-end still posts the photo as a base64 data-URL; it is decoded to a
        // file below so only the path reaches the database.
        $data = $request->validate([
            'name'        => 'required|string|max:255',
            'category'    => 'required|string|max:100',
            'price'       => 'required|integer|min:1',
            'description' => 'nullable|string|max:5000',
            'image'       => 'nullable|string|max:900000',
        ], [
            'image.max' => 'That image is too large. Please choose a smaller one.',
        ]);
        $room = HotelRoom::create([
            'group_name'  => $membership->group_name,
            'faculty_id'  => $membership->faculty_id,
            'name'        => $data['name'],
            'category'    => $data['category'],
            'status'      => 'Available',
            'price'       => (int) $data['price'],
            'description' => $data['description'] ?? null,
            'image'       => \App\Support\HotelImageStore::persist(
                $data['image'] ?? null,
                $membership->faculty_id,
                $membership->group_name
            ),
        ]);
        return response()->json([
            'room' => [
                'id'       => 'db-' . $room->id,
                'dbId'     => $room->id,
                'name'     => $room->name,
                'label'    => $room->category,
                'category' => $room->category,
                'status'   => $room->status,
                'price'    => (int) $room->price,
                'desc'     => $room->description ?? '',
                'img'      => \App\Support\HotelImageStore::url($room->image),
                'amenities'=> [['icon'=>'fa-bed','text'=>'Bed'],['icon'=>'fa-wifi','text'=>'WiFi']],
            ],
        ], 201);
    })->name('hotel.rooms.store');

    /*
    |--------------------------------------------------------------------------
    | Hotel restaurant menu (shared per team, Restaurant role writes only)
    |--------------------------------------------------------------------------
    */

    Route::get('/hotel/menus', function (Request $request) {
        $membership = \App\Support\HotelMenuAccess::membership();
        if (!$membership) {
            return response()->json(['items' => [], 'can_manage' => false]);
        }

        \App\Support\HotelMenuAccess::seedDefaults($membership);

        $items = HotelMenuItem::where('group_name', $membership->group_name)
            ->where('faculty_id', $membership->faculty_id)
            ->orderBy('category')
            ->orderBy('name')
            ->get()
            ->map(fn ($item) => $item->toTemplateArray());

        return response()->json([
            'items'      => $items,
            'can_manage' => \App\Support\HotelMenuAccess::canManage($membership),
        ]);
    })->name('hotel.menus.index');

    Route::post('/hotel/menus', function (Request $request) {
        $membership = \App\Support\HotelMenuAccess::membership();
        if (!$membership) {
            return response()->json(['message' => 'Join a hotel team first.'], 404);
        }
        if (!\App\Support\HotelMenuAccess::canManage($membership)) {
            return response()->json(['message' => 'Only Restaurant Services staff can add menu items.'], 403);
        }

        $data = $request->validate([
            'name'        => 'required|string|max:255',
            'category'    => 'required|string|max:100',
            'price'       => 'required|integer|min:1',
            'stock'       => 'nullable|integer|min:0|max:99999',
            'description' => 'nullable|string|max:500',
            'image'       => 'nullable|string|max:900000',
        ], [
            'image.max' => 'That image is too large. Please choose a smaller one.',
        ]);

        $item = HotelMenuItem::create([
            'group_name'  => $membership->group_name,
            'faculty_id'  => $membership->faculty_id,
            'name'        => trim($data['name']),
            'category'    => HotelMenuItem::normalizeCategory($data['category']),
            'price'       => (int) $data['price'],
            'stock'       => (int) ($data['stock'] ?? 0),
            'description' => $data['description'] ?? null,
            'image'       => \App\Support\HotelImageStore::persist(
                $data['image'] ?? null,
                $membership->faculty_id,
                $membership->group_name
            ),
        ]);

        return response()->json(['item' => $item->toTemplateArray()], 201);
    })->name('hotel.menus.store');

    Route::patch('/hotel/menus/{id}', function (Request $request, $id) {
        $membership = \App\Support\HotelMenuAccess::membership();
        if (!$membership) {
            return response()->json(['message' => 'Join a hotel team first.'], 404);
        }
        if (!\App\Support\HotelMenuAccess::canManage($membership)) {
            return response()->json(['message' => 'Only Restaurant Services staff can edit menu items.'], 403);
        }

        $item = HotelMenuItem::where('id', $id)
            ->where('group_name', $membership->group_name)
            ->where('faculty_id', $membership->faculty_id)
            ->firstOrFail();

        $data = $request->validate([
            'name'        => 'sometimes|string|max:255',
            'category'    => 'sometimes|string|max:100',
            'price'       => 'sometimes|integer|min:1',
            'stock'       => 'sometimes|integer|min:0|max:99999',
            'description' => 'sometimes|nullable|string|max:500',
            'image'       => 'sometimes|nullable|string|max:900000',
        ], [
            'image.max' => 'That image is too large. Please choose a smaller one.',
        ]);

        if (array_key_exists('name', $data))        $item->name        = trim($data['name']);
        if (array_key_exists('category', $data))    $item->category    = HotelMenuItem::normalizeCategory($data['category']);
        if (array_key_exists('price', $data))       $item->price       = (int) $data['price'];
        if (array_key_exists('stock', $data))       $item->stock       = (int) $data['stock'];
        if (array_key_exists('description', $data)) $item->description = $data['description'];
        if (array_key_exists('image', $data))       $item->image       = \App\Support\HotelImageStore::persist(
            $data['image'],
            $membership->faculty_id,
            $membership->group_name
        );
        $item->save();

        return response()->json(['item' => $item->toTemplateArray()]);
    })->name('hotel.menus.update');

    Route::delete('/hotel/menus/{id}', function (Request $request, $id) {
        $membership = \App\Support\HotelMenuAccess::membership();
        if (!$membership) {
            return response()->json(['message' => 'Join a hotel team first.'], 404);
        }
        if (!\App\Support\HotelMenuAccess::canManage($membership)) {
            return response()->json(['message' => 'Only Restaurant Services staff can delete menu items.'], 403);
        }

        HotelMenuItem::where('id', $id)
            ->where('group_name', $membership->group_name)
            ->where('faculty_id', $membership->faculty_id)
            ->firstOrFail()
            ->delete();

        return response()->json(['deleted' => true]);
    })->name('hotel.menus.destroy');

    /*
    |--------------------------------------------------------------------------
    | Hotel room-service orders (Front Desk places, Restaurant Services fulfils)
    |--------------------------------------------------------------------------
    */

    Route::get('/hotel/orders', function (Request $request) {
        $membership = \App\Support\HotelOrderAccess::membership();
        if (!$membership) {
            return response()->json(['orders' => [], 'can_place' => false, 'can_fulfill' => false]);
        }

        $orders = HotelFoodOrder::where('group_name', $membership->group_name)
            ->where('faculty_id', $membership->faculty_id)
            ->orderByDesc('id')
            ->limit(200)
            ->get()
            ->map(fn ($order) => $order->toTemplateArray());

        return response()->json([
            'orders'      => $orders,
            'can_place'   => \App\Support\HotelOrderAccess::canPlace($membership),
            'can_fulfill' => \App\Support\HotelOrderAccess::canFulfill($membership),
        ]);
    })->name('hotel.orders.index');

    Route::post('/hotel/orders', function (Request $request) {
        $membership = \App\Support\HotelOrderAccess::membership();
        if (!$membership) {
            return response()->json(['message' => 'Join a hotel team first.'], 404);
        }
        if (!\App\Support\HotelOrderAccess::canPlace($membership)) {
            return response()->json(['message' => 'Only Front Desk staff can place room-service orders.'], 403);
        }

        $data = $request->validate([
            'room_number'         => 'required|string|max:100',
            'guest_name'          => 'required|string|max:255',
            'items'               => 'required|array|min:1|max:50',
            'items.*.name'        => 'required|string|max:255',
            'items.*.menu_item_id' => 'nullable|integer',
            'items.*.dbId'        => 'nullable|integer',
            'items.*.price'       => 'nullable|integer|min:0',
            'items.*.qty'         => 'required|integer|min:1|max:99',
        ]);

        $items = HotelFoodOrder::sanitizeItems($data['items']);
        if (!$items) {
            return response()->json(['message' => 'Add at least one menu item to the order.'], 422);
        }

        try {
            $order = \Illuminate\Support\Facades\DB::transaction(function () use ($membership, $data, $items) {
                $menuItems = \App\Support\HotelOrderAccess::lockMenuItemsFor($membership, $items);

                // Same dish can appear on more than one line; charge stock for the sum.
                $wanted = [];
                foreach ($items as $line) {
                    $menuItem = \App\Support\HotelOrderAccess::matchMenuItem($menuItems, $line);
                    if (!$menuItem) {
                        throw new \RuntimeException("\"{$line['name']}\" is no longer on the menu.");
                    }
                    $wanted[$menuItem->id] = ($wanted[$menuItem->id] ?? 0) + $line['qty'];
                }

                foreach ($wanted as $menuItemId => $qty) {
                    $menuItem = $menuItems->firstWhere('id', $menuItemId);
                    if ($menuItem->stock < $qty) {
                        throw new \RuntimeException("Only {$menuItem->stock} left of \"{$menuItem->name}\".");
                    }
                }

                foreach ($wanted as $menuItemId => $qty) {
                    $menuItem = $menuItems->firstWhere('id', $menuItemId);
                    $menuItem->stock -= $qty;
                    $menuItem->save();
                }

                return HotelFoodOrder::create([
                    'group_name'  => $membership->group_name,
                    'faculty_id'  => $membership->faculty_id,
                    'room_number' => trim($data['room_number']),
                    'guest_name'  => trim($data['guest_name']),
                    'items'       => $items,
                    'total'       => HotelFoodOrder::totalFor($items),
                    'status'      => 'Pending',
                    'placed_by'   => auth()->user()?->name,
                ]);
            });
        } catch (\RuntimeException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }

        return response()->json(['order' => $order->toTemplateArray()], 201);
    })->name('hotel.orders.store');

    Route::patch('/hotel/orders/{id}', function (Request $request, $id) {
        $membership = \App\Support\HotelOrderAccess::membership();
        if (!$membership) {
            return response()->json(['message' => 'Join a hotel team first.'], 404);
        }
        if (!\App\Support\HotelOrderAccess::canFulfill($membership)) {
            return response()->json(['message' => 'Only Restaurant Services staff can update an order.'], 403);
        }

        $order = HotelFoodOrder::where('id', $id)
            ->where('group_name', $membership->group_name)
            ->where('faculty_id', $membership->faculty_id)
            ->firstOrFail();

        $data = $request->validate([
            'status' => 'required|string|max:50',
        ]);

        $next = HotelFoodOrder::normalizeStatus($data['status']);

        // Cancelling puts the portions back on the shelf. Only on the transition,
        // so re-cancelling an already-cancelled order cannot inflate stock.
        if ($next === 'Cancelled' && $order->status !== 'Cancelled') {
            \Illuminate\Support\Facades\DB::transaction(function () use ($order, $membership) {
                $lines = $order->items ?? [];
                $menuItems = \App\Support\HotelOrderAccess::lockMenuItemsFor($membership, $lines);

                foreach ($lines as $line) {
                    $menuItem = \App\Support\HotelOrderAccess::matchMenuItem($menuItems, $line);
                    // A deleted menu item has nowhere to return stock to; the order
                    // still cancels rather than failing on it.
                    if ($menuItem) {
                        $menuItem->stock += (int) $line['qty'];
                        $menuItem->save();
                    }
                }

                $order->status = 'Cancelled';
                $order->save();
            });

            return response()->json(['order' => $order->fresh()->toTemplateArray()]);
        }

        $order->status = $next;
        $order->save();

        return response()->json(['order' => $order->toTemplateArray()]);
    })->name('hotel.orders.update');

    Route::get('/restaurant', function () {
        $data = \App\Support\DepartmentTemplatePage::boot(auth()->user(), 'restaurant_management');
        return view('students.restaurant', $data);
    })->name('restaurant');
    Route::get('/restaurant/manage', function () {
        $data = \App\Support\DepartmentTemplatePage::boot(auth()->user(), 'restaurant_management');
        return view('students.restaurant.manage', $data);
    })->name('restaurant.manage');
    Route::get('/maintenance', function () {
        $data = \App\Support\DepartmentTemplatePage::boot(auth()->user(), 'maintenance');
        return view('students.maintenance', $data);
    })->name('maintenance');
    Route::get('/housekeeping', function () {
        $data = \App\Support\DepartmentTemplatePage::boot(auth()->user(), 'housekeeping');
        return view('students.housekeeping', $data);
    })->name('housekeeping');
});

