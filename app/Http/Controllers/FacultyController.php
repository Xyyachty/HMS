<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use App\Events\StudentCreated;
use App\Models\ActivityLog;
use App\Models\FacultyClass;
use App\Models\Group;
use App\Models\HotelConcept;
use App\Models\Student;
use App\Models\StudentGroup;
use App\Models\StudentGroupRole;
use App\Models\Task;
use App\Models\TeamRoleTemplateVersion;
use App\Models\User;
use App\Models\UserInformation;
use App\Support\HotelConceptDesk;
use App\Support\Notifier;
use App\Support\StudentWelcomeMailer;
use Illuminate\Validation\Rule;
use PhpOffice\PhpSpreadsheet\IOFactory;
class FacultyController extends Controller
{
    public function dashboard()
    {
        $facultyId = auth()->user()?->faculty?->user_information_id;

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

        $roleLabels = [
            'front_desk' => 'Front Desk',
            'restaurant_management' => 'Restaurant',
            'room_management' => 'Room Mgmt',
            'maintenance' => 'Maintenance',
            'housekeeping' => 'Housekeeping',
        ];

        $recentActivity = $facultyId
            ? Task::with(['student.user', 'assignedTo'])
                ->where('faculty_id', $facultyId)
                ->orderByDesc('updated_at')
                ->take(8)
                ->get()
            : collect();

        return view('faculty.dashboard', compact(
            'totalStudents',
            'totalTeams',
            'assignedTasks',
            'recentActivity',
            'roleLabels'
        ));
    }

    public function storeGroup(Request $request)
    {
        $facultyId = auth()->user()?->faculty?->user_information_id;
        if (!$facultyId) {
            return back()->withErrors(['group_name' => 'Faculty account not found for the current user.'])->withInput();
        }

        $formSource = $request->input('_form_source', 'create_team');

        if ($formSource === 'create_teams_bulk') {
            return $this->storeBulkGroups($request, $facultyId);
        }

        $validated = $request->validate([
            'group_name' => ['required', 'string', 'max:255'],
            'members' => ['required', 'array', 'min:1', 'max:4'],
            'members.*' => ['integer', Rule::exists('user_information', 'user_information_id')->where('user_type', UserInformation::TYPE_STUDENT)],
            'member_roles' => ['nullable', 'array'],
        ]);

        $memberIds = array_values(array_unique(array_map('intval', $validated['members'])));
        if (count($memberIds) > 4) {
            return back()->withErrors(['members' => 'A team cannot have more than 4 members.'])->withInput();
        }

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
                ->where('faculty_id', $facultyId)
                ->pluck('student_id')
                ->map(fn ($id) => (int) $id)
                ->all();

            $overlap = array_intersect($memberIds, $existingMembers);
            if (!empty($overlap)) {
                return back()->withErrors(['members' => 'Some selected students are already in this team.'])->withInput();
            }

            if (count($existingMembers) + count($memberIds) > 4) {
                $slotsLeft = max(0, 4 - count($existingMembers));
                return back()->withErrors([
                    'members' => "This team already has " . count($existingMembers) . " member(s). You can add at most {$slotsLeft} more (max 4 per team).",
                ])->withInput();
            }
        } elseif ($formSource === 'create_team') {
            // Single team create: max 4 members
            $nameTaken = StudentGroup::where('group_name', $validated['group_name'])
                ->where('faculty_id', $facultyId)
                ->exists();
            if ($nameTaken) {
                return back()->withErrors(['group_name' => 'A team with this name already exists.'])->withInput();
            }
        }

        $allowedRoles = $this->teamRoleKeys();
        $roleResult = $this->resolveMemberRoles(
            $memberIds,
            $validated['member_roles'] ?? [],
            $allowedRoles
        );

        if ($roleResult['error'] !== null) {
            return back()->withErrors(['member_roles' => $roleResult['error']])->withInput();
        }
        $rolesByMember = $roleResult['roles'];

        $group = Group::firstOrCreate([
            'group_name' => $validated['group_name'],
            'faculty_id' => $facultyId,
        ]);

        foreach ($memberIds as $studentId) {
            $roles = $rolesByMember[$studentId];

            $studentGroup = StudentGroup::create([
                'group_name' => $validated['group_name'],
                'faculty_id' => $facultyId,
                'group_id' => $group->group_id,
                'student_id' => $studentId,
                'role' => $roles[0], // legacy column; real roles stored in student_group_roles
            ]);

            foreach ($roles as $role) {
                StudentGroupRole::create([
                    'student_group_id' => $studentGroup->student_group_id,
                    'role' => $role,
                ]);
            }
        }

        // Every team starts with the same first task, so it is not on the faculty
        // checklist — it is assigned the moment the team exists.
        HotelConceptDesk::ensureTasksForTeam($validated['group_name'], (int) $facultyId);

        $memberCount = count($memberIds);
        $assignedRoles = collect($rolesByMember)->flatten()->unique()->values()->all();

        if ($formSource === 'insert_student') {
            ActivityLog::recordFor(
                ActivityLog::STUDENT_ASSIGNED,
                'Assigned ' . $memberCount . ' student(s) to team "' . $validated['group_name'] . '".'
            );

            Notifier::teamMembersAdded(
                auth()->user(),
                $validated['group_name'],
                $facultyId,
                $memberIds
            );
        } else {
            ActivityLog::recordFor(
                ActivityLog::TEAM_CREATED,
                'Created team "' . $validated['group_name'] . '" with ' . $memberCount . ' member(s).'
            );

            Notifier::teamCreated(
                auth()->user(),
                $validated['group_name'],
                $facultyId,
                $memberIds
            );
        }

        ActivityLog::recordFor(
            ActivityLog::ROLE_ASSIGNED,
            'Assigned role(s) ' . implode(', ', $assignedRoles) . ' in team "' . $validated['group_name'] . '".'
        );

        return redirect()->route('faculty.role', array_filter([
            'tab' => 'teams',
            'class' => $request->input('class_letter') ?: null,
        ]))->with('success', $formSource === 'insert_student'
            ? 'Students added to the team.'
            : 'Team created successfully.');
    }

    /**
     * Create multiple teams and assign students in one request.
     */
    private function storeBulkGroups(Request $request, int $facultyId)
    {
        $validated = $request->validate([
            'teams' => ['required', 'array', 'min:1'],
            'teams.*.group_name' => ['required', 'string', 'max:255'],
            'teams.*.members' => ['required', 'array', 'size:4'],
            'teams.*.members.*' => ['integer', Rule::exists('user_information', 'user_information_id')->where('user_type', UserInformation::TYPE_STUDENT)],
            'teams.*.member_roles' => ['nullable', 'array'],
        ]);

        $allowedRoles = $this->teamRoleKeys();
        $existingNames = StudentGroup::where('faculty_id', $facultyId)
            ->pluck('group_name')
            ->map(fn ($n) => strtolower(trim((string) $n)))
            ->unique()
            ->all();

        $assignedStudentIds = StudentGroup::where('faculty_id', $facultyId)
            ->pluck('student_id')
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->all();

        $ownedStudentIds = Student::where('faculty_id', $facultyId)
            ->pluck('user_information_id')
            ->map(fn ($id) => (int) $id)
            ->all();

        $seenNames = [];
        $seenMembers = [];
        $normalizedTeams = [];

        foreach ($validated['teams'] as $index => $team) {
            $name = trim((string) $team['group_name']);
            $nameKey = strtolower($name);
            $row = $index + 1;

            if ($name === '') {
                return back()->withErrors(["teams.{$index}.group_name" => "Team #{$row} needs a name."])->withInput();
            }

            if (in_array($nameKey, $seenNames, true) || in_array($nameKey, $existingNames, true)) {
                return back()->withErrors(["teams.{$index}.group_name" => "Team name \"{$name}\" is already used."])->withInput();
            }

            $memberIds = array_values(array_unique(array_map('intval', $team['members'] ?? [])));
            if (count($memberIds) !== 4) {
                return back()->withErrors([
                    "teams.{$index}.members" => "Team \"{$name}\" must have exactly 4 members (got " . count($memberIds) . ").",
                ])->withInput();
            }

            foreach ($memberIds as $studentId) {
                if (!in_array($studentId, $ownedStudentIds, true)) {
                    return back()->withErrors(["teams.{$index}.members" => "Team \"{$name}\" includes a student that is not yours."])->withInput();
                }
                if (in_array($studentId, $assignedStudentIds, true)) {
                    return back()->withErrors(["teams.{$index}.members" => "Team \"{$name}\" includes a student already on a team."])->withInput();
                }
                if (in_array($studentId, $seenMembers, true)) {
                    return back()->withErrors(["teams.{$index}.members" => "A student was assigned to more than one team."])->withInput();
                }
                $seenMembers[] = $studentId;
            }

            $roleResult = $this->resolveMemberRoles(
                $memberIds,
                $team['member_roles'] ?? [],
                $allowedRoles
            );

            if ($roleResult['error'] !== null) {
                return back()->withErrors(["teams.{$index}.member_roles" => "Team \"{$name}\": {$roleResult['error']}"])->withInput();
            }

            $seenNames[] = $nameKey;
            $normalizedTeams[] = [
                'group_name' => $name,
                'members' => $memberIds,
                'roles_by_member' => $roleResult['roles'],
            ];
        }

        DB::transaction(function () use ($normalizedTeams, $facultyId) {
            foreach ($normalizedTeams as $team) {
                $group = Group::firstOrCreate([
                    'group_name' => $team['group_name'],
                    'faculty_id' => $facultyId,
                ]);

                foreach ($team['members'] as $studentId) {
                    $roles = $team['roles_by_member'][$studentId];

                    $studentGroup = StudentGroup::create([
                        'group_name' => $team['group_name'],
                        'faculty_id' => $facultyId,
                        'group_id' => $group->group_id,
                        'student_id' => $studentId,
                        'role' => $roles[0],
                    ]);

                    foreach ($roles as $role) {
                        StudentGroupRole::create([
                            'student_group_id' => $studentGroup->student_group_id,
                            'role' => $role,
                        ]);
                    }
                }
            }
        });

        $count = count($normalizedTeams);

        ActivityLog::recordFor(
            ActivityLog::TEAM_CREATED,
            'Created ' . $count . ' team(s): '
                . implode(', ', array_column($normalizedTeams, 'group_name')) . '.'
        );

        foreach ($normalizedTeams as $team) {
            Notifier::teamCreated(auth()->user(), $team['group_name'], $facultyId, $team['members']);
        }

        return redirect()->route('faculty.role', array_filter([
            'tab' => 'teams',
            'class' => $request->input('class_letter') ?: null,
        ]))->with('success', $count === 1
            ? 'Team created successfully.'
            : "{$count} teams created successfully.");
    }

    /** @return array<string, string> role key => display label */
    private function teamRoleLabels(): array
    {
        return [
            'front_desk'            => 'Front Desk',
            'restaurant_management' => 'Restaurant Management',
            'room_management'       => 'Room Management',
            'maintenance'           => 'Maintenance',
            'housekeeping'          => 'Housekeeping Services',
        ];
    }

    /** @return list<string> */
    private function teamRoleKeys(): array
    {
        return array_keys($this->teamRoleLabels());
    }

    /**
     * Build per-member roles for one team. A role may be held by at most one member —
     * a duplicate pick returns a message naming the role. Missing selections still get
     * an auto-assigned default, but only from roles the team hasn't used yet (always
     * possible: teams top out at 4 members against 5 roles).
     *
     * @param  list<int|string>  $memberIds
     * @param  array<int|string, mixed>  $memberRoles
     * @param  list<string>  $allowedRoles
     * @return array{roles: array<int, list<string>>|null, error: string|null}
     */
    private function resolveMemberRoles(array $memberIds, array $memberRoles, array $allowedRoles): array
    {
        $labels = $this->teamRoleLabels();
        $resolved = [];
        $usedRoles = []; // role => true, claimed by an earlier member in this same save
        $index = 0;

        foreach ($memberIds as $studentId) {
            $roles = $memberRoles[$studentId] ?? [];
            if (!is_array($roles)) {
                $roles = [$roles];
            }

            $roles = array_values(array_unique(array_filter($roles, fn ($r) => $r !== null && $r !== '')));

            foreach ($roles as $role) {
                if (!in_array($role, $allowedRoles, true)) {
                    return ['roles' => null, 'error' => 'Please select valid roles for each selected member.'];
                }
                if (isset($usedRoles[$role])) {
                    $label = $labels[$role] ?? $role;
                    return ['roles' => null, 'error' => "\"{$label}\" is already assigned to another member on this team."];
                }
            }

            if ($roles === []) {
                $free = array_values(array_diff($allowedRoles, array_keys($usedRoles)));
                // Always non-empty: teams top out at 4 members against 5 roles.
                $roles = [$free[$index % count($free)]];
            }

            foreach ($roles as $role) {
                $usedRoles[$role] = true;
            }

            $resolved[(int) $studentId] = $roles;
            $index++;
        }

        return ['roles' => $resolved, 'error' => null];
    }

    public function students()
    {
        $facultyId = auth()->user()?->faculty?->user_information_id;

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
            ->when($activeClass, fn ($q) => $q->where('faculty_class_id', $activeClass->faculty_class_id))
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
        $facultyId = auth()->user()?->faculty?->user_information_id;
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

            // Same wire shape as the StudentCreated broadcast the roster also consumes:
            // "student_id" is the school number, now stored in student_number.
            return [
                'user_id' => $user?->user_id,
                'student_id' => $student->student_number,
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
        $facultyId = auth()->user()?->faculty?->user_information_id;
        if (!$facultyId) {
            return back()->withErrors(['error' => 'Faculty account not found.'])->withInput();
        }

        $validated = $request->validate([
            // Request field keeps its name — it is what the form and the bulk-upload
            // spreadsheet send. The column it validates against is student_number.
            'student_id' => ['required', 'string', 'max:50', 'unique:user_information,student_number'],
            'first_name' => ['required', 'string', 'max:255'],
            'middle_name' => ['nullable', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'max:255'],
            'phone_number' => ['nullable', 'string', 'max:30'],
        ]);

        // Generated rather than typed, same as the bulk upload. The only place it is
        // ever readable is the student's own welcome email, so no faculty member holds
        // a password that opens a student's account and no two students share one.
        $plainPassword = StudentWelcomeMailer::generatePassword();

        $email = strtolower(trim($validated['email']));

        // No @hms.edu fallback on this form. The password now exists only in the
        // welcome email, so an address that cannot receive mail leaves a student
        // with an account and no way into it. Say so before the row is created.
        if (!StudentWelcomeMailer::isDeliverable($email)) {
            return back()->withErrors([
                'email' => 'Enter a real email address the student can open — their password is only sent there.',
            ])->withInput();
        }

        if (User::whereEmail($email)->exists()) {
            return back()->withErrors(['email' => 'Email already exists.'])->withInput();
        }

        $fullName = trim(implode(' ', array_filter([
            $validated['first_name'],
            $validated['middle_name'] ?? null,
            $validated['last_name'],
        ])));

        // Which class was accepting enrollments before this student took a seat —
        // if it differs afterwards, seating them is what opened the next one.
        $openClassIdBefore = FacultyClass::where('faculty_id', $facultyId)
            ->where('status', 'open')
            ->orderBy('sort_order')
            ->value('faculty_class_id');

        [$user, $student, $class] = DB::transaction(function () use ($validated, $email, $fullName, $facultyId, $plainPassword) {
            $class = FacultyClass::claimSeat($facultyId);

            $user = User::create([
                'name' => $fullName,
                'first_name' => $validated['first_name'],
                'middle_name' => User::cleanOptional($validated['middle_name'] ?? null),
                'last_name' => $validated['last_name'],
                'email' => $email,
                'password' => Hash::make($plainPassword),
                'role' => 'student',
                'status' => 'active',
                'phone_number' => User::cleanOptional($validated['phone_number'] ?? null),
                'email_verified_at' => now(),
            ]);

            $student = Student::create([
                'user_id' => $user->user_id,
                'faculty_id' => $facultyId,
                'faculty_class_id' => $class->faculty_class_id,
                'student_number' => $validated['student_id'],
            ]);

            $class->syncCapacity();

            return [$user, $student, $class->fresh()];
        });

        event(new StudentCreated($user, $student));

        ActivityLog::recordFor(
            ActivityLog::ACCOUNT_CREATED,
            'Created student account ' . $fullName . ' (' . $validated['student_id'] . ') in ' . ($class->name ?? 'their block') . '.'
        );

        Notifier::studentAdded(auth()->user(), $user, $fullName, $class, $facultyId);
        $this->notifyIfClassOpened($facultyId, $openClassIdBefore);

        // Only now that the account exists. This is the last point the generated
        // password is readable — the column holds a hash from here on, so a student
        // whose email never arrives has to reset rather than be told.
        $mailResult = StudentWelcomeMailer::send(
            $user,
            $plainPassword,
            $class->name ?? null,
            $validated['student_id']
        );

        $message = 'Student account created successfully and added to ' . ($class->name ?? 'their block') . '.';
        if ($class->status === 'closed') {
            $message .= ' ' . $class->name . ' is now full. A new block tab was opened.';
        }

        // When the email did not go out, this banner is the only place the password
        // is ever shown — the account is otherwise created with a password nobody
        // knows. Shown once, on the faculty's own screen, and never stored in clear.
        $message .= $mailResult['sent']
            ? ' A welcome email with the sign-in details was sent to ' . $user->email . '.'
            : ' Note: ' . $mailResult['reason'] . '. Their password is ' . $plainPassword
                . ' — write it down now and give it to them, it is not shown again.';

        return redirect()
            ->route('faculty.students', ['class' => $class->letter])
            ->with('success', $message)
            ->with('success_title', 'Student Created');
    }

    /**
     * Announce a newly opened class tab.
     *
     * Enrollment is the only thing that fills a class, so this is called right
     * after a seat is claimed: if the faculty's open class is no longer the one
     * that was open beforehand, capacity rolled over into the next letter.
     */
    private function notifyIfClassOpened(int $facultyId, $openClassIdBefore): void
    {
        $openNow = FacultyClass::where('faculty_id', $facultyId)
            ->where('status', 'open')
            ->orderBy('sort_order')
            ->first();

        if (!$openNow || (int) $openNow->faculty_class_id === (int) $openClassIdBefore) {
            return;
        }

        Notifier::classOpened(auth()->user(), $facultyId, $openNow);
    }

    public function updateStudent(Request $request, $userId)
    {
        $validated = $request->validate([
            'phone_number' => ['nullable', 'string', 'max:30'],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
            'status' => ['required', 'in:active,inactive'],
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

        ActivityLog::recordFor(
            ActivityLog::ACCOUNT_UPDATED,
            'Updated student account ' . ($user->name ?? $user->email) . ' — status set to ' . $validated['status'] . '.'
        );

        return redirect()->route('faculty.students', ['class' => request('class')])
            ->with('success', 'Student updated successfully.')
            ->with('success_title', 'Student Updated');
    }

    public function bulkImportStudents(Request $request)
    {
        $facultyId = auth()->user()?->faculty?->user_information_id;
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

        if (empty($allRows)) {
            return response()->json(['message' => 'The file is empty or has no data rows.'], 422);
        }

        // Takes both the template this app hands out and the registrar's official class
        // list, which buries its header under a letterhead and writes the name as one
        // "LAST, FIRST M." cell. See StudentRosterSheet.
        $sheetData = \App\Support\StudentRosterSheet::parse($allRows);

        if ($sheetData['header_row'] === null) {
            return response()->json([
                'message' => 'Could not find the column headings. The sheet needs a student number column and either a name column or first name and last name columns.'
            ], 422);
        }

        if (empty($sheetData['students'])) {
            return response()->json(['message' => 'The file is empty or has no data rows.'], 422);
        }

        $results   = [];
        $classesOpened = [];
        $lastClassLetter = null;

        // Each welcome email is a round trip to Gmail, so a full class costs a minute
        // or two. The default 30 seconds would cut the import in half — some students
        // created and emailed, the rest silently not.
        @set_time_limit(0);
        @ini_set('max_execution_time', '0');

        foreach ($sheetData['students'] as $entry) {
            // The row number as it appears in Excel, so a failure can be found by eye.
            $rowNumber = $entry['row'];

            $studentId  = User::cleanOptional($entry['student_number']);
            $firstName  = User::cleanOptional($entry['first_name']);
            $lastName   = User::cleanOptional($entry['last_name']);
            $middleName = User::cleanOptional($entry['middle_name']);
            $emailRaw   = User::cleanOptional($entry['email']);
            $phone      = User::cleanOptional($entry['phone_number']);

            // Basic validation
            $errors = [];
            if ($studentId === '') $errors[] = 'student number is required';
            if ($firstName  === '') $errors[] = 'first name is required';
            if ($lastName   === '') $errors[] = 'last name is required';
            // Registrar lists carry "N/A" where a student never gave an address; the
            // parser hands that over as blank rather than inventing one.
            if ($emailRaw   === '') $errors[] = 'no email in the file — add it and re-upload, or add this student manually';

            $email = str_contains($emailRaw, '@')
                ? strtolower($emailRaw)
                : strtolower($emailRaw . '@hms.edu');

            if ($emailRaw !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $errors[] = 'invalid email format';
            }

            if ($studentId !== '' && Student::where('student_number', $studentId)->exists()) {
                $errors[] = "student_id '{$studentId}' already exists";
            }

            if ($email !== '' && filter_var($email, FILTER_VALIDATE_EMAIL) && User::whereEmail($email)->exists()) {
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

            $fullName = trim(implode(' ', array_filter([$firstName, $middleName, $lastName])));

            // One password per student rather than a shared default. A shared one
            // was tried and reverted: a password every student knows is one every
            // student can use on any classmate's account. It reaches the student
            // through the welcome email below and is readable nowhere else.
            $plainPassword   = StudentWelcomeMailer::generatePassword();
            $defaultPassword = Hash::make($plainPassword);

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
                        'user_id'          => $user->user_id,
                        'faculty_id'       => $facultyId,
                        'faculty_class_id' => $class->faculty_class_id,
                        'student_number'   => $studentId,
                    ]);

                    $class->syncCapacity();
                    $class->refresh();

                    return [$user, $student, $class->letter];
                });

                event(new StudentCreated($user, $student));

                // Only the student's own welcome here — faculty and dean get one
                // summary after the loop instead of a row per imported student.
                Notifier::studentWelcomed(auth()->user(), $user, 'Block ' . $classLetter);

                if ($lastClassLetter !== null && $classLetter !== $lastClassLetter) {
                    $classesOpened[] = $classLetter;
                }
                $lastClassLetter = $classLetter;

                // Sent only on this path, where the account is known to exist. A row
                // that failed above never reaches here, so nobody is told an account
                // is ready that was not created.
                $mailResult = StudentWelcomeMailer::send(
                    $user,
                    $plainPassword,
                    'Block ' . $classLetter,
                    $studentId
                );

                $results[] = [
                    'row'        => $rowNumber,
                    'status'     => 'success',
                    'name'       => trim("{$lastName}, {$firstName}"),
                    'student_id' => $studentId,
                    'email'      => $email,
                    'class'      => $classLetter,
                    'emailed'    => $mailResult['sent'],
                    'reason'     => $mailResult['sent'] ? '' : $mailResult['reason'],
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
        $emailed = collect($results)->where('status', 'success')->where('emailed', true)->count();
        $notEmailed = $created - $emailed;
        $openClass = FacultyClass::where('faculty_id', $facultyId)->where('status', 'open')->orderBy('sort_order')->first();

        $message = "{$created} student(s) imported successfully. {$failed} failed.";
        if ($created > 0) {
            $message .= " {$emailed} welcome email(s) sent"
                . ($notEmailed > 0 ? ", {$notEmailed} could not be emailed" : '') . '.';
        }
        if (!empty($classesOpened)) {
            $message .= ' New block tab(s) opened: Block ' . implode(', Block ', array_unique($classesOpened)) . '.';
        }

        if ($created > 0) {
            ActivityLog::recordFor(
                ActivityLog::ACCOUNT_CREATED,
                'Bulk imported ' . $created . ' student account(s)'
                    . ($failed > 0 ? ', ' . $failed . ' row(s) failed' : '') . '.'
            );

            Notifier::studentsImported(auth()->user(), $facultyId, $created);

            foreach (array_unique($classesOpened) as $letter) {
                $opened = FacultyClass::where('faculty_id', $facultyId)
                    ->where('letter', $letter)
                    ->first();
                if ($opened) {
                    Notifier::classOpened(auth()->user(), $facultyId, $opened);
                }
            }
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
        $facultyId = auth()->user()?->faculty?->user_information_id;
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
                'taskChecklist'   => [],
                'roles'           => [],
                'teamActivityByGroup' => [],
                'conceptsByGroup' => collect(),
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
            ->whereNotIn('user_information_id', $assignedStudentIds)
            ->when($activeClass, fn ($q) => $q->where('faculty_class_id', $activeClass->faculty_class_id))
            ->latest()
            ->get();

        // Update Team modal still needs all faculty students (then filtered to members in JS)
        $allStudents = Student::with(['user', 'facultyClass'])
            ->where('faculty_id', $facultyId)
            ->orderByDesc('user_information_id')
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
            $teamCountsByClass[$class->faculty_class_id] = 0;
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

            return ($teamClassIdByName[$groupName] ?? null) === $activeClass->faculty_class_id;
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
            ->orderByPriority()
            ->get()
            ->groupBy('role');

        $taskCounts = [];
        foreach (array_keys($rolesMeta) as $roleKey) {
            $taskCounts[$roleKey] = $tasksByRole->get($roleKey, collect())->count();
        }

        // What faculty can tick to assign. Named apart from $tasksByRole above,
        // which is the tasks that already exist — the view used to redefine that
        // one for the checklist and lose the real rows for the rest of the page.
        $taskChecklist = \App\Support\TaskChecklist::all();

        // How many of each team's members hold each role, so the Create Task wizard
        // can say "2 members" or "nobody holds this" once a team is picked. Read off
        // the roster already in memory rather than a query per team.
        $teamRoleCounts = [];
        foreach ($allGroups as $groupName => $members) {
            $counts = array_fill_keys(array_keys($rolesMeta), 0);

            foreach ($members as $member) {
                foreach ($member->roles as $memberRole) {
                    if (array_key_exists($memberRole->role, $counts)) {
                        $counts[$memberRole->role]++;
                    }
                }
            }

            $teamRoleCounts[$groupName] = $counts;
        }

        $roleLabels = [
            'front_desk'            => 'Front Desk',
            'restaurant_management' => 'Restaurant',
            'room_management'       => 'Rooms',
            'maintenance'           => 'Maintenance',
            'housekeeping'          => 'Housekeeping',
        ];

        $allTasks = Task::with('assignedTo')
            ->where('faculty_id', $facultyId)
            // The hotel concept heads each team's list — it is their first task.
            ->conceptFirst()
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
                    // The hotel concept has its own "Hotel Concept" tab in this same
                    // modal — it should not also clutter Team Task Activity.
                    if ($task->is_hotel_concept) {
                        return false;
                    }

                    // A claimed row belongs to exactly one student, so show it only to
                    // that student's team. Falling through to the role match here listed
                    // every team's rows under every team holding the same role.
                    if ($task->student_id) {
                        return in_array((int) $task->student_id, $memberStudentIds, true);
                    }

                    // Unclaimed pool rows still show for any team holding the role.
                    return in_array($task->role, $memberRoles, true);
                })
                ->take(100)
                ->values()
                ->map(function (Task $task) use ($roleLabels) {
                    // Tasks fan out one row per member, so name the student the row
                    // belongs to — otherwise identical titles are indistinguishable.
                    $u = $task->assignedTo;
                    $studentName = trim(implode(' ', array_filter([$u?->last_name, $u?->first_name])));
                    $studentName = $studentName !== '' ? $studentName : ($u?->name ?? null);

                    return [
                        'id' => $task->task_id,
                        'title' => $task->title,
                        'description' => $task->description,
                        'role' => $task->role,
                        'role_label' => $roleLabels[$task->role] ?? $task->role,
                        'priority' => strtolower($task->priority ?? 'medium'),
                        'status' => $task->status,
                        // The concept task is not deletable and reviews differently.
                        'is_hotel_concept' => $task->is_hotel_concept,
                        'has_feedback' => filled($task->feedback),
                        'student_name' => $studentName,
                        'submitted_at' => $task->status === 'archived'
                            ? optional($task->updated_at)->format('M d, Y')
                            : null,
                        'due_date' => optional($task->due_date)->format('M d, Y g:i A'),
                        'updated_at' => optional($task->updated_at)->format('M d, Y'),
                    ];
                })
                ->all();
        }

        // The hotel concepts, so the teams list names what each team proposed. Grouped
        // rather than keyed: a team has two, and keyBy would silently keep one. The
        // full text and the edit histories stay in the Team Details modal. Once a
        // team has decided, only the winner is listed — the same rule payload()
        // applies, kept in step so the table and the modal never disagree.
        $conceptsByGroup = \App\Models\HotelConcept::where('faculty_id', $facultyId)
            ->orderBy('slot')
            ->get()
            ->groupBy('group_name')
            ->map(fn ($concepts) => \App\Support\HotelConceptDesk::visibleConcepts($concepts));

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
            'taskChecklist',
            'teamRoleCounts',
            'teamActivityByGroup',
            'conceptsByGroup'
        ));
    }

    public function updateGroup(Request $request, $groupName)
    {
        $facultyId = auth()->user()?->faculty?->user_information_id;
        if (!$facultyId) {
            return back()->withErrors(['group_name' => 'Faculty account not found.'])->withInput();
        }

        $validated = $request->validate([
            'group_name'    => ['required', 'string', 'max:255'],
            'members'       => ['required', 'array', 'min:1', 'max:4'],
            'members.*'     => ['integer', Rule::exists('user_information', 'user_information_id')->where('user_type', UserInformation::TYPE_STUDENT)],
            'member_roles'  => ['nullable', 'array'],
        ]);

        $allowedRoles = $this->teamRoleKeys();
        $memberIds    = array_values(array_unique($validated['members']));
        if (count($memberIds) > 4) {
            return back()->withErrors(['members' => 'A team cannot have more than 4 members.'])->withInput();
        }
        $roleResult = $this->resolveMemberRoles(
            $memberIds,
            $validated['member_roles'] ?? [],
            $allowedRoles
        );

        if ($roleResult['error'] !== null) {
            return back()->withErrors(['member_roles' => $roleResult['error']])->withInput();
        }
        $rolesByMember = $roleResult['roles'];

        // Rename the canonical group in place (id-stable) — the delete+recreate below
        // only handles student_groups membership rows, so without this the other 8
        // group_name-bearing tables would keep the stale old name forever.
        $group = Group::updateOrCreate(
            ['faculty_id' => $facultyId, 'group_name' => $groupName],
            ['group_name' => $validated['group_name']]
        );

        // Delete old rows for this group/faculty then re-insert
        $oldGroups = StudentGroup::where('faculty_id', $facultyId)
            ->where('group_name', $groupName)
            ->get();

        foreach ($oldGroups as $oldGroup) {
            StudentGroupRole::where('student_group_id', $oldGroup->student_group_id)->delete();
            $oldGroup->delete();
        }

        // If a student is moved onto this team, drop them from any other team first
        $otherMemberships = StudentGroup::where('faculty_id', $facultyId)
            ->whereIn('student_id', $memberIds)
            ->where('group_name', '!=', $validated['group_name'])
            ->get();

        foreach ($otherMemberships as $other) {
            StudentGroupRole::where('student_group_id', $other->student_group_id)->delete();
            $other->delete();
        }

        foreach ($memberIds as $studentId) {
            $roles = $rolesByMember[$studentId];

            $studentGroup = StudentGroup::create([
                'group_name' => $validated['group_name'],
                'faculty_id' => $facultyId,
                'group_id' => $group->group_id,
                'student_id' => $studentId,
                'role' => $roles[0], // legacy column; real roles stored in student_group_roles
            ]);

            foreach ($roles as $role) {
                StudentGroupRole::create([
                    'student_group_id' => $studentGroup->student_group_id,
                    'role' => $role,
                ]);
            }
        }

        // A reshuffle can hand Front Desk to somebody new, and they need the task.
        // Existing rows are left alone, so a submitted concept stays submitted.
        HotelConceptDesk::ensureTasksForTeam($validated['group_name'], (int) $facultyId);

        ActivityLog::recordFor(
            ActivityLog::TEAM_UPDATED,
            'Updated team "' . $groupName . '"'
                . ($groupName !== $validated['group_name'] ? ' (renamed to "' . $validated['group_name'] . '")' : '')
                . ' — ' . count($memberIds) . ' member(s) and their roles reassigned.'
        );

        return redirect()->route('faculty.role', array_filter([
            'tab' => 'teams',
            'class' => $request->input('class_letter') ?: null,
        ]))->with('success', 'Team updated successfully.');
    }

    public function tasks(Request $request)
    {
        $facultyId = auth()->user()?->faculty?->user_information_id;
        if (!$facultyId) {
            return view('faculty.tasks', [
                'tasksByRole' => collect(),
                'taskCounts' => [],
                'teamNames' => [],
                'activeTeam' => null,
            ]);
        }

        $roles = ['front_desk', 'restaurant_management', 'room_management', 'maintenance', 'housekeeping'];

        // Every team this faculty owns, so the filter lists teams that hold no task
        // yet rather than only the ones that happen to appear in the rows below.
        $teamNames = StudentGroup::where('faculty_id', $facultyId)
            ->orderBy('group_name')
            ->pluck('group_name')
            ->unique()
            ->values()
            ->all();

        $requestedTeam = $request->query('team');
        $activeTeam = in_array($requestedTeam, $teamNames, true) ? $requestedTeam : null;

        $tasksByRole = Task::where('faculty_id', $facultyId)
            ->where('status', 'active')
            ->when($activeTeam, fn ($q) => $q->where('group_name', $activeTeam))
            ->conceptFirst()
            ->orderBy('due_date')
            ->orderByPriority()
            ->get()
            ->groupBy('role');

        $taskCounts = [];
        foreach ($roles as $role) {
            $taskCounts[$role] = $tasksByRole->get($role, collect())->count();
        }

        return view('faculty.tasks', compact('tasksByRole', 'taskCounts', 'teamNames', 'activeTeam'));
    }

    public function storeTask(Request $request)
    {
        $facultyId = auth()->user()?->faculty?->user_information_id;
        if (!$facultyId) {
            return back()->withErrors(['title' => 'Faculty account not found.'])->withInput();
        }

        $validated = $request->validate([
            'group_name' => ['required', 'string'],
            'tasks' => ['nullable', 'array'],
            'tasks.*' => ['array'],
            'task_titles' => ['nullable', 'array'],
            'task_descriptions' => ['nullable', 'array'],
            'task_priorities' => ['nullable', 'array'],
            'due_date' => ['nullable', 'date', 'after_or_equal:today'],
        ], [
            'group_name.required' => 'Pick the team this task is for.',
        ]);

        // A team is one (group_name, faculty_id) pair, so the name is only a team
        // once it is read under this faculty. Checked here rather than with a bare
        // exists rule, which would accept another faculty's team of the same name.
        $teamMembers = StudentGroup::with(['student', 'roles'])
            ->where('faculty_id', $facultyId)
            ->where('group_name', $validated['group_name'])
            ->get();

        if ($teamMembers->isEmpty()) {
            return back()->withErrors(['group_name' => 'That team is not one of yours.'])->withInput();
        }

        // Read back off a membership row so the stored name matches the team's own
        // casing, and group_id travels with it the way every team-owned table carries it.
        $groupName = $teamMembers->first()->group_name;
        $groupId = $teamMembers->first()->group_id;

        // Check if any tasks were selected
        $tasksCreated = 0;
        // users.id => how many task rows landed on them, so each student gets one
        // "3 new tasks" notification rather than one per row.
        $assignedCounts = [];
        $facultyUser = auth()->user();

        // Site work has no "Before" without a snapshot of what the team's role
        // template looked like the moment the task was handed out — otherwise a
        // first submission has nothing to compare against. One snapshot covers
        // every member of a (group, role), so it is taken once and reused rather
        // than once per task row.
        $baselineVersionIds = [];

        if (!empty($validated['tasks']) && is_array($validated['tasks'])) {
            foreach ($validated['tasks'] as $role => $taskIndices) {
                if (!is_array($taskIndices)) continue;

                foreach ($taskIndices as $index) {
                    $title = $validated['task_titles'][$role][$index] ?? null;
                    $description = $validated['task_descriptions'][$role][$index] ?? null;
                    $priority = $validated['task_priorities'][$role][$index] ?? 'medium';

                    if ($title) {
                        // Only this team's holders of the role. Assigning used to read
                        // every team under the faculty, so one tick handed the same task
                        // to everybody at once.
                        $members = $teamMembers->filter(
                            fn ($member) => $member->roles->contains('role', $role)
                        );

                        $payload = [
                            'faculty_id'  => $facultyId,
                            'group_name'  => $groupName,
                            'group_id'    => $groupId,
                            'role'        => $role,
                            'title'       => $title,
                            'description' => User::cleanOptional($description),
                            'priority'    => $priority,
                            'due_date'    => $validated['due_date'] ?? null,
                            'status'      => 'active',
                        ];

                        // Nobody on this team fills the role yet. The row is still the
                        // team's — it carries group_name — so it waits for whoever takes
                        // the role rather than showing up on every team's dashboard.
                        if ($members->isEmpty()) {
                            Task::create($payload);
                            $tasksCreated++;
                        } else {
                            foreach ($members as $member) {
                                $assigneeUserId = $member->student?->user_id;

                                $baselineKey = $member->group_name . '|' . $role;
                                if (!array_key_exists($baselineKey, $baselineVersionIds)) {
                                    $baselineVersionIds[$baselineKey] = \App\Support\HotelTemplateBuilder::snapshotForReview(
                                        \App\Support\HotelTemplateBuilder::ensureTemplate($member, $role),
                                        $facultyUser,
                                        'Assigned'
                                    );
                                }

                                Task::create(array_merge($payload, [
                                    'student_id'  => $member->student_id,
                                    'assigned_to' => $assigneeUserId,
                                    'previous_version_id' => $baselineVersionIds[$baselineKey],
                                ]));
                                $tasksCreated++;

                                if ($assigneeUserId) {
                                    $assignedCounts[$assigneeUserId] = ($assignedCounts[$assigneeUserId] ?? 0) + 1;
                                }
                            }
                        }
                    }
                }
            }
        }

        if ($tasksCreated === 0) {
            return back()->withErrors(['tasks' => 'Please select at least one task from the checklist.'])->withInput();
        }

        ActivityLog::recordFor(
            ActivityLog::TASK_CREATED,
            'Created ' . $tasksCreated . ' task assignment(s) for ' . $groupName . '.'
        );

        Notifier::tasksAssigned(auth()->user(), $assignedCounts);

        // Keep the block the faculty was working in; the redirect used to drop it and
        // bounce them back to the first one.
        return redirect()->route('faculty.role', array_filter([
            'tab' => 'create_task',
            'class' => $request->input('class'),
        ]))->with('success', $tasksCreated . ' task(s) assigned to ' . $groupName . '.');
    }

    /**
     * A membership row standing for the task's team, which is what the template
     * helpers take to reach the right team's site.
     *
     * The task names its own team, so read that first: it is right for an unclaimed
     * row, and it does not depend on the submitting student still being on the team.
     * Rows assigned before tasks carried a team fall back to the student's membership,
     * which is how their team used to be worked out.
     */
    private function teamMembershipFor(Task $task, int $facultyId): ?StudentGroup
    {
        if (filled($task->group_name)) {
            $membership = StudentGroup::where('faculty_id', $facultyId)
                ->where('group_name', $task->group_name)
                // The submitter's own row when there is one, so the preview links
                // read as that student's, but any member resolves the same team.
                ->orderByRaw('CASE WHEN student_id = ? THEN 0 ELSE 1 END', [$task->student_id])
                ->first();

            if ($membership) {
                return $membership;
            }
        }

        return $task->student_id
            ? StudentGroup::where('student_id', $task->student_id)
                ->where('faculty_id', $facultyId)
                ->first()
            : null;
    }

    /** The task plus who did it and where their work can be seen. */
    public function reviewTask(Task $task)
    {
        $facultyId = auth()->user()?->faculty?->user_information_id;
        if (!$facultyId || (int) $task->faculty_id !== (int) $facultyId) {
            return response()->json(['error' => 'Not your task.'], 403);
        }

        $task->loadMissing(['assignedTo', 'feedbackBy', 'student']);
        $u = $task->assignedTo;
        $name = trim(implode(' ', array_filter([$u?->last_name, $u?->first_name, $u?->middle_name])));

        // The work lives on the team's site, so resolve the team.
        $membership = $this->teamMembershipFor($task, (int) $facultyId);

        $previewUrl = null;
        $beforePreviewUrl = null;
        $changes = [];
        $changeSummary = ['added' => 0, 'modified' => 0, 'removed' => 0];
        if ($membership && !$task->is_hotel_concept) {
            $previewUrl = route('faculty.teams.preview', [
                'group' => $membership->group_name,
                'role' => $task->role,
            ]);

            // Both sides come from snapshots taken at submission time, so the
            // comparison is exactly "what this task looked like when assigned (or
            // last sent back)" against "this submission" — it does not depend on
            // whether anyone pressed Ctrl+S. Before/After/Changes only exist once
            // there is something submitted to anchor "After" to.
            if ($task->submitted_version_id && TeamRoleTemplateVersion::whereKey($task->submitted_version_id)->exists()) {
                $afterVersionId = (int) $task->submitted_version_id;

                // Pin "After" to the submitted snapshot: the team keeps working
                // while the task sits in the queue, and the live site would
                // quietly drift away from what was actually handed in.
                $previewUrl = route('faculty.teams.preview', [
                    'group' => $membership->group_name,
                    'role' => $task->role,
                    'before_version' => $afterVersionId,
                    'highlight_task' => $task->task_id,
                ]);

                $beforeVersionId = ($task->previous_version_id && TeamRoleTemplateVersion::whereKey($task->previous_version_id)->exists())
                    ? (int) $task->previous_version_id
                    : null;

                // No earlier snapshot (e.g. a task predating this feature) renders
                // Before as the pristine template rather than hiding the toggle —
                // every customization then correctly reads as "added".
                $beforePreviewUrl = route('faculty.teams.preview', [
                    'group' => $membership->group_name,
                    'role' => $task->role,
                    'before_version' => $beforeVersionId ?? 'baseline',
                ]);

                $roleTemplate = \App\Support\HotelTemplateBuilder::ensureTemplate($membership, $task->role);
                $diff = \App\Support\TemplateDiff::between($roleTemplate, $beforeVersionId, $afterVersionId);
                $changes = $diff['changes'];
                $changeSummary = $diff['summary'];
            }
        }

        $payload = [
            'id' => $task->task_id,
            'title' => $task->title,
            'description' => $task->description,
            'role' => $task->role,
            'role_label' => $task->role_label,
            'priority' => $task->priority,
            'status' => $task->status,
            'needs_revision' => $task->needs_revision,
            'is_hotel_concept' => $task->is_hotel_concept,
            'student_name' => $name !== '' ? $name : ($u?->name ?? null),
            'group_name' => $membership?->group_name,
            'due_date' => optional($task->due_date)->format('M d, Y g:i A'),
            'submitted_at' => $task->status === 'archived'
                ? optional($task->updated_at)->format('M d, Y g:i A')
                : null,
            'feedback' => $task->feedback,
            'feedback_at' => optional($task->feedback_at)->format('M d, Y g:i A'),
            'feedback_by' => $task->feedbackBy?->name,
            'revision_count' => (int) $task->revision_count,
            'preview_url' => $previewUrl,
            'before_preview_url' => $beforePreviewUrl,
            'changes' => $changes,
            'change_summary' => $changeSummary,
        ];

        // The concept is text, not a page, so the review dialog reads it inline —
        // with its edit history, which is how faculty sees who contributed what.
        if ($task->is_hotel_concept && $membership) {
            $payload += HotelConceptController::payload(
                HotelConceptController::forTeam($membership->group_name, (int) $facultyId)
            );
        }

        return response()->json($payload);
    }

    /**
     * Approve the submission, or send it back with feedback. "Send back" reuses
     * status 'active' so the row reappears in the student's list — see the
     * add_feedback_to_tasks migration for why the enum is left alone.
     */
    public function storeTaskFeedback(Request $request, Task $task)
    {
        $facultyUser = auth()->user();
        $facultyId = $facultyUser?->faculty?->user_information_id;
        if (!$facultyId || (int) $task->faculty_id !== (int) $facultyId) {
            return response()->json(['error' => 'Not your task.'], 403);
        }

        $data = $request->validate([
            'decision' => ['required', 'in:approve,revise'],
            'feedback' => ['nullable', 'string', 'max:2000', 'required_if:decision,revise'],
            // Which of the team's two concepts this verdict is about. Required only
            // for the concept task; every other task is a single piece of work.
            'slot' => [
                Rule::requiredIf(fn () => $task->is_hotel_concept),
                'integer',
                Rule::in(HotelConceptDesk::SLOTS),
            ],
        ], [
            'feedback.required_if' => 'Tell the student what to change before sending it back.',
            'slot.required' => 'Say which concept this verdict is about.',
        ]);

        $revise = $data['decision'] === 'revise';

        // The concepts are two rows per team behind several task rows, and faculty
        // judges each separately, so the verdict is recorded against one concept and
        // the task rows are re-derived from both — see HotelConceptDesk::review().
        if ($task->is_hotel_concept) {
            return $this->storeConceptFeedback(
                $task,
                $facultyUser,
                (int) $facultyId,
                (int) $data['slot'],
                $revise,
                $data['feedback'] ?? null
            );
        }

        // A concept task can be reopened by one concept while the other is still
        // with faculty, so this check belongs after the concept branch, which does
        // its own per-concept version.
        if ($task->status !== 'archived') {
            return response()->json([
                'error' => 'This task has not been submitted yet, so there is nothing to review.',
            ], 422);
        }

        $task->fill([
            'feedback' => $data['feedback'] ?: null,
            'feedback_at' => now(),
            'feedback_by' => $facultyUser->user_id,
        ]);

        if ($revise) {
            $task->status = 'active';
            $task->revision_count = (int) $task->revision_count + 1;

            // Freeze the work as it stands at the moment changes are asked for. This
            // is the "Before" the next submission is judged against, and it is the
            // honest one: what the student was looking at when the feedback landed.
            // Anchoring here rather than only at submit time also means a single
            // revise/resubmit round is enough to have something to compare.
            $membership = $this->teamMembershipFor($task, (int) $facultyId);

            if ($membership) {
                $beforeId = \App\Support\HotelTemplateBuilder::snapshotForReview(
                    \App\Support\HotelTemplateBuilder::ensureTemplate($membership, $task->role),
                    $facultyUser,
                    'Sent back: ' . $task->title
                );
                if ($beforeId) {
                    $task->previous_version_id = $beforeId;
                }
            }
        }

        $task->save();

        ActivityLog::record(
            $facultyUser,
            ActivityLog::EVALUATION_RECORDED,
            ($revise ? 'Requested changes on' : 'Approved') . ' task "' . $task->title . '"'
                . ($task->assignedTo ? ' by ' . $task->assignedTo->name : '') . '.'
        );

        Notifier::taskFeedback($facultyUser, $task, $revise);

        return response()->json([
            'success' => true,
            'status' => $task->status,
            'revision_count' => (int) $task->revision_count,
            'message' => $revise
                ? 'Sent back to the student with your feedback.'
                : 'Task approved.',
        ]);
    }

    /**
     * The verdict on one of a team's two hotel concepts.
     *
     * Written against the concept rather than the task row that happened to be
     * opened: the concepts are team-level, and every Front Desk member holds a task
     * row covering both. One slot at a time, because the pair exists so each can be
     * judged on its own merits — approve one, send the other back.
     *
     * The response carries the whole team back, so the dialog can redraw both cards
     * and leave the other concept's Approve buttons where they belong.
     */
    private function storeConceptFeedback(
        Task $task,
        User $facultyUser,
        int $facultyId,
        int $slot,
        bool $revise,
        ?string $feedback
    ) {
        $membership = $this->teamMembershipFor($task, (int) $facultyId);

        if (!$membership) {
            return response()->json([
                'error' => 'This concept task is not attached to one of your teams.',
            ], 422);
        }

        $concept = HotelConcept::where('group_name', $membership->group_name)
            ->where('faculty_id', $facultyId)
            ->where('slot', $slot)
            ->first();

        if (!$concept) {
            return response()->json([
                'error' => 'This team has not proposed ' . HotelConceptDesk::slotLabel($slot) . ' yet.',
            ], 422);
        }

        $teamConcepts = HotelConceptDesk::conceptsFor($membership->group_name, $facultyId);

        if (!HotelConceptDesk::allSlotsFilled($teamConcepts)) {
            return response()->json([
                'error' => 'This team has not proposed both concepts yet.',
            ], 422);
        }

        if (HotelConceptDesk::isDecided($teamConcepts)) {
            return response()->json([
                'error' => 'You already approved a concept for this team — that decision is final.',
            ], 422);
        }

        HotelConceptDesk::review($concept, $membership, $facultyUser, $revise, $feedback);

        $team = HotelConceptController::payload(
            HotelConceptController::forTeam($membership->group_name, $facultyId)
        );

        return response()->json(array_merge($team, [
            'success' => true,
            // The task row is re-derived from both concepts, so read it back rather
            // than assuming: one concept coming back reopens it, both settled closes it.
            'status' => HotelConceptDesk::teamTasks((string) $membership->group_name, $facultyId)
                ->first()?->status ?? 'archived',
            'message' => $revise
                ? 'Sent ' . HotelConceptDesk::slotLabel($slot) . ' back to the team with your feedback.'
                : HotelConceptDesk::slotLabel($slot) . ' approved.',
        ]));
    }

    /**
     * Render a team's site read-only so faculty can see the work itself.
     * Mirrors the student template route, but resolves the team from the
     * requested group instead of the viewer's own student profile.
     */
    public function previewTeamSite(Request $request)
    {
        $facultyId = auth()->user()?->faculty?->user_information_id;
        if (!$facultyId) {
            abort(403);
        }

        $data = $request->validate([
            'group' => ['required', 'string', 'max:255'],
            'role' => ['nullable', 'string'],
            // A version snapshot row id, or the literal "baseline" — renders the
            // "Before" half of the faculty review comparison (a real earlier
            // snapshot, or the pristine template when none exists) instead of
            // the live site.
            'before_version' => ['nullable', 'regex:/^(baseline|\d+)$/'],
            // Which task's Changes to outline on this render. Only takes effect
            // when before_version is exactly that task's submitted snapshot —
            // it must not paint "changed" badges onto the Before side, or onto
            // some other task's snapshot reached by editing the URL by hand.
            'highlight_task' => ['nullable', 'integer'],
        ]);

        $membership = StudentGroup::where('faculty_id', $facultyId)
            ->where('group_name', $data['group'])
            ->first();
        if (!$membership) {
            abort(404, 'That team does not belong to you.');
        }

        $isBaseline = ($data['before_version'] ?? null) === 'baseline';

        // The snapshot names the role it belongs to, so the override is keyed off the
        // version row itself rather than the request's role — a mismatched pair would
        // otherwise render one role's history under another's chunk. Loading it
        // through this faculty's own team also keeps it from reading another team's.
        $versionOverrides = [];
        if ($isBaseline) {
            if (!empty($data['role']) && \App\Support\HotelTemplateBuilder::isValidRole($data['role'])) {
                $versionOverrides[$data['role']] = 'baseline';
            }
        } elseif (!empty($data['before_version'])) {
            $version = TeamRoleTemplateVersion::with('template')
                ->whereKey($data['before_version'])
                ->whereHas('template', function ($q) use ($membership) {
                    $q->where('group_name', $membership->group_name)
                        ->where('faculty_id', $membership->faculty_id);
                })
                ->first();

            if ($version && $version->template) {
                $versionOverrides[$version->template->role] = (int) $version->team_role_template_version_id;
            }
        }

        $customizations = \App\Support\HotelTemplateBuilder::mergeTeamCustomizations(
            (string) $membership->group_name,
            (int) $membership->faculty_id,
            $versionOverrides
        );

        $selected = \App\Models\GroupSettings::where('group_name', $membership->group_name)
            ->where('faculty_id', $membership->faculty_id)
            ->value('selected_template');

        $selected = in_array((string) $selected, ['1', '2'], true) ? (string) $selected : '1';

        // Outline what changed, but only on the exact render this task's "After"
        // link points at — never on the Before side, and never when the URL was
        // hand-edited to point highlight_task at a different snapshot.
        $reviewHighlight = null;
        if (!empty($data['highlight_task']) && !$isBaseline && !empty($data['before_version'])) {
            $task = Task::find($data['highlight_task']);
            if ($task
                && (int) $task->faculty_id === $facultyId
                && $task->submitted_version_id
                && (int) $data['before_version'] === (int) $task->submitted_version_id
            ) {
                $roleTemplate = \App\Support\HotelTemplateBuilder::ensureTemplate($membership, $task->role);
                $beforeVersionId = ($task->previous_version_id && TeamRoleTemplateVersion::whereKey($task->previous_version_id)->exists())
                    ? (int) $task->previous_version_id
                    : null;
                $reviewHighlight = \App\Support\TemplateDiff::between(
                    $roleTemplate,
                    $beforeVersionId,
                    (int) $task->submitted_version_id
                )['highlight'];
            }
        }

        // Read-only: no edit permission, no editable pages.
        return view('students.template.' . $selected . 'defaulttemplate', [
            'customizations' => $customizations,
            'canEditTemplate' => false,
            'editablePages' => [],
            'builderRole' => $data['role'] ?? 'front_desk',
            'reviewHighlight' => $reviewHighlight,
        ]);
    }

    public function destroyTask(Task $task)
    {
        $facultyId = auth()->user()?->faculty?->user_information_id;
        if ($task->faculty_id !== $facultyId) {
            abort(403);
        }

        // Every team is assigned the hotel concept automatically, and the rest of
        // their simulation is built on it, so it is not a task to hand back.
        if ($task->is_hotel_concept) {
            return back()->withErrors([
                'task' => 'The hotel concept is assigned to every team automatically and cannot be deleted.',
            ]);
        }

        $role = $task->role;
        $title = $task->title;
        $task->delete();

        ActivityLog::recordFor(
            ActivityLog::TASK_DELETED,
            'Deleted task "' . $title . '" from the ' . $role . ' role.'
        );

        return redirect()->route('faculty.tasks', ['tab' => $role])
            ->with('success', 'Task deleted.');
    }

    public function results()
    {
        $facultyId = auth()->user()?->faculty?->user_information_id;
        if (!$facultyId) {
            abort(403, 'Faculty account not found.');
        }

        ActivityLog::recordFor(ActivityLog::EVALUATION_RECORDED, 'Reviewed team task results and evaluation.');

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
        $facultyId = auth()->user()?->faculty?->user_information_id;
        if (!$facultyId) {
            abort(403, 'Faculty account not found.');
        }

        ActivityLog::recordFor(ActivityLog::REPORT_GENERATED, 'Generated the faculty performance report.');

        $roleLabels = [
            'front_desk' => 'Front Desk',
            'restaurant_management' => 'Restaurant',
            'room_management' => 'Room',
            'maintenance' => 'Maintenance',
            'housekeeping' => 'Housekeeping',
        ];

        // Finalized/completed tasks (archived) — reflected here as soon as status becomes archived
        $completedTasks = Task::with(['student.user', 'assignedTo'])
            ->where('faculty_id', $facultyId)
            ->where('status', 'archived')
            ->orderByDesc('updated_at')
            ->get();

        $membershipByStudentId = StudentGroup::with(['roles', 'student.user'])
            ->where('faculty_id', $facultyId)
            ->get()
            ->groupBy('student_id');

        $teamMembersByName = StudentGroup::with(['roles', 'student.user'])
            ->where('faculty_id', $facultyId)
            ->get()
            ->groupBy(fn ($m) => $m->group_name ?? 'Unassigned');

        $buckets = [];

        foreach ($completedTasks as $task) {
            $studentId = $task->student_id ? (int) $task->student_id : null;
            if (!$studentId && $task->assigned_to) {
                $studentId = Student::where('user_id', $task->assigned_to)->value('user_information_id');
                $studentId = $studentId ? (int) $studentId : null;
            }

            $membership = $studentId
                ? ($membershipByStudentId->get($studentId)?->first())
                : null;

            $teamName = $membership?->group_name ?: 'Unassigned';
            $key = $teamName;

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
                'due_date' => optional($task->due_date)->format('M d, Y g:i A'),
                'completed_at' => optional($task->updated_at)->format('M d, Y'),
                'priority' => strtolower($task->priority ?? 'medium'),
            ];
        }

        foreach ($buckets as $key => &$bucket) {
            $members = $teamMembersByName->get($key, collect());
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

        return view('faculty.reports', compact('teamReports', 'roleLabels'));
    }

    public function activityLogs(Request $request)
    {
        $facultyId = auth()->user()?->faculty?->user_information_id;
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
            // Fourth argument names the column to ignore by. Without it the rule looks
            // for a column called "id", which users no longer has.
            'email' => ['required', 'email', 'max:255', 'unique:users,email,' . $user->user_id . ',user_id'],
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
            \Illuminate\Support\Facades\Storage::disk(\App\Support\HotelImageStore::disk())->delete($user->avatar);
            $userData['avatar'] = null;
        }

        if ($request->hasFile('avatar')) {
            try {
                $storedPath = $request->file('avatar')->store('avatars/faculty', \App\Support\HotelImageStore::disk());
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::error('Faculty avatar upload failed', [
                    'user_id' => $user->user_id,
                    'disk' => \App\Support\HotelImageStore::disk(),
                    'error' => $e->getMessage(),
                ]);

                return back()->withErrors(['avatar' => 'Could not upload the photo to storage. Please try again.']);
            }

            if ($user->avatar) {
                \Illuminate\Support\Facades\Storage::disk(\App\Support\HotelImageStore::disk())->delete($user->avatar);
            }

            $userData['avatar'] = $storedPath;
        }

        $user->update($userData);

        $faculty->update([
            'phone_number' => $validated['phone_number'] ?? null,
        ]);

        ActivityLog::recordFor(ActivityLog::ACCOUNT_UPDATED, 'Updated their own faculty profile.');

        return redirect()->route('faculty.profile')
            ->with('success', 'Profile information updated successfully.');
    }
}