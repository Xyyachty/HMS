<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Validator;
use App\Events\StudentApproved;
use App\Models\Faculty;
use App\Models\User;
use PhpOffice\PhpSpreadsheet\IOFactory;

class DeanController extends Controller
{
    public function dashboard()
    {
        return view('dean.dashboard');
    }

    public function users()
    {
        $users = User::with('faculty')->latest()->get();

        return view('dean.usermanagement', compact('users'));
    }

    public function usersLive()
    {
        $users = User::with('faculty')->latest()->get()->map(function ($user) {
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
                'status' => $user->status ?? ($user->faculty->status ?? 'active'),
                'joined' => optional($user->created_at)->format('M d, Y'),
            ];
        });

        return response()->json($users);
    }

    public function faculties()
    {
        $faculties = Faculty::with(['user', 'studentGroups.student.user'])->latest()->get();
        $completedTasks = \App\Models\Task::with('faculty.user')
            ->where('status', 'archived')
            ->orderByDesc('updated_at')
            ->get();

        return view('dean.faculties', compact('faculties', 'completedTasks'));
    }

    public function storeFaculty(Request $request)
    {
        $validated = $request->validate([
            'first_name' => ['required', 'string', 'max:255'],
            'middle_name' => ['nullable', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'phone_number' => ['nullable', 'string', 'max:30'],
            'status' => ['required', 'in:active,suspended'],
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
            'middle_name' => $validated['middle_name'] ?? null,
            'last_name' => $validated['last_name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => 'faculty',
        ]);

        Faculty::create([
            'user_id' => $user->id,
            'phone_number' => $validated['phone_number'] ?? null,
            'status' => $validated['status'],
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
            'status' => ['required', 'in:active,suspended'],
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
            'middle_name' => $validated['middle_name'] ?? null,
            'last_name' => $validated['last_name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => $validated['role'],
        ];

        if (Schema::hasColumn('users', 'status')) {
            $userData['status'] = $validated['status'];
        }

        if (Schema::hasColumn('users', 'phone_number')) {
            $userData['phone_number'] = $validated['phone_number'] ?? null;
        }

        $user = User::create($userData);

        if ($validated['role'] === 'faculty') {
            Faculty::create([
                'user_id' => $user->id,
                'phone_number' => $validated['phone_number'] ?? null,
                'status' => $validated['status'],
            ]);
        }

        $message = $validated['role'] === 'faculty'
            ? 'Faculty account created successfully.'
            : 'Student account created successfully.';

        return redirect()->route('dean.users')->with('success', $message);
    }

    public function updateUser(Request $request, User $user)
    {
        $validated = $request->validate([
            'phone_number' => ['nullable', 'string', 'max:30'],
            'status' => ['required', 'in:active,suspended'],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
        ]);

        if (Schema::hasColumn('users', 'status')) {
            $user->status = $validated['status'];
        }

        if (Schema::hasColumn('users', 'phone_number')) {
            $user->phone_number = $validated['phone_number'] ?? null;
        }

        if (!empty($validated['password'])) {
            $user->password = Hash::make($validated['password']);
        }

        $user->save();

        if ($user->role === 'faculty' && $user->faculty) {
            $user->faculty->phone_number = $validated['phone_number'] ?? null;
            $user->faculty->status = $validated['status'];
            $user->faculty->save();
        }

        return redirect()->route('dean.users')->with('success', 'User updated successfully.');
    }

    public function reports()
    {
        return view('dean.reports');
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
                'status' => ['nullable', 'in:active,suspended'],
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
                'middle_name' => $validated['middle_name'] ?? null,
                'last_name' => $validated['last_name'],
                'email' => $validated['email'],
                'password' => Hash::make($password),
                'role' => $validated['role'],
            ];

            if (Schema::hasColumn('users', 'status')) {
                $userData['status'] = $validated['status'] ?? 'active';
            }

            if (Schema::hasColumn('users', 'phone_number')) {
                $userData['phone_number'] = $validated['phone_number'] ?? null;
            }

            try {
                $user = User::create($userData);

                if ($validated['role'] === 'faculty') {
                    Faculty::create([
                        'user_id' => $user->id,
                        'phone_number' => $validated['phone_number'] ?? null,
                        'status' => $validated['status'] ?? 'active',
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
