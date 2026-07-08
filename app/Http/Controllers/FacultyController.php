<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use App\Events\StudentCreated;
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

        $totalStudents   = Student::count();
        $totalTeams      = $facultyId ? StudentGroup::where('faculty_id', $facultyId)->distinct('group_name')->count('group_name') : 0;
        $assignedTasks   = $facultyId ? Task::where('faculty_id', $facultyId)->whereNotNull('assigned_to')->count() : 0;
        $compliedTasks   = $facultyId ? Task::where('faculty_id', $facultyId)->where('status', 'archived')->count() : 0;

        return view('faculty.dashboard', compact('totalStudents', 'totalTeams', 'assignedTasks', 'compliedTasks'));
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
            'member_roles' => ['required', 'array'],
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

        $allowedRoles = [
            'front_desk',
            'restaurant_management',
            'room_management',
            'maintenance',
            'housekeeping',
        ];

        $memberIds = array_unique($validated['members']);

        foreach ($memberIds as $studentId) {
            $roles = $validated['member_roles'][$studentId] ?? [];
            if (!is_array($roles)) {
                $roles = [$roles];
            }

            if (empty($roles)) {
                return back()->withErrors(['member_roles' => 'Please select at least one role for each selected member.'])->withInput();
            }

            foreach ($roles as $role) {
                if (!in_array($role, $allowedRoles, true)) {
                    return back()->withErrors(['member_roles' => 'Please select valid roles for each selected member.'])->withInput();
                }
            }
        }

        foreach ($memberIds as $studentId) {
            $studentGroup = StudentGroup::create([
                'group_name' => $validated['group_name'],
                'faculty_id' => $facultyId,
                'student_id' => $studentId,
            ]);

            $roles = $validated['member_roles'][$studentId] ?? [];
            if (!is_array($roles)) {
                $roles = [$roles];
            }

            foreach ($roles as $role) {
                StudentGroupRole::create([
                    'student_group_id' => $studentGroup->id,
                    'role' => $role,
                ]);
            }
        }

        return redirect()->route('faculty.role');
    }

    public function students()
    {
        $students = Student::with('user')->latest()->paginate(5);

        return view('faculty.managestudent', compact('students'));
    }

    public function studentsLive()
    {
        $students = Student::with('user')->latest()->get()->map(function ($student) {
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
            ];
        });

        return response()->json($students);
    }

    public function storeStudent(Request $request)
    {
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

        [$user, $student] = DB::transaction(function () use ($validated, $email, $fullName) {
            $user = User::create([
                'name' => $fullName,
                'first_name' => $validated['first_name'],
                'middle_name' => $validated['middle_name'] ?? null,
                'last_name' => $validated['last_name'],
                'email' => $email,
                'password' => Hash::make($validated['password']),
                'role' => 'student',
                'status' => 'active',
                'phone_number' => $validated['phone_number'] ?? null,
            ]);

            $student = Student::create([
                'user_id' => $user->id,
                'student_id' => $validated['student_id'],
            ]);
        
            return [$user, $student];
        });

        event(new StudentCreated($user, $student));

        return redirect()->route('faculty.students')->with('success', 'Student account created successfully.');
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
            'phone_number' => $validated['phone_number'] ?? $user->phone_number,
            'status' => $validated['status'],
        ];

        if (!empty($validated['password'])) {
            $updateData['password'] = Hash::make($validated['password']);
        }

        $user->update($updateData);

        return redirect()->route('faculty.students')->with('success', 'Student updated successfully.');
    }

    public function bulkImportStudents(Request $request)
    {
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

        foreach (array_slice($allRows, 1) as $row) {
            $rowNumber++;

            // Map values by header name
            $data = array_combine($rawHeader, array_map(fn($v) => trim((string) ($v ?? '')), $row));

            $studentId  = $data['student_id']   ?? '';
            $firstName  = $data['first_name']   ?? '';
            $lastName   = $data['last_name']    ?? '';
            $middleName = $data['middle_name']  ?? '';
            $emailRaw   = $data['email']        ?? '';
            $phone      = $data['phone_number'] ?? '';

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
            $defaultPassword = Hash::make('hms@' . $studentId);

            try {
                [$user, $student] = DB::transaction(function () use (
                    $studentId, $firstName, $middleName, $lastName,
                    $email, $phone, $fullName, $defaultPassword
                ) {
                    $user = User::create([
                        'name'         => $fullName,
                        'first_name'   => $firstName,
                        'middle_name'  => $middleName !== '' ? $middleName : null,
                        'last_name'    => $lastName,
                        'email'        => $email,
                        'password'     => $defaultPassword,
                        'role'         => 'student',
                        'status'       => 'active',
                        'phone_number' => $phone !== '' ? $phone : null,
                    ]);

                    $student = Student::create([
                        'user_id'    => $user->id,
                        'student_id' => $studentId,
                    ]);

                    return [$user, $student];
                });

                event(new StudentCreated($user, $student));

                $results[] = [
                    'row'        => $rowNumber,
                    'status'     => 'success',
                    'name'       => trim("{$lastName}, {$firstName}"),
                    'student_id' => $studentId,
                    'email'      => $email,
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

        return response()->json([
            'message' => "{$created} student(s) imported successfully. {$failed} failed.",
            'created' => $created,
            'failed'  => $failed,
            'results' => $results,
        ]);
    }

    public function role()
    {
        $facultyId = auth()->user()?->faculty?->id;
        if (!$facultyId) {
            return view('faculty.pagerole', [
                'students'    => collect(),
                'groups'      => collect(),
                'allStudents' => collect(),
                'tasksByRole' => collect(),
                'taskCounts'  => [],
                'roles'       => [],
            ]);
        }

        $assignedStudentIds = StudentGroup::pluck('student_id')->unique();
        $students = Student::with('user')
            ->whereNotIn('id', $assignedStudentIds)
            ->latest()
            ->get();

        // All students (for update tab — includes already-assigned ones)
        $allStudents = Student::with('user')->latest()->get();

        $groups = StudentGroup::with('student.user', 'roles')
            ->latest()
            ->get()
            ->groupBy('group_name');

        // Debug: Log groups count
        \Log::info('Faculty Role - Groups Count: ' . count($groups));
        foreach ($groups as $name => $members) {
            \Log::info('  Group: ' . $name . ' with ' . $members->count() . ' members');
        }

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

        return view('faculty.pagerole', compact('students', 'allStudents', 'groups', 'rolesMeta', 'tasksByRole', 'taskCounts'));
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
            'member_roles'  => ['required', 'array'],
        ]);

        $allowedRoles = ['front_desk', 'restaurant_management', 'room_management', 'maintenance', 'housekeeping'];
        $memberIds    = array_unique($validated['members']);

        foreach ($memberIds as $studentId) {
            $roles = $validated['member_roles'][$studentId] ?? [];
            if (!is_array($roles)) {
                $roles = [$roles];
            }

            if (empty($roles)) {
                return back()->withErrors(['member_roles' => 'Please select at least one role for each selected member.'])->withInput();
            }

            foreach ($roles as $role) {
                if (!in_array($role, $allowedRoles, true)) {
                    return back()->withErrors(['member_roles' => 'Please select valid roles for each selected member.'])->withInput();
                }
            }
        }

        // Delete old rows for this group/faculty then re-insert
        $oldGroups = StudentGroup::where('faculty_id', $facultyId)
            ->where('group_name', $groupName)
            ->get();

        foreach ($oldGroups as $oldGroup) {
            StudentGroupRole::where('student_group_id', $oldGroup->id)->delete();
            $oldGroup->delete();
        }

        foreach ($memberIds as $studentId) {
            $studentGroup = StudentGroup::create([
                'group_name' => $validated['group_name'],
                'faculty_id' => $facultyId,
                'student_id' => $studentId,
            ]);

            $roles = $validated['member_roles'][$studentId] ?? [];
            if (!is_array($roles)) {
                $roles = [$roles];
            }

            foreach ($roles as $role) {
                StudentGroupRole::create([
                    'student_group_id' => $studentGroup->id,
                    'role' => $role,
                ]);
            }
        }

        return redirect()->route('faculty.role', ['tab' => 'teams'])
            ->with('success', 'Team updated successfully.');
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
                        Task::create([
                            'faculty_id' => $facultyId,
                            'role' => $role,
                            'title' => $title,
                            'description' => $description,
                            'priority' => $priority,
                            'due_date' => $validated['due_date'] ?? null,
                            'status' => 'active',
                        ]);
                        $tasksCreated++;
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
        return view('faculty.results');
    }

    public function reports()
    {
        return view('faculty.reports');
    }
}