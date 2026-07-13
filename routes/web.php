<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\FacultyController;
use App\Http\Controllers\DeanController;
use App\Http\Controllers\AuthController;
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
Route::get('/signup', [AuthController::class, 'signup'])->middleware('guest')->name('signup');
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
});

// Faculty Routes
Route::prefix('faculty')->middleware('auth')->name('faculty.')->group(function () {
    Route::get('/dashboard', [FacultyController::class, 'dashboard'])->name('dashboard');
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
                        'id'    => $user?->id,
                        'name'  => $displayName,
                        'email' => $user?->email,
                        'roles' => $member->roles->pluck('role')->toArray(),
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
        $taskCounts  = ['front_desk' => 0, 'restaurant_management' => 0, 'room_management' => 0, 'maintenance' => 0];
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

            $recentTasks = Task::where('faculty_id', $facultyId)
                ->latest()
                ->take(3)
                ->get();
        }

        $totalTasks   = array_sum($taskCounts);
        $studentRoles = $groupMembership ? $groupMembership->roles->pluck('role')->toArray() : [];
        $myRoleTasks  = $tasksByRole->filter(fn($tasks, $role) => in_array($role, $studentRoles))->flatten();

        $myCompletedTasks = $facultyId && !empty($studentRoles)
            ? Task::where('faculty_id', $facultyId)
                ->whereIn('role', $studentRoles)
                ->where('status', 'archived')
                ->orderByDesc('updated_at')
                ->get()
            : collect();


        return view('students.dashboard', compact(
            'membersByRole', 'group', 'groupMembers',
            'tasksByRole', 'taskCounts', 'totalTasks',
            'studentRoles', 'myRoleTasks', 'completedTasksCount',
            'pendingTasksCount', 'completionRate', 'recentTasks',
            'myCompletedTasks'
        ));
    })->name('dashboard');
    Route::get('/roommanagement', function () {
        $authUser = auth()->user();
        $student  = $authUser?->student;
        $groupMembership = $student
            ? StudentGroup::with('student.user', 'roles')->where('student_id', $student->id)->first()
            : null;
        $facultyId = $groupMembership?->faculty_id;
        $groupName = $groupMembership?->group_name;
        $studentRoles = $groupMembership ? $groupMembership->roles->pluck('role')->toArray() : [];

        $groupMembers = collect();
        $group = null;

        if ($groupMembership) {
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
                        'id'    => $user?->id,
                        'name'  => $displayName,
                        'email' => $user?->email,
                        'roles' => $member->roles->pluck('role')->toArray(),
                    ];
                });

            $group = (object) [
                'name'    => $groupName,
                'members' => $groupMembers,
            ];
        }

        $tasks = $facultyId
            ? Task::where('faculty_id', $facultyId)->where('role', 'room_management')->where('status', 'active')->get()
            : collect();

        $tasksByRole = $facultyId
            ? Task::where('faculty_id', $facultyId)->where('status', 'active')->get()->groupBy('role')
            : collect();

        $selectedTemplate = null;
        if ($groupName && $facultyId) {
            $settings = GroupSettings::where('group_name', $groupName)
                ->where('faculty_id', $facultyId)
                ->first();
            $selectedTemplate = $settings?->selected_template;
        }

        return view('students.roommanagement', compact('tasks', 'groupMembers', 'group', 'studentRoles', 'tasksByRole', 'groupName', 'facultyId', 'selectedTemplate'));
    })->name('roommanagement');
    Route::get('/frontdesk', function () {
        $authUser = auth()->user();
        $student  = $authUser?->student;
        
        $groupMembership = $student
            ? StudentGroup::with('student.user', 'roles')
                ->where('student_id', $student->id)
                ->first()
            : null;
            
        $groupMembers = collect();
        $group = null;
        $facultyId = null;
        $studentRoles = [];

        if ($groupMembership) {
            $facultyId = $groupMembership->faculty_id;
            $groupName = $groupMembership->group_name;
            $studentRoles = $groupMembership->roles->pluck('role')->toArray();
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
                        'id'    => $user?->id,
                        'name'  => $displayName,
                        'email' => $user?->email,
                        'roles' => $member->roles->pluck('role')->toArray(),
                    ];
                });

            $group = (object) [
                'name'    => $groupName,
                'members' => $groupMembers,
            ];
        }

        $tasks = $facultyId
            ? Task::where('faculty_id', $facultyId)->where('role', 'front_desk')->where('status', 'active')->get()
            : collect();

        // All tasks grouped by role so sidebar can show each member's tasks
        $tasksByRole = $facultyId
            ? Task::where('faculty_id', $facultyId)->where('status', 'active')->get()->groupBy('role')
            : collect();

        // Get selected template for this group
        $selectedTemplate = null;
        if ($groupName && $facultyId) {
            $settings = GroupSettings::where('group_name', $groupName)
                ->where('faculty_id', $facultyId)
                ->first();
            $selectedTemplate = $settings?->selected_template;
        }

        return view('students.frontdesk.frontdesk', compact('tasks', 'groupMembers', 'group', 'studentRoles', 'tasksByRole', 'groupName', 'facultyId', 'selectedTemplate'));
    })->name('frontdesk');

    Route::get('/frontdesk/template/1', function (Request $request) {
        if ($request->query('save') === '1') {
            $authUser = auth()->user();
            $student = $authUser?->student;
            if ($student) {
                $groupMembership = StudentGroup::where('student_id', $student->id)->first();
                if ($groupMembership) {
                    GroupSettings::updateOrCreate(
                        ['group_name' => $groupMembership->group_name, 'faculty_id' => $groupMembership->faculty_id],
                        ['selected_template' => '1']
                    );
                }
            }
        }
        return view('students.template.1defaulttemplate');
    })->name('frontdesk.template.1');

    Route::get('/frontdesk/template/2', function (Request $request) {
        if ($request->query('save') === '1') {
            $authUser = auth()->user();
            $student = $authUser?->student;
            if ($student) {
                $groupMembership = StudentGroup::where('student_id', $student->id)->first();
                if ($groupMembership) {
                    GroupSettings::updateOrCreate(
                        ['group_name' => $groupMembership->group_name, 'faculty_id' => $groupMembership->faculty_id],
                        ['selected_template' => '2']
                    );
                }
            }
        }
        return view('students.template.2defaulttemplate');
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

        $groupMembership = StudentGroup::where('student_id', $student->id)->first();
        if (!$groupMembership) {
            \Log::warning('Template select: group not found', ['student_id' => $student->id]);
            return response()->json(['error' => 'Group not found'], 404);
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

        return response()->json(['success' => true]);
    })->name('frontdesk.template.select');


    Route::get('/restaurant', function () {
        $authUser = auth()->user();
        $student  = $authUser?->student;
        $groupMembership = $student
            ? StudentGroup::with('student.user', 'roles')->where('student_id', $student->id)->first()
            : null;
        $facultyId = $groupMembership?->faculty_id;
        $groupName = $groupMembership?->group_name;
        $studentRoles = $groupMembership ? $groupMembership->roles->pluck('role')->toArray() : [];

        $groupMembers = collect();
        $group = null;

        if ($groupMembership) {
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
                        'id'    => $user?->id,
                        'name'  => $displayName,
                        'email' => $user?->email,
                        'roles' => $member->roles->pluck('role')->toArray(),
                    ];
                });

            $group = (object) [
                'name'    => $groupName,
                'members' => $groupMembers,
            ];
        }

        $tasks = $facultyId
            ? Task::where('faculty_id', $facultyId)->where('role', 'restaurant_management')->where('status', 'active')->get()
            : collect();

        $tasksByRole = $facultyId
            ? Task::where('faculty_id', $facultyId)->where('status', 'active')->get()->groupBy('role')
            : collect();

        $selectedTemplate = null;
        if ($groupName && $facultyId) {
            $settings = GroupSettings::where('group_name', $groupName)
                ->where('faculty_id', $facultyId)
                ->first();
            $selectedTemplate = $settings?->selected_template;
        }

        return view('students.restaurant', compact('tasks', 'groupMembers', 'group', 'studentRoles', 'tasksByRole', 'groupName', 'facultyId', 'selectedTemplate'));
    })->name('restaurant');
    Route::get('/maintenance', function () {
        $authUser = auth()->user();
        $student  = $authUser?->student;
        $groupMembership = $student
            ? StudentGroup::with('student.user', 'roles')->where('student_id', $student->id)->first()
            : null;
        $facultyId = $groupMembership?->faculty_id;
        $groupName = $groupMembership?->group_name;
        $studentRoles = $groupMembership ? $groupMembership->roles->pluck('role')->toArray() : [];

        $groupMembers = collect();
        $group = null;

        if ($groupMembership) {
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
                        'id'    => $user?->id,
                        'name'  => $displayName,
                        'email' => $user?->email,
                        'roles' => $member->roles->pluck('role')->toArray(),
                    ];
                });

            $group = (object) [
                'name'    => $groupName,
                'members' => $groupMembers,
            ];
        }

        $tasks = $facultyId
            ? Task::where('faculty_id', $facultyId)->where('role', 'maintenance')->where('status', 'active')->get()
            : collect();

        $tasksByRole = $facultyId
            ? Task::where('faculty_id', $facultyId)->where('status', 'active')->get()->groupBy('role')
            : collect();

        $selectedTemplate = null;
        if ($groupName && $facultyId) {
            $settings = GroupSettings::where('group_name', $groupName)
                ->where('faculty_id', $facultyId)
                ->first();
            $selectedTemplate = $settings?->selected_template;
        }

        return view('students.maintenance', compact('tasks', 'groupMembers', 'group', 'studentRoles', 'tasksByRole', 'groupName', 'facultyId', 'selectedTemplate'));
    })->name('maintenance');
    Route::get('/housekeeping', function () {
        $authUser = auth()->user();
        $student  = $authUser?->student;
        $groupMembership = $student
            ? StudentGroup::with('student.user', 'roles')->where('student_id', $student->id)->first()
            : null;
        $facultyId = $groupMembership?->faculty_id;
        $groupName = $groupMembership?->group_name;
        $studentRoles = $groupMembership ? $groupMembership->roles->pluck('role')->toArray() : [];

        $groupMembers = collect();
        $group = null;

        if ($groupMembership) {
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
                        'id'    => $user?->id,
                        'name'  => $displayName,
                        'email' => $user?->email,
                        'roles' => $member->roles->pluck('role')->toArray(),
                    ];
                });

            $group = (object) [
                'name'    => $groupName,
                'members' => $groupMembers,
            ];
        }

        $tasks = $facultyId
            ? Task::where('faculty_id', $facultyId)->where('role', 'housekeeping')->where('status', 'active')->get()
            : collect();

        $tasksByRole = $facultyId
            ? Task::where('faculty_id', $facultyId)->where('status', 'active')->get()->groupBy('role')
            : collect();

        $selectedTemplate = null;
        if ($groupName && $facultyId) {
            $settings = GroupSettings::where('group_name', $groupName)
                ->where('faculty_id', $facultyId)
                ->first();
            $selectedTemplate = $settings?->selected_template;
        }

        return view('students.housekeeping', compact('tasks', 'groupMembers', 'group', 'studentRoles', 'tasksByRole', 'groupName', 'facultyId', 'selectedTemplate'));
    })->name('housekeeping');
});

Route::get('/auto-login-student', function() {
    $facultyUser = \App\Models\User::firstOrCreate(
        ['email' => 'faculty@hms.edu'],
        [
            'name' => 'Faculty Teacher',
            'password' => \Illuminate\Support\Facades\Hash::make('Faculty@1234'),
            'role' => 'faculty',
            'status' => 'active',
            'email_verified_at' => now(),
        ]
    );
    $faculty = \App\Models\Faculty::firstOrCreate(
        ['user_id' => $facultyUser->id],
        ['status' => 'active']
    );

    $roles = ['front_desk', 'restaurant_management', 'room_management', 'maintenance'];
    $students = [];
    foreach ($roles as $idx => $role) {
        $user = \App\Models\User::firstOrCreate(
            ['email' => "student_$role@hms.edu"],
            [
                'name' => "Student " . ucfirst(str_replace('_', ' ', $role)),
                'password' => \Illuminate\Support\Facades\Hash::make('Student@1234'),
                'role' => 'student',
                'status' => 'active',
                'email_verified_at' => now(),
            ]
        );
        $student = \App\Models\Student::firstOrCreate(
            ['user_id' => $user->id],
            ['student_id' => 'STU00' . ($idx + 1)]
        );
        
        \App\Models\StudentGroup::firstOrCreate(
            [
                'group_name' => 'Group Alpha',
                'student_id' => $student->id,
            ],
            [
                'faculty_id' => $faculty->id,
                'role' => $role,
            ]
        );
        
        $students[$role] = $user;
    }

    foreach ($roles as $role) {
        $studentUser = $students[$role];
        $student = \App\Models\Student::where('user_id', $studentUser->id)->first();
        \App\Models\Task::firstOrCreate(
            [
                'title' => 'Sample Task for ' . ucfirst(str_replace('_', ' ', $role)),
                'assigned_to' => $studentUser->id,
            ],
            [
                'faculty_id' => $faculty->id,
                'student_id' => $student?->id,
                'role' => $role,
                'description' => 'This is a description of the sample task for ' . $role,
                'due_date' => now()->addDays(2),
                'priority' => 'high',
                'status' => 'active',
            ]
        );
    }

    auth()->login($students['front_desk']);
    return redirect()->route('students.dashboard');
});

Route::get('/test-faculty-groups', function() {
    $facultyId = auth()->user()?->faculty?->id;
    
    if (!$facultyId) {
        return 'No faculty ID found. Current user: ' . auth()->user()->name . ' (role: ' . auth()->user()->role . ')';
    }
    
    $groups = \App\Models\StudentGroup::with('student.user')
        ->where('faculty_id', $facultyId)
        ->get()
        ->groupBy('group_name');
    
    $output = '<h1>Faculty ID: ' . $facultyId . '</h1>';
    $output .= '<h2>Total Groups: ' . count($groups) . '</h2>';
    
    foreach ($groups as $groupName => $members) {
        $output .= '<div style="border:1px solid #ccc; padding:10px; margin:10px;">';
        $output .= '<h3>' . $groupName . '</h3>';
        $output .= '<ul>';
        foreach ($members as $member) {
            $name = $member->student?->user?->name ?? 'Unknown';
            $output .= '<li>' . $name . ' (' . $member->role . ')</li>';
        }
        $output .= '</ul>';
        $output .= '</div>';
    }
    
    return $output;
});

