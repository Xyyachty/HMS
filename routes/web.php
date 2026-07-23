<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\FacultyController;
use App\Http\Controllers\DeanController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\HotelTemplateController;
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
    Route::get('/results', [FacultyController::class, 'results'])->name('results');
    Route::get('/reports', [FacultyController::class, 'reports'])->name('reports');
    Route::get('/activity', [FacultyController::class, 'activityLogs'])->name('activity');

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
                ->orderByRaw("FIELD(priority,'high','medium','low')")
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
            'studentDisplayName', 'studentClass'
        ));
    })->name('dashboard');

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

        $task->update([
            'status' => 'archived',
            'student_id' => $student->id,
            'assigned_to' => $authUser->id,
        ]);

        return back()->with('success', 'Task marked as completed.');
    })->name('tasks.complete');
    Route::get('/roommanagement', function () {
        $data = \App\Support\DepartmentTemplatePage::boot(auth()->user(), 'room_management');
        return view('students.roommanagement', $data);
    })->name('roommanagement');
    Route::get('/frontdesk', function () {
        $data = \App\Support\DepartmentTemplatePage::boot(auth()->user(), 'front_desk');
        return view('students.frontdesk.frontdesk', $data);
    })->name('frontdesk');

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
                $settings = GroupSettings::where('group_name', $groupMembership->group_name)
                    ->where('faculty_id', $groupMembership->faculty_id)
                    ->first();
                $customizations = $settings?->customizations ?? [];
                $canEditTemplate = \App\Support\StudentGroupSync::canEditTemplate($groupMembership);
            }
        }

        $editablePages = $canEditTemplate
            ? \App\Support\HotelTemplateBuilder::editablePagesForRole('front_desk')
            : [];

        return view('students.template.1defaulttemplate', compact('customizations', 'canEditTemplate', 'editablePages'));
    })->name('frontdesk.template.1');

    Route::get('/frontdesk/template/2', function (Request $request) {
        $customizations = [];
        $canEditTemplate = false;
        $authUser = auth()->user();
        $student = $authUser?->student;
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
                $settings = GroupSettings::where('group_name', $groupMembership->group_name)
                    ->where('faculty_id', $groupMembership->faculty_id)
                    ->first();
                $customizations = $settings?->customizations ?? [];
                $canEditTemplate = \App\Support\StudentGroupSync::canEditTemplate($groupMembership);
            }
        }

        $editablePages = $canEditTemplate
            ? \App\Support\HotelTemplateBuilder::editablePagesForRole('front_desk')
            : [];

        return view('students.template.2defaulttemplate', compact('customizations', 'canEditTemplate', 'editablePages'));
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

        $payload = [
            'customizations' => $request->input('customizations', []),
        ];
        if ($request->boolean('publish')) {
            $payload['is_published'] = true;
        }

        $settings = GroupSettings::updateOrCreate(
            [
                'group_name' => $groupMembership->group_name,
                'faculty_id' => $groupMembership->faculty_id,
            ],
            $payload
        );

        // Force updated_at bump even if JSON content is identical length-wise
        $settings->touch();

        if ($authUser) {
            \App\Support\StudentGroupSync::heartbeat($authUser, $groupMembership);
        }

        return response()->json([
            'success' => true,
            'published' => (bool) $settings->is_published,
            'version' => optional($settings->fresh()->updated_at)->timestamp,
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

        if (!\App\Support\StudentGroupSync::canEditTemplate($groupMembership)) {
            return response()->json([
                'error' => 'Only students with the Front Desk role can upload template media.',
            ], 403);
        }

        $folder = 'hotel-media/' . ($groupMembership->id ?? $student->id);
        $path = $request->file('image')->store($folder, 'public');

        return response()->json([
            'success' => true,
            'url' => asset('storage/' . $path),
        ]);
    })->name('frontdesk.template.media');


    Route::get('/restaurant', function () {
        $data = \App\Support\DepartmentTemplatePage::boot(auth()->user(), 'restaurant_management');
        return view('students.restaurant', $data);
    })->name('restaurant');
    Route::get('/maintenance', function () {
        $data = \App\Support\DepartmentTemplatePage::boot(auth()->user(), 'maintenance');
        return view('students.maintenance', $data);
    })->name('maintenance');
    Route::get('/housekeeping', function () {
        $data = \App\Support\DepartmentTemplatePage::boot(auth()->user(), 'housekeeping');
        return view('students.housekeeping', $data);
    })->name('housekeeping');
});

