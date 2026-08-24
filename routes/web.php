<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ActivityLogController;
use App\Http\Controllers\FacultyController;
use App\Http\Controllers\DeanController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\HotelConceptController;
use App\Http\Controllers\HotelTemplateController;
use App\Http\Controllers\NotificationController;
use App\Models\ActivityLog;
use App\Models\HotelBooking;
use App\Models\HotelComplaint;
use App\Models\HotelDineInTable;
use App\Models\HotelFoodOrder;
use App\Models\HotelMenuItem;
use App\Models\HotelRoom;
use App\Models\StudentGroup;
use App\Models\Task;
use App\Models\GroupSettings;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;


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

// Notification bell — same feed endpoints for dean, faculty and students.
// Every query is scoped to auth()->id() inside the controller.
Route::prefix('notifications')->middleware('auth')->name('notifications.')->group(function () {
    Route::get('/', [NotificationController::class, 'index'])->name('index');
    Route::get('/unread-count', [NotificationController::class, 'unreadCount'])->name('unread-count');
    Route::post('/{id}/read', [NotificationController::class, 'markRead'])->name('read');
    Route::post('/read-all', [NotificationController::class, 'markAllRead'])->name('read-all');
});

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

    // Front Desk's hotel concept for any team, with its edit history.
    Route::get('/teams/hotel-concept', [HotelConceptController::class, 'deanHistory'])->name('teams.hotel-concept');
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

    // Front Desk's hotel concept for one of this faculty's own teams, with its edit history.
    Route::get('/teams/{groupName}/hotel-concept', [HotelConceptController::class, 'facultyHistory'])
        ->name('teams.hotel-concept');
});

// Student Routes
Route::prefix('students')->middleware('auth')->name('students.')->group(function () {
    Route::get('/dashboard', function () {
        $authUser = auth()->user();
        $student  = $authUser?->student;

        $groupMembership = $student
            ? StudentGroup::with('student.user')
                ->where('student_id', $student->user_information_id)
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
                ->where('faculty_id', $facultyId)
                ->get()
                ->map(function ($member) {
                    $user = $member->student?->user;
                    $displayName = trim(implode(' ', array_filter([
                        $user?->last_name  ?? null,
                        $user?->first_name ?? null,
                        $user?->middle_name ?? null,
                    ])));
                    $displayName = $displayName !== '' ? $displayName : ($user?->name ?? 'Student');

                    // "id" is the member shape the dashboard Blade reads.
                    return (object) [
                        'id'         => $user?->user_id,
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

        /*
         * A task row carries no team column of its own — it is tied to one student,
         * and the student's membership is what puts it on a team. Faculty assign the
         * same role task to every team they own, so filtering by faculty + role alone
         * showed one team every other team's rows, including the feedback and approval
         * faculty left on them. Resolve the team once here and scope every task query
         * below to it, so a team only ever reads its own submissions and their verdict.
         */
        $teamStudentIds = $groupMembership
            ? StudentGroup::where('group_name', $groupMembership->group_name)
                ->where('faculty_id', $groupMembership->faculty_id)
                ->pluck('student_id')
                ->filter()
                ->map(fn ($id) => (int) $id)
                ->unique()
                ->values()
                ->all()
            : [];

        // Rows with no student belong to no team: faculty creates one when nobody in
        // the faculty holds that role yet. They carry no submission and so no feedback,
        // so they stay visible to the role rather than vanishing from every dashboard.
        $scopeToTeam = function ($query) use ($teamStudentIds) {
            $query->where(function ($q) use ($teamStudentIds) {
                $q->whereNull('student_id');
                if ($teamStudentIds !== []) {
                    $q->orWhereIn('student_id', $teamStudentIds);
                }
            });
        };

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
                ->where($scopeToTeam)
                ->where('status', 'active')
                // The hotel concept is Task 1 and gates everything else, so it heads
                // the list regardless of the due date it does not have.
                ->conceptFirst()
                ->orderBy('due_date')
                ->orderByPriority()
                ->get();

            $tasksByRole = $allTasks->groupBy('role');

            foreach (array_keys($taskCounts) as $role) {
                $taskCounts[$role] = $tasksByRole->get($role, collect())->count();
            }

            $completedTasksCount = Task::where('faculty_id', $facultyId)
                ->where($scopeToTeam)
                ->where('status', 'archived')
                ->count();

            $pendingTasksCount = Task::where('faculty_id', $facultyId)
                ->where($scopeToTeam)
                ->where('status', 'active')
                ->count();

            $totalAllTasks = $completedTasksCount + $pendingTasksCount;
            $completionRate = $totalAllTasks > 0 ? round(($completedTasksCount / $totalAllTasks) * 100) : 0;

            $recentTasks = Task::with(['student.user', 'assignedTo'])
                ->where('faculty_id', $facultyId)
                ->where($scopeToTeam)
                ->latest('updated_at')
                ->take(12)
                ->get();
        }

        $totalTasks   = array_sum($taskCounts);
        $studentRoles = $groupMembership ? $groupMembership->roles->pluck('role')->toArray() : [];
        $myRoleTasks  = $tasksByRole->filter(fn($tasks, $role) => in_array($role, $studentRoles))->flatten();

        $myCompletedTasks = $facultyId && $student
            ? Task::where('faculty_id', $facultyId)
                ->where($scopeToTeam)
                ->where('status', 'archived')
                ->where(function ($q) use ($student, $authUser, $studentRoles) {
                    $q->where('student_id', $student->user_information_id)
                        ->orWhere('assigned_to', $authUser->user_id);
                    if (!empty($studentRoles)) {
                        $q->orWhereIn('role', $studentRoles);
                    }
                })
                ->orderByDesc('updated_at')
                ->get()
                ->unique('task_id')
                ->values()
            : collect();

        // Centralized activity log — a student only ever sees their own rows.
        $myActivityLogs = $authUser
            ? \App\Support\ActivityLogAccess::logsFor($authUser, 100)
            : collect();

        $selfActivityLogs = $facultyId && $student
            ? Task::where('faculty_id', $facultyId)
                ->where($scopeToTeam)
                ->where(function ($q) use ($student, $authUser, $studentRoles) {
                    // Only this user's own history
                    $q->where('student_id', $student->user_information_id)
                        ->orWhere('assigned_to', $authUser->user_id);

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
                ->unique('task_id')
                ->values()
            : collect();

        // Used for teammate Activity modal + reports (not shown in Activity Logs nav)
        $teamActivityLogs = $facultyId
            ? Task::with(['student.user', 'assignedTo'])
                ->where('faculty_id', $facultyId)
                ->where($scopeToTeam)
                ->orderByDesc('updated_at')
                ->take(50)
                ->get()
            : collect();

        $studentDisplayName = trim(implode(' ', array_filter([
            $authUser?->first_name,
            $authUser?->last_name,
        ]))) ?: ($authUser?->name ?? 'Student');

        $studentClass = $student?->facultyClass;

        // The team's first task: two hotel concepts. Read by the whole team in My
        // Team; who may write each depends on what state that one is in, so
        // HotelConceptDesk decides — Front Desk proposes each first version, then
        // everyone improves them, and each locks while faculty holds it. The
        // controller enforces the same rules on write.
        //
        // Handed to the view as one payload rather than loose variables per slot:
        // the same shape the save and submit endpoints return, so the Blade and the
        // JS that repaints it read identical keys.
        $conceptPayload = HotelConceptController::payload(
            HotelConceptController::forTeam(
                $groupMembership?->group_name,
                $facultyId ? (int) $facultyId : null
            ),
            $studentRoles
        );

        return view('students.dashboard', compact(
            'membersByRole', 'group', 'groupMembers',
            'tasksByRole', 'taskCounts', 'totalTasks',
            'studentRoles', 'myRoleTasks', 'completedTasksCount',
            'pendingTasksCount', 'completionRate', 'recentTasks',
            'myCompletedTasks', 'selfActivityLogs', 'teamActivityLogs',
            'myActivityLogs', 'conceptPayload',
            'studentDisplayName', 'studentClass', 'student'
        ));
    })->name('dashboard');

    // The team's first task: the hotel concept. Every member reads it, and the
    // controller decides who may save or submit at any given moment.
    Route::post('/hotel-concept', [HotelConceptController::class, 'store'])->name('hotel-concept.store');
    Route::post('/hotel-concept/submit', [HotelConceptController::class, 'submit'])->name('hotel-concept.submit');
    Route::get('/hotel-concept/history', [HotelConceptController::class, 'history'])->name('hotel-concept.history');

    // The student's own account details. Read on the dashboard's Profile section,
    // so there is no GET here — only the write.
    Route::put('/profile', [\App\Http\Controllers\StudentController::class, 'updateProfile'])->name('profile.update');

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

        $groupMembership = StudentGroup::with('roles')->where('student_id', $student->user_information_id)->first();
        if (!$groupMembership || (int) $task->faculty_id !== (int) $groupMembership->faculty_id) {
            abort(403);
        }

        $studentRoles = $groupMembership->roles->pluck('role')->toArray();
        if (!in_array($task->role, $studentRoles, true)) {
            return back()->withErrors(['task' => 'This task is not assigned to your role.']);
        }

        // The hotel concept closes by submitting the concept itself, not by ticking
        // the row — otherwise Front Desk could mark it done with nothing written.
        if ($task->is_hotel_concept) {
            return back()->withErrors([
                'task' => 'Submit the hotel concept from My Team instead — that is what closes this task.',
            ]);
        }

        // Tasks fan out one row per member, so a role match alone is not enough —
        // without this a student could submit a teammate's row. Unclaimed rows
        // (no member held the role at assign time) stay open to the first submitter.
        $claimedByOther = ($task->assigned_to && (int) $task->assigned_to !== (int) $authUser->user_id)
            || ($task->student_id && (int) $task->student_id !== (int) $student->user_information_id);
        if ($claimedByOther) {
            return back()->withErrors(['task' => 'This task belongs to a teammate.']);
        }

        if ($task->status !== 'active') {
            return back()->withErrors(['task' => 'This task has already been submitted.']);
        }

        // Freeze the work as handed in, so the faculty review can show what changed
        // between this submission and the last one. The previous anchor slides down
        // to become the "Before" side.
        $roleTemplate = \App\Support\HotelTemplateBuilder::ensureTemplate($groupMembership, $task->role);
        $snapshotId = \App\Support\HotelTemplateBuilder::snapshotForReview(
            $roleTemplate,
            $authUser,
            'Submitted: ' . $task->title
        );

        // "Before" resolves in order: the anchor already on the task (set at
        // assignment, or at the last send-back), then the last submission (a
        // plain resubmit compares against what was last handed in), then the
        // newest snapshot that exists for this template — covers tasks created
        // before baseline snapshots existed. Null only if none of those exist.
        $previousVersionId = $task->previous_version_id
            ?: $task->submitted_version_id
            ?: \App\Models\TeamRoleTemplateVersion::where('team_role_template_id', $roleTemplate->team_role_template_id)
                // Exclude the snapshot just taken above — it is this submission's
                // own "After", not something to compare it against.
                ->when($snapshotId, fn ($q) => $q->where('team_role_template_version_id', '!=', $snapshotId))
                ->orderByDesc('team_role_template_version_id')
                ->value('team_role_template_version_id');

        $task->update([
            'status' => 'archived',
            'student_id' => $student->user_information_id,
            'assigned_to' => $authUser->user_id,
            'previous_version_id' => $previousVersionId,
            'submitted_version_id' => $snapshotId ?: $task->submitted_version_id,
        ]);

        ActivityLog::record(
            $authUser,
            ActivityLog::TASK_SUBMITTED,
            'Submitted task "' . $task->title . '" for the ' . $task->role . ' role.'
        );

        \App\Support\Notifier::taskSubmitted($authUser, $task, $authUser->name);

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
        $membership = \App\Support\StudentGroupSync::membershipForStudent($student?->user_information_id);

        \App\Support\StudentGroupSync::heartbeat($authUser, $membership);

        if (!$membership) {
            return response()->json([
                'online' => [(string) $authUser->user_id => true],
                'members' => [],
            ]);
        }

        $members = StudentGroup::with('student.user', 'roles')
            ->where('group_name', $membership->group_name)
            ->when($membership->faculty_id, fn ($q) => $q->where('faculty_id', $membership->faculty_id))
            ->get();

        $userIds = $members->map(fn ($m) => $m->student?->user?->user_id)->filter()->values()->all();
        $online = \App\Support\StudentGroupSync::onlineMap($userIds, $membership);
        $online[(string) $authUser->user_id] = true;

        $payload = $members->map(function ($m) use ($online) {
            $user = $m->student?->user;
            $uid = (int) ($user?->user_id ?? 0);
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
        $membership = \App\Support\StudentGroupSync::membershipForStudent($student?->user_information_id);
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
            $groupMembership = StudentGroup::with('roles')->where('student_id', $student->user_information_id)->first();
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
            $groupMembership = StudentGroup::with('roles')->where('student_id', $student->user_information_id)->first();
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

        $groupMembership = StudentGroup::with('roles')->where('student_id', $student->user_information_id)->first();
        if (!$groupMembership) {
            \Log::warning('Template select: group not found', ['student_id' => $student->user_information_id]);
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

        $groupMembership = StudentGroup::with('roles')->where('student_id', $student->user_information_id)->first();
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

        $groupMembership = StudentGroup::with('roles')->where('student_id', $student->user_information_id)->first();
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

    /*
    |--------------------------------------------------------------------------
    | Hotel Rooms (shared between Room Management & Front Desk)
    |--------------------------------------------------------------------------
    |
    | These routes own the room inventory only. Who is staying in a room is a
    | hotel_bookings row — see the /hotel/bookings routes below. The room payload
    | still carries a `reservation` object because the room grid, Verify Guest and
    | Guest Details all read it, but it is projected from the open booking now, not
    | stored on the room.
    */
    Route::get('/hotel/rooms', function (Request $request) {
        $membership = \App\Support\HotelBookingDesk::membership();
        if (!$membership) {
            return response()->json(['rooms' => []]);
        }

        // A team starts with ten rooms per category. No-ops once it has any rooms at
        // all, so a team that already built its own inventory is never touched.
        \App\Support\HotelRoomDefaults::ensureFor($membership);

        $rooms = HotelRoom::with(['activeBooking.guest', 'activeBooking.payments', 'activeBooking.foodOrders', 'activeBooking.charges', 'activeBooking.addons', 'openBookings'])
            ->where('group_name', $membership->group_name)
            ->where('faculty_id', $membership->faculty_id)
            ->orderBy('hotel_room_id')
            ->get()
            ->map(fn (HotelRoom $room) => $room->toTemplateArray());
        return response()->json(['rooms' => $rooms]);
    })->name('hotel.rooms.index');

    /**
     * Guests Front Desk has registered that Room Management has not checked in yet.
     * Count only — this is polled every few seconds for the sidebar badge, so it stays
     * a single COUNT(*) rather than reusing the full /hotel/rooms payload.
     */
    Route::get('/hotel/guests/pending-count', function () {
        $membership = \App\Support\HotelBookingDesk::membership();
        if (!$membership) {
            return response()->json(['count' => 0]);
        }

        return response()->json([
            'count' => \App\Support\HotelBookingDesk::scopedQuery($membership)
                ->awaitingCheckIn()
                ->count(),
        ]);
    })->name('hotel.guests.pending-count');

    /**
     * Counts behind the Staff Tools nav badges, for whichever role the sidebar was
     * rendered for. The role comes from the query string rather than the session
     * because a student may hold more than one, and the badge has to match the nav
     * that is actually on screen.
     */
    Route::get('/hotel/nav-badges', function (Request $request) {
        $membership = \App\Support\HotelBookingDesk::membership();

        return response()->json([
            'badges' => \App\Support\HotelNavBadges::forRole($membership, $request->query('role')),
        ]);
    })->name('hotel.nav-badges');

    /**
     * Edits a room, and/or moves its status. Releasing a room (Available / Cleaning /
     * Maintenance) also closes whatever booking was holding it, which is what keeps a
     * departed guest from lingering in room service — so status still goes through
     * applyRoomStatus() rather than being written with the rest of the fields.
     */
    Route::patch('/hotel/rooms/{id}', function (Request $request, $id) {
        $membership = \App\Support\HotelBookingDesk::membership();
        if (!$membership) {
            return response()->json(['error' => 'Group not found'], 404);
        }
        $room = HotelRoom::where('hotel_room_id', $id)
            ->where('group_name', $membership->group_name)
            ->where('faculty_id', $membership->faculty_id)
            ->firstOrFail();

        $data = $request->validate([
            'status'      => ['sometimes', 'string', Rule::in(HotelRoom::STATUSES)],
            'name'        => 'sometimes|required|string|max:255',
            'category'    => 'sometimes|required|string|max:100',
            'price'       => 'sometimes|required|integer|min:1',
            'description' => 'sometimes|nullable|string|max:5000',
            'image'       => 'sometimes|nullable|string|max:900000',
        ], [
            'image.max' => 'That image is too large. Please choose a smaller one.',
        ]);

        foreach (['name', 'category', 'price', 'description'] as $field) {
            if (array_key_exists($field, $data)) {
                $room->{$field} = $field === 'price' ? (int) $data[$field] : $data[$field];
            }
        }

        // persist() passes an existing storage path straight back, so the edit form can
        // return the photo it was given without re-uploading it.
        if (array_key_exists('image', $data)) {
            $room->image = \App\Support\HotelImageStore::persist(
                $data['image'],
                $membership->faculty_id,
                $membership->group_name
            );
        }

        if ($room->isDirty()) {
            $room->save();
        }

        if (array_key_exists('status', $data)) {
            \App\Support\HotelBookingDesk::applyRoomStatus($room, $data['status']);
        }

        return response()->json([
            'room' => $room->fresh()->toTemplateArray(),
        ]);
    })->name('hotel.rooms.update');

    Route::post('/hotel/rooms', function (Request $request) {
        $authUser = auth()->user();
        $student  = $authUser?->student;
        $membership = \App\Support\StudentGroupSync::membershipForStudent($student?->user_information_id);
        if (!$membership) {
            return response()->json(['error' => 'Group not found'], 404);
        }
        // The front-end still posts the photo as a base64 data-URL; it is decoded to a
        // file below so only the path reaches the database.
        $data = $request->validate([
            'category'    => 'required|string|max:100',
            'price'       => 'required|integer|min:1',
            'description' => 'nullable|string|max:5000',
            'image'       => 'nullable|string|max:900000',
        ], [
            'image.max' => 'That image is too large. Please choose a smaller one.',
        ]);

        // The name is the category's next free number, never whatever the browser sent
        // — that is what keeps Classic 111 following Classic 110 without anyone typing.
        $category = \App\Support\HotelRoomDefaults::normalizeCategory($data['category']);

        $room = HotelRoom::create([
            'group_name'  => $membership->group_name,
            'faculty_id'  => $membership->faculty_id,
            'group_id'    => $membership->group_id,
            'name'        => \App\Support\HotelRoomDefaults::nextNameFor($membership, $category),
            'category'    => $category,
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
            'room' => $room->toTemplateArray(),
        ], 201);
    })->name('hotel.rooms.store');

    /*
    |--------------------------------------------------------------------------
    | Hotel bookings (guest stays — the tables that replaced the reservation blob)
    |--------------------------------------------------------------------------
    |
    | A booking joins one room to one guest for one stay, with its payments hanging
    | off it. Room status moves as a side effect of the lifecycle, all of it inside
    | App\Support\HotelBookingDesk so the two can never disagree.
    */

    Route::get('/hotel/bookings', function (Request $request) {
        $membership = \App\Support\HotelBookingDesk::membership();
        if (!$membership) {
            return response()->json(['bookings' => []]);
        }

        $query = \App\Support\HotelBookingDesk::scopedQuery($membership);

        // ?open=1 is the desk's working set: the stays that still hold a room.
        if ($request->boolean('open')) {
            $query->open();
        }
        if ($request->filled('room_id')) {
            $query->where('hotel_room_id', (int) $request->input('room_id'));
        }

        return response()->json([
            'bookings' => $query->get()->map(fn (HotelBooking $booking) => $booking->toReservationArray() + [
                'roomId'   => 'db-' . $booking->hotel_room_id,
                'roomName' => $booking->room?->name,
            ]),
        ]);
    })->name('hotel.bookings.index');

    Route::post('/hotel/bookings', function (Request $request) {
        $membership = \App\Support\HotelBookingDesk::membership();
        if (!$membership) {
            return response()->json(['message' => 'Join a hotel team first.'], 404);
        }

        $data = $request->validate([
            'room_id'            => 'required|integer',
            'guest.full_name'    => 'required|string|max:255',
            'guest.contact_no'   => 'nullable|string|max:100',
            'guest.email'        => 'nullable|string|email|max:255',
            'guest.id_number'    => 'nullable|string|max:100',
            'check_in'           => 'required|date',
            'check_in_time'      => 'required|string|max:10',
            'check_out'          => 'required|date|after:check_in',
            'notes'              => 'nullable|string|max:2000',
            'payment'            => 'nullable|array',
            'payment.type'       => 'nullable|string|max:20',
            'payment.amount_paid'=> 'nullable|numeric|min:0',
            'payment.method'     => 'nullable|string|max:50',
            'payment.reference'  => 'nullable|string|max:255',
            'payment.payer_name' => 'nullable|string|max:255',
            'payment.notes'      => 'nullable|string|max:2000',
            // Housekeeping add-ons Front Desk ticked while registering the guest.
            'addons'             => 'nullable|array',
            'addons.*.addon_id'  => 'required|integer',
            'addons.*.qty'       => 'required|integer|min:1|max:99',
        ]);

        $room = HotelRoom::where('hotel_room_id', $data['room_id'])
            ->where('group_name', $membership->group_name)
            ->where('faculty_id', $membership->faculty_id)
            ->first();
        if (!$room) {
            return response()->json(['message' => 'That room is not on your team\'s floor.'], 404);
        }

        // A room may carry several open stays now — one in-house, the rest booked for
        // later — so the guard is against overlapping dates, not against any open
        // booking at all. Without this two people polling the same grid could both
        // book the same week.
        $overlaps = $room->openBookings()
            ->where('check_in', '<', $data['check_out'])
            ->where('check_out', '>', $data['check_in'])
            ->exists();
        if ($overlaps) {
            return response()->json(['message' => 'Those dates are already booked for this room.'], 409);
        }

        try {
            $booking = \App\Support\HotelBookingDesk::reserve(
                $membership,
                $room,
                $data['guest'],
                [
                    'check_in'      => $data['check_in'],
                    'check_in_time' => $data['check_in_time'],
                    'check_out'     => $data['check_out'],
                    'booked_by'     => auth()->user()?->name,
                    'notes'         => $data['notes'] ?? null,
                ],
                $data['payment'] ?? null,
                $data['addons'] ?? []
            );
        } catch (\RuntimeException $e) {
            // An add-on ran out between the picker rendering and this request. The whole
            // reservation rolled back, so there is no half-booked stay to clean up.
            return response()->json(['message' => $e->getMessage()], 422);
        }

        return response()->json([
            'booking' => $booking->toReservationArray(),
            'room'    => $room->fresh()->toTemplateArray(),
        ], 201);
    })->name('hotel.bookings.store');

    /**
     * Lifecycle moves. `action` is one of arrive / check_in / check_out / cancel;
     * a body without one edits the stay's own details instead.
     */
    Route::patch('/hotel/bookings/{id}', function (Request $request, $id) {
        $membership = \App\Support\HotelBookingDesk::membership();
        if (!$membership) {
            return response()->json(['message' => 'Group not found'], 404);
        }

        $booking = \App\Support\HotelBookingDesk::findBooking($membership, $id);
        if (!$booking) {
            return response()->json(['message' => 'Booking not found.'], 404);
        }

        $data = $request->validate([
            'action'        => ['nullable', 'string', Rule::in(['arrive', 'check_in', 'check_out', 'cancel'])],
            'check_in'      => 'sometimes|date',
            'check_in_time' => 'sometimes|string|max:10',
            'check_out'     => 'sometimes|date',
            'notes'         => 'sometimes|nullable|string|max:2000',
        ]);

        $action = $data['action'] ?? null;

        if ($action !== null && !$booking->isOpen()) {
            return response()->json(['message' => 'That booking is already closed.'], 409);
        }

        // Payment Completed is a real precondition for check-out, not just a browser
        // confirm — a room only reaches Housekeeping once the bill is settled.
        if ($action === 'check_out' && $booking->outstanding() > 0) {
            return response()->json([
                'message' => 'Settle the bill before checking the guest out. Outstanding: PHP '
                    . number_format($booking->outstanding(), 2),
            ], 422);
        }

        // Status only ever moves forward, for every role — a booking already
        // Checked In cannot be walked back to Arrived, whoever asks.
        $actionTarget = [
            'arrive'    => 'Arrived',
            'check_in'  => 'Checked In',
            'check_out' => 'Checked Out',
            'cancel'    => 'Cancelled',
        ][$action] ?? null;
        if ($actionTarget && !HotelBooking::isForwardTransition($booking->status, $actionTarget)) {
            return response()->json([
                'message' => $booking->status . ' cannot go back to ' . $actionTarget . '. Status only moves forward.',
            ], 422);
        }

        switch ($action) {
            case 'arrive':
                \App\Support\HotelBookingDesk::markArrived($booking);
                break;
            case 'check_in':
                \App\Support\HotelBookingDesk::checkIn($booking);
                break;
            case 'check_out':
                \App\Support\HotelBookingDesk::checkOut($booking);
                break;
            case 'cancel':
                \App\Support\HotelBookingDesk::cancel($booking);
                break;
            default:
                $booking->fill(array_intersect_key($data, array_flip([
                    'check_in', 'check_in_time', 'check_out', 'notes',
                ])))->save();
        }

        $booking = \App\Support\HotelBookingDesk::findBooking($membership, $id);

        return response()->json([
            'booking' => $booking->toReservationArray(),
            'room'    => $booking->room?->fresh()->toTemplateArray(),
        ]);
    })->name('hotel.bookings.update');

    /** A later payment against an existing stay — settling a partial, say. */
    Route::post('/hotel/bookings/{id}/payments', function (Request $request, $id) {
        $membership = \App\Support\HotelBookingDesk::membership();
        if (!$membership) {
            return response()->json(['message' => 'Group not found'], 404);
        }

        $booking = \App\Support\HotelBookingDesk::findBooking($membership, $id);
        if (!$booking) {
            return response()->json(['message' => 'Booking not found.'], 404);
        }

        $data = $request->validate([
            'type'        => 'nullable|string|max:20',
            'amount_paid' => 'required|numeric|min:0.01',
            'method'      => 'nullable|string|max:50',
            'reference'   => 'nullable|string|max:255',
            'payer_name'  => 'nullable|string|max:255',
            'notes'       => 'nullable|string|max:2000',
        ]);

        \App\Support\HotelBookingDesk::addPayment($booking, $data);

        $fresh = \App\Support\HotelBookingDesk::findBooking($membership, $id);

        return response()->json([
            'booking' => $fresh->toReservationArray(),
            'bill'    => $fresh->toBillArray(),
        ], 201);
    })->name('hotel.bookings.payments.store');

    /*
    |--------------------------------------------------------------------------
    | Final bill (Front Desk settles it at check-out)
    |--------------------------------------------------------------------------
    |
    | Every charge on a stay, itemised: the room, room service, and whatever Front
    | Desk added by hand. Read-only — settling it is a payment plus the ordinary
    | check_out action on the booking.
    */

    Route::get('/hotel/bookings/{id}/bill', function (Request $request, $id) {
        $membership = \App\Support\HotelBookingDesk::membership();
        if (!$membership) {
            return response()->json(['message' => 'Group not found'], 404);
        }

        $booking = \App\Support\HotelBookingDesk::findBooking($membership, $id);
        if (!$booking) {
            return response()->json(['message' => 'Booking not found.'], 404);
        }

        return response()->json(['bill' => $booking->toBillArray()]);
    })->name('hotel.bookings.bill');

    Route::post('/hotel/bookings/{id}/charges', function (Request $request, $id) {
        $membership = \App\Support\HotelBookingDesk::membership();
        if (!$membership) {
            return response()->json(['message' => 'Group not found'], 404);
        }

        $booking = \App\Support\HotelBookingDesk::findBooking($membership, $id);
        if (!$booking) {
            return response()->json(['message' => 'Booking not found.'], 404);
        }
        // A closed stay's bill is history; adding to it would change what the guest
        // was already told they owed.
        if (!$booking->isOpen()) {
            return response()->json(['message' => 'That stay is already closed.'], 409);
        }

        $data = $request->validate([
            'description' => 'required|string|max:255',
            'amount'      => 'required|numeric|min:0.01|max:9999999',
        ]);

        \App\Models\HotelBookingCharge::create([
            'group_name'       => $membership->group_name,
            'faculty_id'       => $membership->faculty_id,
            'group_id'         => $membership->group_id,
            'hotel_booking_id' => $booking->hotel_booking_id,
            'description'      => trim($data['description']),
            'amount'           => round((float) $data['amount'], 2),
            'added_by'         => auth()->user()?->name,
        ]);

        return response()->json([
            'bill' => \App\Support\HotelBookingDesk::findBooking($membership, $id)->toBillArray(),
        ], 201);
    })->name('hotel.bookings.charges.store');

    Route::delete('/hotel/bookings/{id}/charges/{chargeId}', function (Request $request, $id, $chargeId) {
        $membership = \App\Support\HotelBookingDesk::membership();
        if (!$membership) {
            return response()->json(['message' => 'Group not found'], 404);
        }

        $booking = \App\Support\HotelBookingDesk::findBooking($membership, $id);
        if (!$booking) {
            return response()->json(['message' => 'Booking not found.'], 404);
        }
        if (!$booking->isOpen()) {
            return response()->json(['message' => 'That stay is already closed.'], 409);
        }

        \App\Models\HotelBookingCharge::where('hotel_booking_charge_id', $chargeId)
            ->where('hotel_booking_id', $booking->hotel_booking_id)
            ->delete();

        return response()->json([
            'bill' => \App\Support\HotelBookingDesk::findBooking($membership, $id)->toBillArray(),
        ]);
    })->name('hotel.bookings.charges.destroy');

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

        $item = HotelMenuItem::where('hotel_menu_item_id', $id)
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

        HotelMenuItem::where('hotel_menu_item_id', $id)
            ->where('group_name', $membership->group_name)
            ->where('faculty_id', $membership->faculty_id)
            ->firstOrFail()
            ->delete();

        return response()->json(['deleted' => true]);
    })->name('hotel.menus.destroy');

    /*
    |--------------------------------------------------------------------------
    | Room add-ons
    |--------------------------------------------------------------------------
    |
    | Housekeeping owns this catalogue; Front Desk reads it to attach an add-on to a
    | stay while registering a guest. There is deliberately no delete route — the
    | Housekeeping table offers Update only, and deleting a row that stays are billed
    | against would strand those lines.
    |
    | "available" is never stored. It is quantity minus whatever is out on open stays,
    | so a guest checking out returns the folding bed with no reversal step.
    */
    Route::get('/hotel/addons', function () {
        $membership = \App\Support\HotelAddonAccess::membership();
        if (!$membership) {
            return response()->json(['items' => [], 'can_manage' => false]);
        }

        // A team starts with a folding bed and an extra towel. No-ops once it has any
        // add-ons at all, so a team that built its own list is never touched.
        \App\Support\HotelAddonAccess::seedDefaults($membership);

        $reserved = \App\Support\HotelAddonDesk::reservedFor($membership);

        $items = \App\Models\HotelAddon::where('group_name', $membership->group_name)
            ->where('faculty_id', $membership->faculty_id)
            ->orderBy('hotel_addon_id')
            ->get()
            ->map(fn (\App\Models\HotelAddon $addon) => $addon->toTemplateArray(
                $reserved[$addon->hotel_addon_id] ?? 0
            ));

        return response()->json([
            'items'      => $items,
            'can_manage' => \App\Support\HotelAddonAccess::canManage($membership),
        ]);
    })->name('hotel.addons.index');

    Route::post('/hotel/addons', function (Request $request) {
        $membership = \App\Support\HotelAddonAccess::membership();
        if (!$membership) {
            return response()->json(['message' => 'Join a hotel team first.'], 404);
        }
        if (!\App\Support\HotelAddonAccess::canManage($membership)) {
            return response()->json(['message' => 'Only Housekeeping staff can add add-ons.'], 403);
        }

        $data = $request->validate([
            'name'     => 'required|string|max:120',
            'price'    => 'required|integer|min:0|max:9999999',
            'quantity' => 'required|integer|min:0|max:99999',
            'image'    => 'nullable|string|max:900000',
        ], [
            'image.max' => 'That image is too large. Please choose a smaller one.',
        ]);

        $addon = \App\Models\HotelAddon::create([
            'group_name' => $membership->group_name,
            'faculty_id' => $membership->faculty_id,
            'group_id'   => $membership->group_id,
            'name'       => trim($data['name']),
            'price'      => (int) $data['price'],
            'quantity'   => (int) $data['quantity'],
            'image'      => \App\Support\HotelImageStore::persist(
                $data['image'] ?? null,
                $membership->faculty_id,
                $membership->group_name
            ),
        ]);

        // Nothing can be out on loan yet, so the fresh row's reserved count is zero.
        return response()->json(['item' => $addon->toTemplateArray(0)], 201);
    })->name('hotel.addons.store');

    Route::patch('/hotel/addons/{id}', function (Request $request, $id) {
        $membership = \App\Support\HotelAddonAccess::membership();
        if (!$membership) {
            return response()->json(['message' => 'Join a hotel team first.'], 404);
        }
        if (!\App\Support\HotelAddonAccess::canManage($membership)) {
            return response()->json(['message' => 'Only Housekeeping staff can edit add-ons.'], 403);
        }

        $addon = \App\Models\HotelAddon::where('hotel_addon_id', $id)
            ->where('group_name', $membership->group_name)
            ->where('faculty_id', $membership->faculty_id)
            ->firstOrFail();

        $data = $request->validate([
            'name'     => 'sometimes|string|max:120',
            'price'    => 'sometimes|integer|min:0|max:9999999',
            'quantity' => 'sometimes|integer|min:0|max:99999',
            'image'    => 'sometimes|nullable|string|max:900000',
        ], [
            'image.max' => 'That image is too large. Please choose a smaller one.',
        ]);

        if (array_key_exists('name', $data))     $addon->name     = trim($data['name']);
        if (array_key_exists('price', $data))    $addon->price    = (int) $data['price'];
        if (array_key_exists('quantity', $data)) $addon->quantity = (int) $data['quantity'];
        if (array_key_exists('image', $data))    $addon->image    = \App\Support\HotelImageStore::persist(
            $data['image'],
            $membership->faculty_id,
            $membership->group_name
        );
        $addon->save();

        // Lowering quantity below what is already lent out is allowed — the stays that
        // hold those add-ons are real. availability floors at 0 until they end.
        $reserved = \App\Support\HotelAddonDesk::reservedFor($membership);

        return response()->json([
            'item' => $addon->toTemplateArray($reserved[$addon->hotel_addon_id] ?? 0),
        ]);
    })->name('hotel.addons.update');

    /*
    |--------------------------------------------------------------------------
    | Hotel food orders — room service and dine-in. Front Desk places both (room
    | service for a stay, dine-in for a table it reserved), Restaurant Services
    | may also take a dine-in order tableside, and cooks and delivers every one.
    |--------------------------------------------------------------------------
    */

    Route::get('/hotel/orders', function (Request $request) {
        $membership = \App\Support\HotelOrderAccess::membership();
        if (!$membership) {
            return response()->json(['orders' => [], 'can_place' => false, 'can_fulfill' => false, 'can_place_dine_in' => false]);
        }

        // Tenancy is the base of the query, never one of the optional filters below —
        // nothing they do can widen it, only narrow it further.
        $query = HotelFoodOrder::where('group_name', $membership->group_name)
            ->where('faculty_id', $membership->faculty_id);

        // Each filter is ignored unless it is well-formed, so a junk query string can
        // never quietly change the answer. Note the strict in_array on type rather than
        // normalizeOrderType(), which defaults anything it does not recognise to
        // room_service — ?type=bogus has to mean "no type filter", not "room service".
        $filtered = false;

        $status = trim((string) $request->query('status', ''));
        if ($status !== '' && in_array($status, HotelFoodOrder::STATUSES, true)) {
            $query->where('status', $status);
            $filtered = true;
        }

        $type = trim((string) $request->query('type', ''));
        if ($type !== '' && in_array($type, HotelFoodOrder::ORDER_TYPES, true)) {
            $query->where('order_type', $type);
            $filtered = true;
        }

        // created_at is a timestamp and these bounds are bare YYYY-MM-DD, so
        // "created_at <= '2026-08-19'" would read as 2026-08-19 00:00:00 and drop that
        // whole day. The upper bound is half-open against the next midnight instead,
        // which covers the day and still uses the index. Both bounds are UTC, matching
        // how the column is stored.
        $from = (string) $request->query('from', '');
        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $from)) {
            $query->where('created_at', '>=', $from . ' 00:00:00');
            $filtered = true;
        }

        $to = (string) $request->query('to', '');
        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $to)) {
            $query->where('created_at', '<', date('Y-m-d', strtotime($to . ' +1 day')) . ' 00:00:00');
            $filtered = true;
        }

        $query->orderByDesc('hotel_food_order_id');

        // The two live order boards ask unfiltered and want the most recent 200 tickets,
        // which is what they have always had. A report asks a narrower question and must
        // not have its answer silently truncated, so a filtered read is uncapped.
        if (!$filtered) {
            $query->limit(200);
        }

        $orders = $query->get()->map(fn ($order) => $order->toTemplateArray());

        return response()->json([
            'orders'      => $orders,
            'can_place'   => \App\Support\HotelOrderAccess::canPlace($membership),
            'can_fulfill' => \App\Support\HotelOrderAccess::canFulfill($membership),
            // Front Desk orders for the table it reserved, so this is not can_place.
            'can_place_dine_in' => \App\Support\HotelOrderAccess::canPlaceDineIn($membership),
        ]);
    })->name('hotel.orders.index');

    Route::post('/hotel/orders', function (Request $request) {
        $membership = \App\Support\HotelOrderAccess::membership();
        if (!$membership) {
            return response()->json(['message' => 'Join a hotel team first.'], 404);
        }

        // Absent order_type means room-service — the only kind that existed before
        // dine-in, and the guest-facing booking flow never sends the field at all.
        $orderType = HotelFoodOrder::normalizeOrderType($request->input('order_type'));
        $isDineIn = $orderType === 'dine_in';

        if ($isDineIn) {
            if (!\App\Support\HotelOrderAccess::canPlaceDineIn($membership)) {
                return response()->json(['message' => 'Only Front Desk or Restaurant Services staff can take a dine-in order.'], 403);
            }
        } elseif (!\App\Support\HotelOrderAccess::canPlace($membership)) {
            return response()->json(['message' => 'Only Front Desk staff can place room-service orders.'], 403);
        }

        $data = $request->validate([
            'room_number'          => ($isDineIn ? 'nullable' : 'required') . '|string|max:100',
            'guest_name'           => ($isDineIn ? 'nullable' : 'required') . '|string|max:255',
            'dine_in_table_id'     => ($isDineIn ? 'required' : 'nullable') . '|integer',
            'items'                => 'required|array|min:1|max:50',
            'items.*.name'         => 'required|string|max:255',
            'items.*.menu_item_id' => 'nullable|integer',
            'items.*.dbId'         => 'nullable|integer',
            'items.*.price'        => 'nullable|integer|min:0',
            'items.*.qty'          => 'required|integer|min:1|max:99',
        ]);

        $table = null;
        $booking = null;
        if ($isDineIn) {
            $table = HotelDineInTable::where('hotel_dine_in_table_id', $data['dine_in_table_id'])
                ->where('group_name', $membership->group_name)
                ->where('faculty_id', $membership->faculty_id)
                ->first();
            if (!$table) {
                return response()->json(['message' => 'That table was not found.'], 404);
            }
            if ($table->status !== 'Occupied') {
                return response()->json(['message' => 'Seat a guest at this table before ordering.'], 422);
            }
        } else {
            // Room service is charged to a stay, so it has to resolve to one. The room
            // is identified by name (that is all the ordering screen has), and only a
            // guest who has actually checked in can order to their room.
            $room = HotelRoom::with('activeBooking')
                ->where('group_name', $membership->group_name)
                ->where('faculty_id', $membership->faculty_id)
                ->where('name', trim($data['room_number']))
                ->first();

            $booking = $room?->activeBooking;
            if (!$booking || $booking->status !== 'Checked In') {
                return response()->json([
                    'message' => 'That room has no checked-in guest to bill this order to.',
                ], 422);
            }
        }

        $items = HotelFoodOrder::sanitizeItems($data['items']);
        if (!$items) {
            return response()->json(['message' => 'Add at least one menu item to the order.'], 422);
        }

        try {
            $order = \Illuminate\Support\Facades\DB::transaction(function () use ($membership, $data, $items, $orderType, $table, $booking) {
                $menuItems = \App\Support\HotelOrderAccess::lockMenuItemsFor($membership, $items);

                // Same dish can appear on more than one line; charge stock for the sum.
                $wanted = [];
                foreach ($items as $line) {
                    $menuItem = \App\Support\HotelOrderAccess::matchMenuItem($menuItems, $line);
                    if (!$menuItem) {
                        throw new \RuntimeException("\"{$line['name']}\" is no longer on the menu.");
                    }
                    $wanted[$menuItem->hotel_menu_item_id] = ($wanted[$menuItem->hotel_menu_item_id] ?? 0) + $line['qty'];
                }

                foreach ($wanted as $menuItemId => $qty) {
                    $menuItem = $menuItems->firstWhere('hotel_menu_item_id', $menuItemId);
                    if ($menuItem->stock < $qty) {
                        throw new \RuntimeException("Only {$menuItem->stock} left of \"{$menuItem->name}\".");
                    }
                }

                foreach ($wanted as $menuItemId => $qty) {
                    $menuItem = $menuItems->firstWhere('hotel_menu_item_id', $menuItemId);
                    $menuItem->stock -= $qty;
                    $menuItem->save();
                }

                return HotelFoodOrder::create([
                    'group_name'       => $membership->group_name,
                    'faculty_id'       => $membership->faculty_id,
                    'group_id'         => $membership->group_id,
                    'order_type'       => $orderType,
                    'hotel_booking_id' => $booking?->hotel_booking_id,
                    'dine_in_table_id' => $table?->hotel_dine_in_table_id,
                    'room_number'      => isset($data['room_number']) ? trim($data['room_number']) : null,
                    'guest_name'       => trim($data['guest_name'] ?? $table?->guest_name ?? ''),
                    'items'            => $items,
                    'total'            => HotelFoodOrder::totalFor($items),
                    // Straight to the stove: Restaurant Services owns the order from
                    // the moment it is placed, so there is nothing to accept first.
                    'status'           => 'Preparing',
                    'placed_by'        => auth()->user()?->name,
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
        $order = HotelFoodOrder::where('hotel_food_order_id', $id)
            ->where('group_name', $membership->group_name)
            ->where('faculty_id', $membership->faculty_id)
            ->firstOrFail();

        $data = $request->validate([
            'status' => 'required|string|max:50',
        ]);

        $next = HotelFoodOrder::normalizeStatus($data['status']);

        // The kitchen owns every step, delivery included. Front Desk reads the status
        // and nothing more, so any write from them is refused rather than ignored.
        if (!\App\Support\HotelOrderAccess::canFulfill($membership)) {
            return response()->json([
                'message' => 'Only Restaurant Services staff can update an order.',
            ], 403);
        }

        // A room-service order is billed to a stay the moment it is placed, so there
        // is no cancelling it — it runs to Completed. Dine-in keeps the exit: nothing
        // is charged to a room there, and a table can change its mind.
        if ($next === 'Cancelled' && $order->order_type !== 'dine_in') {
            return response()->json([
                'message' => 'A room-service order cannot be cancelled. See it through to Completed.',
            ], 422);
        }

        // Status only ever moves forward, for every role — the kitchen included.
        // Once Completed or Cancelled, an order is done; nothing may reopen it.
        if (!HotelFoodOrder::isForwardTransition($order->status, $next)) {
            return response()->json([
                'message' => $order->status . ' cannot go back to ' . $next . '. Status only moves forward.',
            ], 422);
        }

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

    /*
    |--------------------------------------------------------------------------
    | Guest complaints (Front Desk records, Maintenance / Housekeeping resolve)
    |--------------------------------------------------------------------------
    */

    Route::get('/hotel/complaints', function (Request $request) {
        $membership = \App\Support\HotelComplaintAccess::membership();
        if (!$membership) {
            return response()->json([
                'complaints' => [],
                'can_file' => false,
                'handled_departments' => [],
                'categories' => HotelComplaint::CATEGORY_DEPARTMENTS,
            ]);
        }

        $complaints = HotelComplaint::where('group_name', $membership->group_name)
            ->where('faculty_id', $membership->faculty_id)
            ->orderByDesc('hotel_complaint_id')
            ->limit(200)
            ->get()
            ->map(fn ($complaint) => $complaint->toTemplateArray());

        return response()->json([
            'complaints'          => $complaints,
            'can_file'            => \App\Support\HotelComplaintAccess::canFile($membership),
            'handled_departments' => \App\Support\HotelComplaintAccess::handledDepartments($membership),
            'categories'          => HotelComplaint::CATEGORY_DEPARTMENTS,
        ]);
    })->name('hotel.complaints.index');

    Route::post('/hotel/complaints', function (Request $request) {
        $membership = \App\Support\HotelComplaintAccess::membership();
        if (!$membership) {
            return response()->json(['message' => 'Join a hotel team first.'], 404);
        }
        if (!\App\Support\HotelComplaintAccess::canFile($membership)) {
            return response()->json(['message' => 'Only Front Desk staff can record a guest complaint.'], 403);
        }

        $data = $request->validate([
            'room_number' => 'required|string|max:100',
            'guest_name'  => 'nullable|string|max:255',
            'category'    => 'required|string|max:100',
            'department'  => 'nullable|string|max:50',
            'details'     => 'required|string|max:2000',
        ]);

        $category = HotelComplaint::normalizeCategory($data['category']);

        $complaint = HotelComplaint::create([
            'group_name'  => $membership->group_name,
            'faculty_id'  => $membership->faculty_id,
            'group_id'    => $membership->group_id,
            'room_number' => trim($data['room_number']),
            'guest_name'  => isset($data['guest_name']) ? trim($data['guest_name']) : null,
            'category'    => $category,
            // No explicit department means take the category's default.
            'department'  => empty($data['department'])
                ? HotelComplaint::departmentForCategory($category)
                : HotelComplaint::normalizeDepartment($data['department']),
            'details'     => trim($data['details']),
            'status'      => 'Open',
            'filed_by'    => auth()->user()?->name,
        ]);

        \App\Support\Notifier::complaintFiled(auth()->user(), $complaint);

        ActivityLog::record(
            auth()->user(),
            ActivityLog::COMPLAINT_FILED,
            'Recorded a ' . $complaint->departmentLabel() . ' complaint for room '
                . $complaint->room_number . ' (' . $complaint->category . ').'
        );

        return response()->json(['complaint' => $complaint->toTemplateArray()], 201);
    })->name('hotel.complaints.store');

    Route::patch('/hotel/complaints/{id}', function (Request $request, $id) {
        $membership = \App\Support\HotelComplaintAccess::membership();
        if (!$membership) {
            return response()->json(['message' => 'Join a hotel team first.'], 404);
        }

        $complaint = HotelComplaint::where('hotel_complaint_id', $id)
            ->where('group_name', $membership->group_name)
            ->where('faculty_id', $membership->faculty_id)
            ->firstOrFail();

        $data = $request->validate([
            'status'          => 'sometimes|string|max:50',
            'department'      => 'sometimes|string|max:50',
            'resolution_note' => 'sometimes|nullable|string|max:2000',
        ]);

        // Authorised against the department the complaint sits in *now* — that is
        // also what lets Housekeeping hand a mis-routed one to Maintenance.
        $handles = \App\Support\HotelComplaintAccess::canHandle($membership, $complaint->department);
        $isFrontDesk = \App\Support\HotelComplaintAccess::canFile($membership);

        $reassignedFrom = null;
        if (array_key_exists('department', $data)) {
            if (!$handles) {
                return response()->json([
                    'message' => 'Only ' . $complaint->departmentLabel() . ' staff can reassign this complaint.',
                ], 403);
            }
            // A closed complaint has nowhere to be handed to — reassigning one used to
            // reopen it, which is the same backward move the status pills forbid.
            if (in_array($complaint->status, ['Resolved', 'Cancelled'], true)) {
                return response()->json([
                    'message' => 'This complaint is already ' . $complaint->status . ' and cannot be reassigned.',
                ], 422);
            }
            $next = HotelComplaint::normalizeDepartment($data['department']);
            if ($next !== $complaint->department) {
                $reassignedFrom = $complaint->department;
                $complaint->department = $next;
                // The status stays where the first department left it: status only ever
                // moves forward, so a handed-over complaint cannot drop back to Open.
                // handled_by clears because the new department has not worked it yet;
                // any note the first department left stays — it is what they found.
                $complaint->handled_by = null;
            }
        }

        if (array_key_exists('status', $data)) {
            $next = HotelComplaint::normalizeStatus($data['status']);
            // Front Desk took the complaint from the guest, so they may withdraw it —
            // but working it is the department's job.
            $maySet = $handles || ($isFrontDesk && $next === 'Cancelled');
            if (!$maySet) {
                return response()->json([
                    'message' => 'Only ' . $complaint->departmentLabel() . ' staff can update this complaint.',
                ], 403);
            }

            // Status only ever moves forward, for every role. Once Resolved or
            // Cancelled, a complaint is done; nothing may reopen it this way —
            // reassigning to the other department is the one deliberate reset,
            // handled above, not through this field.
            if (!HotelComplaint::isForwardTransition($complaint->status, $next)) {
                return response()->json([
                    'message' => $complaint->status . ' cannot go back to ' . $next . '. Status only moves forward.',
                ], 422);
            }

            $complaint->status = $next;
            $complaint->resolved_at = in_array($next, ['Resolved', 'Cancelled'], true) ? now() : null;
            if ($handles) {
                $complaint->handled_by = auth()->user()?->name;
            }
        }

        if (array_key_exists('resolution_note', $data)) {
            if (!$handles) {
                return response()->json([
                    'message' => 'Only ' . $complaint->departmentLabel() . ' staff can leave a resolution note.',
                ], 403);
            }
            $note = $data['resolution_note'];
            $complaint->resolution_note = $note === null ? null : trim($note);
        }

        $closed = $complaint->isDirty('status')
            && in_array($complaint->status, ['Resolved', 'Cancelled'], true);

        $complaint->save();

        if ($reassignedFrom) {
            \App\Support\Notifier::complaintReassigned(auth()->user(), $complaint, $reassignedFrom);
        }
        if ($closed) {
            \App\Support\Notifier::complaintResolved(auth()->user(), $complaint);

            ActivityLog::record(
                auth()->user(),
                ActivityLog::COMPLAINT_RESOLVED,
                'Marked the room ' . $complaint->room_number . ' complaint ('
                    . $complaint->category . ') as ' . $complaint->status . '.'
            );

            // Housekeeping's pass paused for this issue — see whether it can now
            // move on to a final re-inspection.
            \App\Support\HotelHousekeepingDesk::onIssueClosed($complaint, auth()->user());
        }

        return response()->json(['complaint' => $complaint->toTemplateArray()]);
    })->name('hotel.complaints.update');

    /*
    |--------------------------------------------------------------------------
    | Housekeeping inspections (opened automatically on check-out)
    |--------------------------------------------------------------------------
    */

    Route::get('/hotel/inspections', function (Request $request) {
        $membership = \App\Support\HotelHousekeepingAccess::membership();
        if (!$membership) {
            return response()->json([
                'inspections' => [],
                'can_inspect' => false,
                'findings'    => \App\Models\HotelRoomInspection::FINDINGS,
                'statuses'    => \App\Models\HotelRoomInspection::STATUSES,
                'categories'  => HotelComplaint::CATEGORY_DEPARTMENTS,
            ]);
        }

        $inspections = \App\Models\HotelRoomInspection::with(['room', 'complaints'])
            ->where('group_name', $membership->group_name)
            ->where('faculty_id', $membership->faculty_id)
            ->orderByDesc('hotel_room_inspection_id')
            ->limit(200)
            ->get()
            ->map(fn ($inspection) => $inspection->toTemplateArray());

        return response()->json([
            'inspections' => $inspections,
            'can_inspect' => \App\Support\HotelHousekeepingAccess::canInspect($membership),
            'findings'    => \App\Models\HotelRoomInspection::FINDINGS,
            'statuses'    => \App\Models\HotelRoomInspection::STATUSES,
            'categories'  => HotelComplaint::CATEGORY_DEPARTMENTS,
        ]);
    })->name('hotel.inspections.index');

    Route::patch('/hotel/inspections/{id}', function (Request $request, $id) {
        $membership = \App\Support\HotelHousekeepingAccess::membership();
        if (!$membership) {
            return response()->json(['message' => 'Join a hotel team first.'], 404);
        }
        if (!\App\Support\HotelHousekeepingAccess::canInspect($membership)) {
            return response()->json(['message' => 'Only Housekeeping staff can update an inspection.'], 403);
        }

        $inspection = \App\Models\HotelRoomInspection::where('hotel_room_inspection_id', $id)
            ->where('group_name', $membership->group_name)
            ->where('faculty_id', $membership->faculty_id)
            ->firstOrFail();

        $data = $request->validate([
            'action'  => ['required', 'string', Rule::in(['start', 'record', 'complete'])],
            'finding' => ['sometimes', 'nullable', 'string', Rule::in(array_keys(\App\Models\HotelRoomInspection::FINDINGS))],
            'notes'   => 'sometimes|nullable|string|max:2000',
        ]);

        $actor = auth()->user();
        $by = $actor?->name;

        try {
            switch ($data['action']) {
                case 'start':
                    \App\Support\HotelHousekeepingDesk::startInspection($inspection, $by);
                    break;
                case 'record':
                    if (empty($data['finding'])) {
                        return response()->json(['message' => 'Choose what you found before saving.'], 422);
                    }
                    \App\Support\HotelHousekeepingDesk::recordFinding($inspection, $data['finding'], $data['notes'] ?? null, $by);
                    break;
                case 'complete':
                    \App\Support\HotelHousekeepingDesk::complete($inspection, $by, $actor);
                    break;
            }
        } catch (\RuntimeException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }

        return response()->json(['inspection' => $inspection->fresh(['room', 'complaints'])->toTemplateArray()]);
    })->name('hotel.inspections.update');

    Route::post('/hotel/inspections/{id}/issues', function (Request $request, $id) {
        $membership = \App\Support\HotelHousekeepingAccess::membership();
        if (!$membership) {
            return response()->json(['message' => 'Join a hotel team first.'], 404);
        }
        if (!\App\Support\HotelHousekeepingAccess::canInspect($membership)) {
            return response()->json(['message' => 'Only Housekeeping staff can report an issue.'], 403);
        }

        $inspection = \App\Models\HotelRoomInspection::where('hotel_room_inspection_id', $id)
            ->where('group_name', $membership->group_name)
            ->where('faculty_id', $membership->faculty_id)
            ->firstOrFail();

        if (!in_array($inspection->status, ['Pending', 'Inspecting', 'Awaiting Re-inspection'], true)) {
            return response()->json(['message' => 'This inspection already has an issue awaiting Maintenance.'], 409);
        }

        $data = $request->validate([
            'category'   => 'nullable|string|max:100',
            'department' => 'nullable|string|max:50',
            'details'    => 'required|string|max:2000',
        ]);

        \App\Support\HotelHousekeepingDesk::reportIssue($inspection, $data, auth()->user());

        return response()->json(['inspection' => $inspection->fresh(['room', 'complaints'])->toTemplateArray()], 201);
    })->name('hotel.inspections.issues.store');

    // One page, three doors: each role opens it from its own module so the shell
    // keeps that role's theme, sidebar and Back target.
    Route::get('/frontdesk/complaints', function () {
        $data = \App\Support\DepartmentTemplatePage::boot(auth()->user(), 'front_desk');
        return view('students.complaints.manage', $data);
    })->name('frontdesk.complaints');
    Route::get('/maintenance/complaints', function () {
        $data = \App\Support\DepartmentTemplatePage::boot(auth()->user(), 'maintenance');
        return view('students.complaints.manage', $data);
    })->name('maintenance.complaints');
    Route::get('/housekeeping/complaints', function () {
        $data = \App\Support\DepartmentTemplatePage::boot(auth()->user(), 'housekeeping');
        return view('students.complaints.manage', $data);
    })->name('housekeeping.complaints');

    /*
    |--------------------------------------------------------------------------
    | Dine-in tables (Restaurant Management manages, Front Desk reserves one for a
    | customer and then orders at it)
    |--------------------------------------------------------------------------
    */

    Route::get('/hotel/tables', function (Request $request) {
        $membership = \App\Support\HotelTableAccess::membership();
        if (!$membership) {
            return response()->json(['tables' => [], 'can_manage' => false, 'can_assign' => false]);
        }

        $tables = HotelDineInTable::where('group_name', $membership->group_name)
            ->where('faculty_id', $membership->faculty_id)
            ->orderBy('name')
            ->get()
            ->map(fn ($table) => $table->toTemplateArray());

        return response()->json([
            'tables'      => $tables,
            'can_manage'  => \App\Support\HotelTableAccess::canManage($membership),
            'can_assign'  => \App\Support\HotelTableAccess::canAssign($membership),
        ]);
    })->name('hotel.tables.index');

    Route::post('/hotel/tables', function (Request $request) {
        $membership = \App\Support\HotelTableAccess::membership();
        if (!$membership) {
            return response()->json(['message' => 'Join a hotel team first.'], 404);
        }
        if (!\App\Support\HotelTableAccess::canManage($membership)) {
            return response()->json(['message' => 'Only Restaurant Management staff can add tables.'], 403);
        }

        $data = $request->validate([
            'name'     => 'required|string|max:100',
            'capacity' => 'required|integer|min:1|max:50',
        ]);

        $table = HotelDineInTable::create([
            'group_name' => $membership->group_name,
            'faculty_id' => $membership->faculty_id,
            'group_id'   => $membership->group_id,
            'name'       => trim($data['name']),
            'capacity'   => (int) $data['capacity'],
            'status'     => 'Available',
        ]);

        return response()->json(['table' => $table->toTemplateArray()], 201);
    })->name('hotel.tables.store');

    Route::patch('/hotel/tables/{id}', function (Request $request, $id) {
        $membership = \App\Support\HotelTableAccess::membership();
        if (!$membership) {
            return response()->json(['message' => 'Join a hotel team first.'], 404);
        }

        $table = HotelDineInTable::where('hotel_dine_in_table_id', $id)
            ->where('group_name', $membership->group_name)
            ->where('faculty_id', $membership->faculty_id)
            ->firstOrFail();

        $manages = \App\Support\HotelTableAccess::canManage($membership);
        $assigns = \App\Support\HotelTableAccess::canAssign($membership);

        $data = $request->validate([
            'name'        => 'sometimes|string|max:100',
            'capacity'    => 'sometimes|integer|min:1|max:50',
            'close'       => 'sometimes|boolean',
            'guest_name'  => 'sometimes|nullable|string|max:255',
            'party_size'  => 'sometimes|integer|min:1|max:50',
        ]);

        $assignedNow = false;
        $closedNow = false;

        if ($request->boolean('close')) {
            if (!$manages) {
                return response()->json(['message' => 'Only Restaurant Management staff can close a table.'], 403);
            }

            $openOrder = HotelFoodOrder::where('dine_in_table_id', $table->hotel_dine_in_table_id)
                ->whereIn('status', ['Preparing'])
                ->exists();
            if ($openOrder) {
                return response()->json([
                    'message' => 'This table still has an order in the kitchen — mark it Ready or Cancelled first.',
                ], 422);
            }

            $table->status = 'Available';
            $table->guest_name = null;
            $table->party_size = null;
            $table->assigned_by = null;
            $table->assigned_at = null;
            $closedNow = true;
        } elseif (array_key_exists('guest_name', $data) || array_key_exists('party_size', $data)) {
            if (!$assigns) {
                return response()->json(['message' => 'Only Front Desk staff can reserve a table for a guest.'], 403);
            }
            if ($table->status !== 'Available') {
                return response()->json(['message' => 'This table is already occupied.'], 422);
            }
            $partySize = (int) ($data['party_size'] ?? 0);
            if ($partySize < 1) {
                return response()->json(['message' => 'Enter how many guests are in the party.'], 422);
            }
            if ($partySize > $table->capacity) {
                return response()->json([
                    'message' => "This table seats {$table->capacity} — the party is too large for it.",
                ], 422);
            }

            $table->status = 'Occupied';
            $table->guest_name = isset($data['guest_name']) ? trim($data['guest_name']) : null;
            $table->party_size = $partySize;
            $table->assigned_by = auth()->user()?->name;
            $table->assigned_at = now();
            $assignedNow = true;
        } else {
            if (!$manages) {
                return response()->json(['message' => 'Only Restaurant Management staff can edit a table.'], 403);
            }
            if (array_key_exists('name', $data)) {
                $table->name = trim($data['name']);
            }
            if (array_key_exists('capacity', $data)) {
                $table->capacity = (int) $data['capacity'];
            }
        }

        $table->save();

        if ($assignedNow) {
            \App\Support\Notifier::tableAssigned(auth()->user(), $table);

            ActivityLog::record(
                auth()->user(),
                ActivityLog::TABLE_ASSIGNED,
                'Seated ' . ($table->guest_name ?: 'a guest') . ' (party of ' . $table->party_size
                    . ') at ' . $table->name . '.'
            );
        }
        if ($closedNow) {
            ActivityLog::record(
                auth()->user(),
                ActivityLog::TABLE_CLOSED,
                'Closed out ' . $table->name . '.'
            );
        }

        return response()->json(['table' => $table->toTemplateArray()]);
    })->name('hotel.tables.update');

    Route::delete('/hotel/tables/{id}', function (Request $request, $id) {
        $membership = \App\Support\HotelTableAccess::membership();
        if (!$membership) {
            return response()->json(['message' => 'Join a hotel team first.'], 404);
        }
        if (!\App\Support\HotelTableAccess::canManage($membership)) {
            return response()->json(['message' => 'Only Restaurant Management staff can remove tables.'], 403);
        }

        $table = HotelDineInTable::where('hotel_dine_in_table_id', $id)
            ->where('group_name', $membership->group_name)
            ->where('faculty_id', $membership->faculty_id)
            ->firstOrFail();

        if ($table->status !== 'Available') {
            return response()->json(['message' => 'Close the table before removing it.'], 422);
        }

        $table->delete();

        return response()->json(['deleted' => true]);
    })->name('hotel.tables.destroy');

    Route::get('/frontdesk/dine-in', function () {
        $data = \App\Support\DepartmentTemplatePage::boot(auth()->user(), 'front_desk');
        return view('students.frontdesk.dine-in', $data);
    })->name('frontdesk.dine-in');

    Route::get('/frontdesk/room-service', function () {
        $data = \App\Support\DepartmentTemplatePage::boot(auth()->user(), 'front_desk');
        return view('students.frontdesk.room-service', $data);
    })->name('frontdesk.room-service');

    // Read-only: everything the team has finished, drawn from the existing
    // bookings / orders / complaints / inspections endpoints. No new state.
    Route::get('/frontdesk/reports', function () {
        $data = \App\Support\DepartmentTemplatePage::boot(auth()->user(), 'front_desk');
        return view('students.frontdesk.reports', $data);
    })->name('frontdesk.reports');

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

    Route::get('/housekeeping/inspections', function () {
        $data = \App\Support\DepartmentTemplatePage::boot(auth()->user(), 'housekeeping');
        return view('students.housekeeping.inspections', $data);
    })->name('housekeeping.inspections');

    Route::get('/housekeeping/addons', function () {
        $data = \App\Support\DepartmentTemplatePage::boot(auth()->user(), 'housekeeping');
        return view('students.housekeeping.addons', $data);
    })->name('housekeeping.addons');
});

