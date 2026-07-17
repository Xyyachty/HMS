<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use App\Events\StudentCreated;
use App\Models\FacultyClass;
use App\Models\Student;
use App\Models\StudentGroup;
use App\Models\StudentGroupRole;
use App\Models\Task;
use App\Models\User;
use PhpOffice\PhpSpreadsheet\IOFactory;
class FacultyController extends Controller
{
    public function dashboard()
    {
        $facultyId = auth()->user()?->faculty?->id;

        $totalStudents = $facultyId
            ? Student::where('faculty_id', $facultyId)->count()
            : 0;

        $totalTeams = $facultyId
            ? (int) StudentGroup::where('faculty_id', $facultyId)
                ->selectRaw('COUNT(DISTINCT group_name) as aggregate')
                ->value('aggregate')
            : 0;

        $assignedTasks = $facultyId
            ? Task::where('faculty_id', $facultyId)->where('status', 'active')->count()
            : 0;

        return view('faculty.dashboard', compact(
            'totalStudents',
            'totalTeams',
            'assignedTasks'
        ));
    }

    public function storeGroup(Request $request)
    {
        $facultyId = auth()->user()?->faculty?->id;
        if (!$facultyId) {
            return back()->withErrors(['group_name' => 'Faculty account not found for the current user.'])->withInput();
        }

        $formSource = $request->input('_form_source', 'create_team');

        $validated = $request->validate([
            'group_name' => ['required', 'string', 'max:255'],
            'members' => ['required', 'array', 'min:1'],
            'members.*' => ['integer', 'exists:students,id'],
            'member_roles' => ['nullable', 'array'],
        ]);

        // If inserting into existing team, validate that the team already exists
        if ($formSource === 'insert_student') {
            $teamExists = StudentGroup::where('group_name', $validated['group_name'])
                ->where('faculty_id', $facultyId)
                ->exists();

            if (!$teamExists) {
                return back()->withErrors(['group_name' => 'The selected team does not exist.'])->withInput();
            }

            // Check that selected students are not already in this team
            $existingMembers = StudentGroup::where('group_name', $validated['group_name'])
                ->whereIn('student_id', $validated['members'])
                ->pluck('student_id')
                ->toArray();

            if (!empty($existingMembers)) {
                return back()->withErrors(['members' => 'Some selected students are already in this team.'])->withInput();
            }
        }

        $allowedRoles = $this->teamRoleKeys();
        $memberIds = array_values(array_unique($validated['members']));
        $rolesByMember = $this->resolveMemberRoles(
            $memberIds,
            $validated['member_roles'] ?? [],
            $allowedRoles
        );

        if ($rolesByMember === null) {
            return back()->withErrors(['member_roles' => 'Please select valid roles for each selected member.'])->withInput();
        }

        foreach ($memberIds as $studentId) {
            $roles = $rolesByMember[$studentId];

            $studentGroup = StudentGroup::create([
                'group_name' => $validated['group_name'],
                'faculty_id' => $facultyId,
                'student_id' => $studentId,
                'role' => $roles[0], // legacy column; real roles stored in student_group_roles
            ]);

            foreach ($roles as $role) {
                StudentGroupRole::create([
                    'student_group_id' => $studentGroup->id,
                    'role' => $role,
                ]);
            }
        }

        return redirect()->route('faculty.role', array_filter([
            'tab' => 'teams',
            'class' => $request->input('class_letter') ?: null,
        ]));
    }

    /** @return list<string> */
    private function teamRoleKeys(): array
    {
        return [
            'front_desk',
            'restaurant_management',
            'room_management',
            'maintenance',
            'housekeeping',
        ];
    }

    /**
     * Build per-member roles. Missing selections get rotating default hotel roles.
     *
     * @param  list<int|string>  $memberIds
     * @param  array<int|string, mixed>  $memberRoles
     * @param  list<string>  $allowedRoles
     * @return array<int, list<string>>|null  null when an explicit role is invalid
     */
    private function resolveMemberRoles(array $memberIds, array $memberRoles, array $allowedRoles): ?array
    {
        $resolved = [];
        $index = 0;

        foreach ($memberIds as $studentId) {
            $roles = $memberRoles[$studentId] ?? [];
            if (!is_array($roles)) {
                $roles = [$roles];
            }

            $roles = array_values(array_unique(array_filter($roles, fn ($r) => $r !== null && $r !== '')));

            if ($roles === []) {
                $roles = [$allowedRoles[$index % count($allowedRoles)]];
            }

            foreach ($roles as $role) {
                if (!in_array($role, $allowedRoles, true)) {
                    return null;
                }
            }

            $resolved[(int) $studentId] = $roles;
            $index++;
        }

        return $resolved;
    }

    public function students()
    {
        $facultyId = auth()->user()?->faculty?->id;

        if (!$facultyId) {
            return view('faculty.managestudent', [
                'students' => Student::whereRaw('1 = 0')->paginate(5),
                'classes' => collect(),
                'activeClass' => null,
                'openClass' => null,
                'classCapacity' => FacultyClass::CAPACITY,
            ]);
        }

        $classes = FacultyClass::ensureForFaculty($facultyId)->map(function (FacultyClass $class) {
            $class->seats_taken = $class->students()->count();
            return $class;
        });

        $openClass = $classes->firstWhere('status', 'open') ?? $classes->last();

        $requestedLetter = strtoupper((string) request('class', $openClass?->letter ?? 'A'));
        $activeClass = $classes->firstWhere('letter', $requestedLetter) ?? $openClass;

        $students = Student::with('user')
            ->where('faculty_id', $facultyId)
            ->when($activeClass, fn ($q) => $q->where('faculty_class_id', $activeClass->id))
            ->latest()
            ->paginate(5)
            ->withQueryString();

        $classCapacity = FacultyClass::CAPACITY;

        return view('faculty.managestudent', compact(
            'students',
            'classes',
            'activeClass',
            'openClass',
            'classCapacity'
        ));
    }

    public function studentsLive()
    {
        $facultyId = auth()->user()?->faculty?->id;
        $classLetter = strtoupper((string) request('class', ''));

        $query = Student::with(['user', 'facultyClass'])
            ->when($facultyId, fn ($q) => $q->where('faculty_id', $facultyId))
            ->when($classLetter !== '', function ($q) use ($facultyId, $classLetter) {
                $q->whereHas('facultyClass', function ($cq) use ($facultyId, $classLetter) {
                    $cq->where('letter', $classLetter)
                        ->when($facultyId, fn ($inner) => $inner->where('faculty_id', $facultyId));
                });
            })
            ->latest();

        $students = $query->get()->map(function ($student) {
            $user = $student->user;
            $displayName = trim(implode(' ', array_filter([
                $user?->last_name ?? null,
                $user?->first_name ?? null,
                $user?->middle_name ?? null,
            ])));

            $displayName = $displayName !== '' ? $displayName : ($user?->name ?? 'Student');

            return [
                'user_id' => $user?->id,
                'student_id' => $student->student_id,
                'name' => $displayName,
                'email' => $user?->email,
                'phone_number' => $user?->phone_number,
                'status' => $user?->status ?? 'active',
                'joined' => optional($user?->created_at)->format('M d, Y'),
                'class' => $student->facultyClass?->letter,
            ];
        });

        return response()->json($students);
    }

    public function storeStudent(Request $request)
    {
        $facultyId = auth()->user()?->faculty?->id;
        if (!$facultyId) {
            return back()->withErrors(['error' => 'Faculty account not found.'])->withInput();
        }

        $validated = $request->validate([
            'student_id' => ['required', 'string', 'max:50', 'unique:students,student_id'],
            'first_name' => ['required', 'string', 'max:255'],
            'middle_name' => ['nullable', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'max:255'],
            'phone_number' => ['nullable', 'string', 'max:30'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $emailInput = trim($validated['email']);
        $email = str_contains($emailInput, '@')
            ? strtolower($emailInput)
            : strtolower($emailInput . '@hms.edu');

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return back()->withErrors(['email' => 'Please enter a valid email address.'])->withInput();
        }

        if (User::where('email', $email)->exists()) {
            return back()->withErrors(['email' => 'Email already exists.'])->withInput();
        }

        $fullName = trim(implode(' ', array_filter([
            $validated['first_name'],
            $validated['middle_name'] ?? null,
            $validated['last_name'],
        ])));

        [$user, $student, $class] = DB::transaction(function () use ($validated, $email, $fullName, $facultyId) {
            $class = FacultyClass::claimSeat($facultyId);

            $user = User::create([
                'name' => $fullName,
                'first_name' => $validated['first_name'],
                'middle_name' => User::cleanOptional($validated['middle_name'] ?? null),
                'last_name' => $validated['last_name'],
                'email' => $email,
                'password' => Hash::make($validated['password']),
                'role' => 'student',
                'status' => 'active',
                'phone_number' => User::cleanOptional($validated['phone_number'] ?? null),
                'email_verified_at' => now(),
            ]);

            $student = Student::create([
                'user_id' => $user->id,
                'faculty_id' => $facultyId,
                'faculty_class_id' => $class->id,
                'student_id' => $validated['student_id'],
            ]);

            $class->syncCapacity();

            return [$user, $student, $class->fresh()];
        });

        event(new StudentCreated($user, $student));

        $message = 'Student account created successfully and added to ' . ($class->name ?? 'class') . '.';
        if ($class->status === 'closed') {
            $message .= ' ' . $class->name . ' is now full. A new class tab was opened.';
        }

        return redirect()
            ->route('faculty.students', ['class' => $class->letter])
            ->with('success', $message);
    }

    public function updateStudent(Request $request, $userId)
    {
        $validated = $request->validate([
            'phone_number' => ['nullable', 'string', 'max:30'],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
            'status' => ['required', 'in:active,suspended'],
        ]);

        $user = User::findOrFail($userId);

        if ($user->role !== 'student') {
            return back()->withErrors(['error' => 'Only student accounts can be updated.']);
        }

        $updateData = [
            'phone_number' => User::cleanOptional($validated['phone_number'] ?? null) !== ''
                ? User::cleanOptional($validated['phone_number'] ?? null)
                : ($user->phone_number ?? ''),
            'status' => $validated['status'],
        ];

        if (!empty($validated['password'])) {
            $updateData['password'] = Hash::make($validated['password']);
        }

        $user->update($updateData);

        return redirect()->route('faculty.students', ['class' => request('class')])->with('success', 'Student updated successfully.');
    }

    public function bulkImportStudents(Request $request)
    {
        $facultyId = auth()->user()?->faculty?->id;
        if (!$facultyId) {
            return response()->json(['message' => 'Faculty account not found.'], 403);
        }

        $request->validate([
            'excel_file' => [
                'required', 'file',
                'mimes:xlsx,xls,ods,csv',
                'max:5120',
            ],
        ]);

        $file   = $request->file('excel_file');
        $path   = $file->getRealPath();

        try {
            $spreadsheet = IOFactory::load($path);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Could not read the file: ' . $e->getMessage()], 422);
        }

        $sheet     = $spreadsheet->getActiveSheet();
        $allRows   = $sheet->toArray(null, true, true, false);

        if (empty($allRows) || count($allRows) < 2) {
            return response()->json(['message' => 'The file is empty or has no data rows.'], 422);
        }

        // Normalize header row (row index 0)
        $rawHeader = array_map(fn($h) => strtolower(trim((string) $h)), $allRows[0]);

        $required = ['student_id', 'first_name', 'last_name', 'email'];
        foreach ($required as $col) {
            if (!in_array($col, $rawHeader, true)) {
                return response()->json([
                    'message' => "Missing required column: '{$col}'. Expected columns: student_id, first_name, last_name, email, middle_name (optional), phone_number (optional)"
                ], 422);
            }
        }

        $results   = [];
        $rowNumber = 1;
        $classesOpened = [];
        $lastClassLetter = null;

        foreach (array_slice($allRows, 1) as $row) {
            $rowNumber++;

            // Map values by header name
            $data = array_combine($rawHeader, array_map(fn($v) => trim((string) ($v ?? '')), $row));

            $studentId  = User::cleanOptional($data['student_id']   ?? '');
            $firstName  = User::cleanOptional($data['first_name']   ?? '');
            $lastName   = User::cleanOptional($data['last_name']    ?? '');
            $middleName = User::cleanOptional($data['middle_name']  ?? '');
            $emailRaw   = User::cleanOptional($data['email']        ?? '');
            $phone      = User::cleanOptional($data['phone_number'] ?? '');

            // Skip completely blank rows
            if ($studentId === '' && $firstName === '' && $lastName === '' && $emailRaw === '') {
                continue;
            }

            // Basic validation
            $errors = [];
            if ($studentId === '') $errors[] = 'student_id is required';
            if ($firstName  === '') $errors[] = 'first_name is required';
            if ($lastName   === '') $errors[] = 'last_name is required';
            if ($emailRaw   === '') $errors[] = 'email is required';

            $email = str_contains($emailRaw, '@')
                ? strtolower($emailRaw)
                : strtolower($emailRaw . '@hms.edu');

            if ($emailRaw !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $errors[] = 'invalid email format';
            }

            if ($studentId !== '' && Student::where('student_id', $studentId)->exists()) {
                $errors[] = "student_id '{$studentId}' already exists";
            }

            if ($email !== '' && filter_var($email, FILTER_VALIDATE_EMAIL) && User::where('email', $email)->exists()) {
                $errors[] = "email '{$email}' already exists";
            }

            if (!empty($errors)) {
                $results[] = [
                    'row'    => $rowNumber,
                    'status' => 'failed',
                    'name'   => trim("{$lastName} {$firstName}"),
                    'reason' => implode('; ', $errors),
                ];
                continue;
            }

            $fullName        = trim(implode(' ', array_filter([$firstName, $middleName, $lastName])));
            $defaultPassword = Hash::make('password');

            try {
                [$user, $student, $classLetter] = DB::transaction(function () use (
                    $studentId, $firstName, $middleName, $lastName,
                    $email, $phone, $fullName, $defaultPassword, $facultyId
                ) {
                    $class = FacultyClass::claimSeat($facultyId);

                    $user = User::create([
                        'name'         => $fullName,
                        'first_name'   => $firstName,
                        'middle_name'  => $middleName,
                        'last_name'    => $lastName,
                        'email'        => $email,
                        'password'     => $defaultPassword,
                        'role'         => 'student',
                        'status'       => 'active',
                        'phone_number' => $phone,
                        'email_verified_at' => now(),
                    ]);

                    $student = Student::create([
                        'user_id'          => $user->id,
                        'faculty_id'       => $facultyId,
                        'faculty_class_id' => $class->id,
                        'student_id'       => $studentId,
                    ]);

                    $class->syncCapacity();
                    $class->refresh();

                    return [$user, $student, $class->letter];
                });

                event(new StudentCreated($user, $student));

                if ($lastClassLetter !== null && $classLetter !== $lastClassLetter) {
                    $classesOpened[] = $classLetter;
                }
                $lastClassLetter = $classLetter;

                $results[] = [
                    'row'        => $rowNumber,
                    'status'     => 'success',
                    'name'       => trim("{$lastName}, {$firstName}"),
                    'student_id' => $studentId,
                    'email'      => $email,
                    'class'      => $classLetter,
                ];
            } catch (\Exception $e) {
                $results[] = [
                    'row'    => $rowNumber,
                    'status' => 'failed',
                    'name'   => trim("{$lastName} {$firstName}"),
                    'reason' => 'Server error: ' . $e->getMessage(),
                ];
            }
        }

        $created = collect($results)->where('status', 'success')->count();
        $failed  = collect($results)->where('status', 'failed')->count();
        $openClass = FacultyClass::where('faculty_id', $facultyId)->where('status', 'open')->orderBy('sort_order')->first();

        $message = "{$created} student(s) imported successfully. {$failed} failed.";
        if (!empty($classesOpened)) {
            $message .= ' New class tab(s) opened: Class ' . implode(', Class ', array_unique($classesOpened)) . '.';
        }

        return response()->json([
            'message' => $message,
            'created' => $created,
            'failed'  => $failed,
            'results' => $results,
            'open_class' => $openClass?->letter,
            'classes_opened' => array_values(array_unique($classesOpened)),
        ]);
    }

    public function role()
    {
        $facultyId = auth()->user()?->faculty?->id;
        if (!$facultyId) {
            return view('faculty.pagerole', [
                'students'        => collect(),
                'groups'          => collect(),
                'allStudents'     => collect(),
                'studentTeamMap'  => collect(),
                'classes'         => collect(),
                'activeClass'     => null,
                'openClass'       => null,
                'classCapacity'   => FacultyClass::CAPACITY,
                'teamCountsByClass' => [],
                'tasksByRole'     => collect(),
                'taskCounts'      => [],
                'roles'           => [],
                'teamActivityByGroup' => [],
            ]);
        }

        $classes = FacultyClass::ensureForFaculty($facultyId)->map(function (FacultyClass $class) {
            $class->seats_taken = $class->students()->count();
            return $class;
        });

        $openClass = $classes->firstWhere('status', 'open') ?? $classes->first();
        $requestedLetter = strtoupper((string) request('class', $openClass?->letter ?? 'A'));
        $activeClass = $classes->firstWhere('letter', $requestedLetter) ?? $openClass;
        $classCapacity = FacultyClass::CAPACITY;

        $assignedStudentIds = StudentGroup::where('faculty_id', $facultyId)
            ->pluck('student_id')
            ->unique();

        // Unassigned students for Add Team / Insert — scoped to the active class tab
        $students = Student::with(['user', 'facultyClass'])
            ->where('faculty_id', $facultyId)
            ->whereNotIn('id', $assignedStudentIds)
            ->when($activeClass, fn ($q) => $q->where('faculty_class_id', $activeClass->id))
            ->latest()
            ->get();

        // Update Team modal still needs all faculty students (then filtered to members in JS)
        $allStudents = Student::with(['user', 'facultyClass'])
            ->where('faculty_id', $facultyId)
            ->orderByDesc('id')
            ->get();

        $studentTeamMap = StudentGroup::where('faculty_id', $facultyId)
            ->get(['student_id', 'group_name'])
            ->groupBy('student_id')
            ->map(fn ($rows) => $rows->pluck('group_name')->unique()->values()->all());

        $allGroups = StudentGroup::with(['student.user', 'student.facultyClass', 'roles'])
            ->where('faculty_id', $facultyId)
            ->latest()
            ->get()
            ->groupBy('group_name');

        // Attribute each team to the class shared by the majority of its members
        $teamClassIdByName = [];
        $teamCountsByClass = [];
        foreach ($classes as $class) {
            $teamCountsByClass[$class->id] = 0;
        }

        foreach ($allGroups as $groupName => $members) {
            $classIds = $members
                ->map(fn ($m) => $m->student?->faculty_class_id)
                ->filter()
                ->countBy()
                ->sortDesc();

            $majorityClassId = $classIds->keys()->first();
            $teamClassIdByName[$groupName] = $majorityClassId;

            if ($majorityClassId && isset($teamCountsByClass[$majorityClassId])) {
                $teamCountsByClass[$majorityClassId]++;
            }
        }

        $groups = $allGroups->filter(function ($members, $groupName) use ($activeClass, $teamClassIdByName) {
            if (!$activeClass) {
                return true;
            }

            return ($teamClassIdByName[$groupName] ?? null) === $activeClass->id;
        });

        // Role definitions + task counts for the Create Task tab
        $rolesMeta = [
            'front_desk'            => ['label' => 'Front Desk',            'icon' => 'mdi:desk',                  'color' => 'text-rose-500',  'bg' => 'bg-rose-50'],
            'restaurant_management' => ['label' => 'Restaurant Management', 'icon' => 'mdi:silverware-fork-knife', 'color' => 'text-amber-500', 'bg' => 'bg-amber-50'],
            'room_management'       => ['label' => 'Room Management',       'icon' => 'mdi:bed-outline',           'color' => 'text-brand',     'bg' => 'bg-brand-soft'],
            'maintenance'           => ['label' => 'Maintenance',           'icon' => 'mdi:broom',                 'color' => 'text-plum-accent','bg' => 'bg-plum-soft'],
            'housekeeping'          => ['label' => 'Housekeeping Services', 'icon' => 'mdi:sparkles',              'color' => 'text-teal-500',  'bg' => 'bg-teal-50'],
        ];

        $tasksByRole = Task::where('faculty_id', $facultyId)
            ->where('status', 'active')
            ->orderBy('due_date')
            ->orderByRaw("FIELD(priority, 'high', 'medium', 'low')")
            ->get()
            ->groupBy('role');

        $taskCounts = [];
        foreach (array_keys($rolesMeta) as $roleKey) {
            $taskCounts[$roleKey] = $tasksByRole->get($roleKey, collect())->count();
        }

        $roleLabels = [
            'front_desk'            => 'Front Desk',
            'restaurant_management' => 'Restaurant',
            'room_management'       => 'Rooms',
            'maintenance'           => 'Maintenance',
            'housekeeping'          => 'Housekeeping',
        ];

        $allTasks = Task::where('faculty_id', $facultyId)
            ->orderByDesc('updated_at')
            ->limit(500)
            ->get();

        $teamActivityByGroup = [];
        foreach ($allGroups as $groupName => $members) {
            $memberStudentIds = $members->pluck('student_id')->filter()->map(fn ($id) => (int) $id)->unique()->all();
            $memberRoles = $members
                ->flatMap(fn ($m) => $m->roles->pluck('role'))
                ->filter()
                ->unique()
                ->values()
                ->all();

            $teamActivityByGroup[$groupName] = $allTasks
                ->filter(function (Task $task) use ($memberStudentIds, $memberRoles) {
                    if ($task->student_id && in_array((int) $task->student_id, $memberStudentIds, true)) {
                        return true;
                    }

                    return in_array($task->role, $memberRoles, true);
                })
                ->take(100)
                ->values()
                ->map(function (Task $task) use ($roleLabels) {
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

        return view('faculty.pagerole', compact(
            'students',
            'allStudents',
            'studentTeamMap',
            'groups',
            'classes',
            'activeClass',
            'openClass',
            'classCapacity',
            'teamCountsByClass',
            'rolesMeta',
            'tasksByRole',
            'taskCounts',
            'teamActivityByGroup'
        ));
    }

    public function updateGroup(Request $request, $groupName)
    {
        $facultyId = auth()->user()?->faculty?->id;
        if (!$facultyId) {
            return back()->withErrors(['group_name' => 'Faculty account not found.'])->withInput();
        }

        $validated = $request->validate([
            'group_name'    => ['required', 'string', 'max:255'],
            'members'       => ['required', 'array', 'min:1'],
            'members.*'     => ['integer', 'exists:students,id'],
            'member_roles'  => ['nullable', 'array'],
        ]);

        $allowedRoles = $this->teamRoleKeys();
        $memberIds    = array_values(array_unique($validated['members']));
        $rolesByMember = $this->resolveMemberRoles(
            $memberIds,
            $validated['member_roles'] ?? [],
            $allowedRoles
        );

        if ($rolesByMember === null) {
            return back()->withErrors(['member_roles' => 'Please select valid roles for each selected member.'])->withInput();
        }

        // Delete old rows for this group/faculty then re-insert
        $oldGroups = StudentGroup::where('faculty_id', $facultyId)
            ->where('group_name', $groupName)
            ->get();

        foreach ($oldGroups as $oldGroup) {
            StudentGroupRole::where('student_group_id', $oldGroup->id)->delete();
            $oldGroup->delete();
        }

        // If a student is moved onto this team, drop them from any other team first
        $otherMemberships = StudentGroup::where('faculty_id', $facultyId)
            ->whereIn('student_id', $memberIds)
            ->where('group_name', '!=', $validated['group_name'])
            ->get();

        foreach ($otherMemberships as $other) {
            StudentGroupRole::where('student_group_id', $other->id)->delete();
            $other->delete();
        }

        foreach ($memberIds as $studentId) {
            $roles = $rolesByMember[$studentId];

            $studentGroup = StudentGroup::create([
                'group_name' => $validated['group_name'],
                'faculty_id' => $facultyId,
                'student_id' => $studentId,
                'role' => $roles[0], // legacy column; real roles stored in student_group_roles
            ]);

            foreach ($roles as $role) {
                StudentGroupRole::create([
                    'student_group_id' => $studentGroup->id,
                    'role' => $role,
                ]);
            }
        }

        return redirect()->route('faculty.role', array_filter([
            'tab' => 'teams',
            'class' => $request->input('class_letter') ?: null,
        ]))->with('success', 'Team updated successfully.');
    }

    public function tasks()
    {
        $facultyId = auth()->user()?->faculty?->id;
        if (!$facultyId) {
            return view('faculty.tasks', ['tasksByRole' => collect(), 'taskCounts' => []]);
        }

        $roles = ['front_desk', 'restaurant_management', 'room_management', 'maintenance', 'housekeeping'];

        $tasksByRole = Task::where('faculty_id', $facultyId)
            ->where('status', 'active')
            ->orderBy('due_date')
            ->orderByRaw("FIELD(priority, 'high', 'medium', 'low')")
            ->get()
            ->groupBy('role');

        $taskCounts = [];
        foreach ($roles as $role) {
            $taskCounts[$role] = $tasksByRole->get($role, collect())->count();
        }

        return view('faculty.tasks', compact('tasksByRole', 'taskCounts'));
    }

    public function storeTask(Request $request)
    {
        $facultyId = auth()->user()?->faculty?->id;
        if (!$facultyId) {
            return back()->withErrors(['title' => 'Faculty account not found.'])->withInput();
        }

        $validated = $request->validate([
            'tasks' => ['nullable', 'array'],
            'tasks.*' => ['array'],
            'task_titles' => ['nullable', 'array'],
            'task_descriptions' => ['nullable', 'array'],
            'task_priorities' => ['nullable', 'array'],
            'due_date' => ['nullable', 'date', 'after_or_equal:today'],
        ]);

        // Check if any tasks were selected
        $tasksCreated = 0;
        
        if (!empty($validated['tasks']) && is_array($validated['tasks'])) {
            foreach ($validated['tasks'] as $role => $taskIndices) {
                if (!is_array($taskIndices)) continue;
                
                foreach ($taskIndices as $index) {
                    $title = $validated['task_titles'][$role][$index] ?? null;
                    $description = $validated['task_descriptions'][$role][$index] ?? null;
                    $priority = $validated['task_priorities'][$role][$index] ?? 'medium';
                    
                    if ($title) {
                        $members = StudentGroup::with('student')
                            ->where('faculty_id', $facultyId)
                            ->whereHas('roles', fn ($q) => $q->where('role', $role))
                            ->get();

                        $payload = [
                            'faculty_id'  => $facultyId,
                            'role'        => $role,
                            'title'       => $title,
                            'description' => User::cleanOptional($description),
                            'priority'    => $priority,
                            'due_date'    => $validated['due_date'] ?? null,
                            'status'      => 'active',
                        ];

                        if ($members->isEmpty()) {
                            Task::create($payload);
                            $tasksCreated++;
                        } else {
                            foreach ($members as $member) {
                                Task::create(array_merge($payload, [
                                    'student_id'  => $member->student_id,
                                    'assigned_to' => $member->student?->user_id,
                                ]));
                                $tasksCreated++;
                            }
                        }
                    }
                }
            }
        }

        if ($tasksCreated === 0) {
            return back()->withErrors(['tasks' => 'Please select at least one task from the checklist.'])->withInput();
        }

        return redirect()->route('faculty.role', ['tab' => 'create_task'])
            ->with('success', $tasksCreated . ' task(s) created successfully.');
    }

    public function destroyTask(Task $task)
    {
        $facultyId = auth()->user()?->faculty?->id;
        if ($task->faculty_id !== $facultyId) {
            abort(403);
        }

        $role = $task->role;
        $task->delete();

        return redirect()->route('faculty.tasks', ['tab' => $role])
            ->with('success', 'Task deleted.');
    }

    public function results()
    {
        $facultyId = auth()->user()?->faculty?->id;
        if (!$facultyId) {
            abort(403, 'Faculty account not found.');
        }

        $roleLabels = [
            'front_desk' => 'Front Desk',
            'restaurant_management' => 'Restaurant',
            'room_management' => 'Room Management',
            'maintenance' => 'Maintenance',
            'housekeeping' => 'Housekeeping',
        ];

        $outputs = Task::with(['student.user', 'assignedTo'])
            ->where('faculty_id', $facultyId)
            ->where('status', 'archived')
            ->orderByDesc('updated_at')
            ->get();

        $countsByRole = $outputs->groupBy('role')->map->count();

        return view('faculty.results', compact('outputs', 'roleLabels', 'countsByRole'));
    }

    public function reports()
    {
        $facultyId = auth()->user()?->faculty?->id;
        if (!$facultyId) {
            abort(403, 'Faculty account not found.');
        }

        $completedTasks = Task::with(['student.user', 'assignedTo'])
            ->where('faculty_id', $facultyId)
            ->where('status', 'archived')
            ->orderByDesc('updated_at')
            ->get();

        $totalCompleted = $completedTasks->count();
        $totalActive = Task::where('faculty_id', $facultyId)->where('status', 'active')->count();
        $totalAssigned = $totalCompleted + $totalActive;
        $completionRate = $totalAssigned > 0 ? round(($totalCompleted / $totalAssigned) * 100) : 0;

        $byRole = $completedTasks->groupBy('role')->map->count()->sortDesc();
        $byPriority = $completedTasks->groupBy(fn ($t) => strtolower($t->priority ?? 'medium'))->map->count();
        $recentCompleted = $completedTasks->take(25);

        return view('faculty.reports', compact(
            'totalCompleted',
            'totalActive',
            'totalAssigned',
            'completionRate',
            'byRole',
            'byPriority',
            'recentCompleted'
        ));
    }

    public function activityLogs(Request $request)
    {
        $facultyId = auth()->user()?->faculty?->id;
        if (!$facultyId) {
            abort(403, 'Faculty account not found.');
        }

        $filter = $request->query('filter', 'completed');
        if (!in_array($filter, ['completed', 'active', 'all'], true)) {
            $filter = 'completed';
        }

        $base = Task::where('faculty_id', $facultyId);
        $completedCount = (clone $base)->where('status', 'archived')->count();
        $activeCount = (clone $base)->where('status', 'active')->count();

        $query = Task::where('faculty_id', $facultyId)->orderByDesc('updated_at');
        if ($filter === 'completed') {
            $query->where('status', 'archived');
        } elseif ($filter === 'active') {
            $query->where('status', 'active');
        }

        $logs = $query->paginate(15);

        return view('faculty.activitylogs', compact('logs', 'completedCount', 'activeCount'));
    }

    public function profile()
    {
        $user = auth()->user();
        $faculty = $user?->faculty;

        if (!$faculty) {
            abort(403, 'Faculty account not found.');
        }

        return view('faculty.profile', compact('user', 'faculty'));
    }

    public function updateProfile(Request $request)
    {
        $user = auth()->user();
        $faculty = $user?->faculty;

        if (!$faculty) {
            abort(403, 'Faculty account not found.');
        }

        $validated = $request->validate([
            'first_name' => ['required', 'string', 'max:255'],
            'middle_name' => ['nullable', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email,' . $user->id],
            'phone_number' => ['nullable', 'string', 'max:30'],
            'avatar' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp,gif', 'max:2048'],
            'remove_avatar' => ['nullable', 'boolean'],
        ]);

        $fullName = trim(implode(' ', array_filter([
            $validated['first_name'],
            $validated['middle_name'] ?? null,
            $validated['last_name'],
        ])));

        $userData = [
            'name' => $fullName,
            'first_name' => $validated['first_name'],
            'middle_name' => $validated['middle_name'] ?? null,
            'last_name' => $validated['last_name'],
            'email' => $validated['email'],
            'phone_number' => $validated['phone_number'] ?? null,
        ];

        if ($request->boolean('remove_avatar') && $user->avatar) {
            \Illuminate\Support\Facades\Storage::disk('public')->delete($user->avatar);
            $userData['avatar'] = null;
        }

        if ($request->hasFile('avatar')) {
            if ($user->avatar) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($user->avatar);
            }
            $userData['avatar'] = $request->file('avatar')->store('avatars/faculty', 'public');
        }

        $user->update($userData);

        $faculty->update([
            'phone_number' => $validated['phone_number'] ?? null,
        ]);

        return redirect()->route('faculty.profile')
            ->with('success', 'Profile information updated successfully.');
    }
}