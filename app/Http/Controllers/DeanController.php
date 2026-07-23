<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Validator;
use App\Events\StudentApproved;
use App\Models\Faculty;
use App\Models\Student;
use App\Models\StudentGroup;
use App\Models\Task;
use App\Models\User;
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

        $totalTeams = (int) StudentGroup::query()
            ->selectRaw('COUNT(DISTINCT CONCAT(COALESCE(faculty_id, 0), "::", group_name)) as aggregate')
            ->value('aggregate');

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

        $teamsThisMonth = (int) StudentGroup::query()
            ->where('created_at', '>=', now()->startOfMonth())
            ->selectRaw('COUNT(DISTINCT CONCAT(COALESCE(faculty_id, 0), "::", group_name)) as aggregate')
            ->value('aggregate');

        $recentStudents = Student::with('user')
            ->latest()
            ->take(6)
            ->get();

        $recentActivity = Task::with('faculty.user')
            ->where('status', 'archived')
            ->orderByDesc('updated_at')
            ->take(6)
            ->get();

        $roleLabels = [
            'front_desk' => 'Front Desk',
            'restaurant_management' => 'Restaurant',
            'room_management' => 'Room Mgmt',
            'maintenance' => 'Maintenance',
            'housekeeping' => 'Housekeeping',
        ];

        return view('dean.dashboard', compact(
            'totalStudents',
            'totalFaculty',
            'pendingFaculty',
            'totalTeams',
            'studentTrend',
            'teamsThisMonth',
            'recentStudents',
            'recentActivity',
            'roleLabels'
        ));
    }

    public function users()
    {
        $users = User::with(['faculty', 'student.facultyClass'])->latest()->get();
        $availableBlocks = Faculty::availableBlocks();
        $systemBlocks = Faculty::existingClassLetters();

        return view('dean.usermanagement', compact('users', 'availableBlocks', 'systemBlocks'));
    }

    public function usersLive()
    {
        $users = User::with(['faculty', 'student.facultyClass'])->latest()->get()->map(function ($user) {
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
            if ($user->role === 'faculty') {
                $block = $user->faculty->block ?? null;
            } elseif ($user->role === 'student') {
                // Student's block = their Manage Students class (Class B → Block B)
                $block = $user->student?->facultyClass?->letter ?? null;
            }

            return [
                'id' => $user->id,
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
                'block_label' => Faculty::blockLabel($block),
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

        $completedTasks = \App\Models\Task::with(['faculty.user', 'student.user', 'assignedTo'])
            ->where('status', 'archived')
            ->orderByDesc('updated_at')
            ->get();

        $roleLabels = [
            'front_desk' => 'Front Desk',
            'restaurant_management' => 'Restaurant',
            'room_management' => 'Rooms',
            'maintenance' => 'Maintenance',
            'housekeeping' => 'Housekeeping',
        ];

        $allTasks = \App\Models\Task::query()
            ->orderByDesc('updated_at')
            ->limit(1000)
            ->get()
            ->groupBy('faculty_id');

        $teamActivityByFacultyGroup = [];
        foreach ($faculties as $faculty) {
            $facultyId = (int) $faculty->id;
            $facultyTasks = $allTasks->get($facultyId, collect());
            $groups = $faculty->studentGroups
                ? $faculty->studentGroups->groupBy('group_name')
                : collect();

            foreach ($groups as $groupName => $members) {
                $memberStudentIds = $members->pluck('student_id')->filter()->map(fn ($id) => (int) $id)->unique()->all();
                $memberRoles = $members
                    ->flatMap(function ($m) {
                        $fromRelation = $m->roles->pluck('role');
                        return $fromRelation->isNotEmpty()
                            ? $fromRelation
                            : collect([$m->role])->filter();
                    })
                    ->filter()
                    ->unique()
                    ->values()
                    ->all();

                $teamActivityByFacultyGroup[$facultyId][$groupName] = $facultyTasks
                    ->filter(function ($task) use ($memberStudentIds, $memberRoles) {
                        if ($task->student_id && in_array((int) $task->student_id, $memberStudentIds, true)) {
                            return true;
                        }

                        return in_array($task->role, $memberRoles, true);
                    })
                    ->take(100)
                    ->values()
                    ->map(function ($task) use ($roleLabels) {
                        return [
                            'title' => $task->title,
                            'description' => $task->description,
                            'role' => $task->role,
                            'role_label' => $roleLabels[$task->role] ?? $task->role,
                            'priority' => strtolower($task->priority ?? 'medium'),
                            'status' => $task->status,
                            'due_date' => optional($task->due_date)->format('M d, Y'),
                            'updated_at' => optional($task->updated_at)->format('M d, Y'),
                        ];
                    })
                    ->all();
            }
        }

        return view('dean.faculties', compact('faculties', 'completedTasks', 'teamActivityByFacultyGroup', 'roleLabels', 'availableBlocks'));
    }

    public function storeFaculty(Request $request)
    {
        $validated = $request->validate([
            'first_name' => ['required', 'string', 'max:255'],
            'middle_name' => ['nullable', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'phone_number' => ['nullable', 'string', 'max:30'],
            'block' => ['required', 'string', 'in:' . implode(',', Faculty::existingClassLetters() ?: ['A']), 'unique:faculties,block'],
            'status' => ['required', 'in:active,inactive'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

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
            'user_id' => $user->id,
            'phone_number' => User::cleanOptional($validated['phone_number'] ?? null),
            'status' => $validated['status'],
            'block' => strtoupper($validated['block']),
        ]);

        return redirect()->route('dean.faculties')->with('success', 'Faculty account created successfully.');
    }

    public function storeUser(Request $request)
    {
        $validated = $request->validate([
            'first_name' => ['required', 'string', 'max:255'],
            'middle_name' => ['nullable', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'phone_number' => ['nullable', 'string', 'max:30'],
            'role' => ['required', 'in:faculty,student'],
            'block' => [
                'nullable',
                'string',
                'in:' . implode(',', Faculty::existingClassLetters() ?: ['A']),
                'unique:faculties,block',
                'required_if:role,faculty',
            ],
            'status' => ['required', 'in:active,inactive'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

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

        if (Schema::hasColumn('users', 'status')) {
            $userData['status'] = $validated['status'];
        }

        if (Schema::hasColumn('users', 'phone_number')) {
            $userData['phone_number'] = User::cleanOptional($validated['phone_number'] ?? null);
        }

        $user = User::create($userData);

        if ($validated['role'] === 'faculty') {
            Faculty::create([
                'user_id' => $user->id,
                'phone_number' => User::cleanOptional($validated['phone_number'] ?? null),
                'status' => $validated['status'],
                'block' => strtoupper($validated['block']),
            ]);
        }

        $message = $validated['role'] === 'faculty'
            ? 'Faculty account created successfully.'
            : 'Student account created successfully.';

        return redirect()->route('dean.users')->with('success', $message);
    }

    public function updateUser(Request $request, User $user)
    {
        $rules = [
            'status' => ['required', 'in:active,inactive'],
        ];

        if ($user->role === 'faculty') {
            $selectable = Faculty::selectableBlocksForFaculty(
                $user->faculty?->id,
                $user->faculty?->block
            );
            $rules['block'] = [
                'required',
                'string',
                'in:' . implode(',', $selectable ?: ['__none__']),
            ];
        }

        $validated = $request->validate($rules);

        if (Schema::hasColumn('users', 'status')) {
            $user->status = $validated['status'];
        }

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

        return redirect()->route('dean.users')->with('success', 'User updated successfully.');
    }

    public function reports()
    {
        $roleLabels = [
            'front_desk' => 'Front Desk',
            'restaurant_management' => 'Restaurant',
            'room_management' => 'Room',
            'maintenance' => 'Maintenance',
            'housekeeping' => 'Housekeeping',
        ];

        $completedTasks = Task::with(['faculty.user', 'student.user', 'assignedTo'])
            ->where('status', 'archived')
            ->orderByDesc('updated_at')
            ->get();

        $membershipByStudentId = StudentGroup::with(['roles', 'student.user', 'faculty.user'])
            ->get()
            ->groupBy('student_id');

        $teamMembersByKey = StudentGroup::with(['roles', 'student.user'])
            ->get()
            ->groupBy(fn ($m) => ((int) ($m->faculty_id ?? 0)) . '::' . ($m->group_name ?? 'Unassigned'));

        $buckets = [];

        foreach ($completedTasks as $task) {
            $studentId = $task->student_id ? (int) $task->student_id : null;
            if (!$studentId && $task->assigned_to) {
                $studentId = Student::where('user_id', $task->assigned_to)->value('id');
                $studentId = $studentId ? (int) $studentId : null;
            }

            $membership = $studentId
                ? ($membershipByStudentId->get($studentId)?->first())
                : null;

            $facultyId = (int) ($task->faculty_id ?? $membership?->faculty_id ?? 0);
            $teamName = $membership?->group_name ?: 'Unassigned';
            $key = $facultyId . '::' . $teamName;

            $fUser = $task->faculty?->user ?? $membership?->faculty?->user;
            $facultyName = trim(implode(' ', array_filter([
                $fUser?->last_name,
                $fUser?->first_name,
            ]))) ?: ($fUser?->name ?? '—');

            $studentUser = $task->student?->user ?? $task->assignedTo;
            $studentName = trim(implode(' ', array_filter([
                $studentUser?->last_name,
                $studentUser?->first_name,
                $studentUser?->middle_name,
            ]))) ?: ($studentUser?->name ?? '—');

            if (!isset($buckets[$key])) {
                $buckets[$key] = [
                    'id' => $key,
                    'team_name' => $teamName,
                    'faculty_id' => $facultyId,
                    'faculty_name' => $facultyName,
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
                'student_name' => $studentName,
                'role' => $roleKey,
                'role_label' => $roleLabels[$roleKey] ?? $roleKey,
                'due_date' => optional($task->due_date)->format('M d, Y'),
                'completed_at' => optional($task->updated_at)->format('M d, Y'),
                'priority' => strtolower($task->priority ?? 'medium'),
            ];
        }

        foreach ($buckets as $key => &$bucket) {
            $members = $teamMembersByKey->get($key, collect());
            $bucket['members'] = $members->map(function ($m) use ($roleLabels) {
                $user = $m->student?->user;
                $name = trim(implode(' ', array_filter([
                    $user?->last_name,
                    $user?->first_name,
                    $user?->middle_name,
                ]))) ?: ($user?->name ?? 'Member');

                $roles = $m->roles->pluck('role')->filter()->values();
                if ($roles->isEmpty() && $m->role) {
                    $roles = collect([$m->role]);
                }

                return [
                    'name' => $name,
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
            $bucket['final_grade'] = 'TBA';
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

        return view('dean.reports', compact('teamReports', 'roleLabels'));
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

            if (Schema::hasColumn('users', 'status')) {
                $userData['status'] = $validated['status'] ?? 'active';
            }

            if (Schema::hasColumn('users', 'phone_number')) {
                $userData['phone_number'] = User::cleanOptional($validated['phone_number'] ?? null);
            }

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
                        'user_id' => $user->id,
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

        if ($errorCount > 0 && count($errors) <= 10) {
            session()->flash('upload_errors', $errors);
        }

        return redirect()->route('dean.users')->with('success', $message);
    }

    public function activityLogs()
    {
        $logs = Task::with('faculty.user')
            ->where('status', 'archived')
            ->orderByDesc('updated_at')
            ->paginate(15);

        return view('dean.activitylogs', compact('logs'));
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

        return redirect()->route('dean.users')->with('success', 'Student account approved successfully.');
    }

}
