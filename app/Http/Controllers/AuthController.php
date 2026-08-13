<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Http\JsonResponse;

class AuthController extends Controller
{
    public function login()
    {
        // Login UI lives on the landing page drawer — keep /login for auth redirects.
        return redirect()->to('/?login=1');
    }

    public function authenticate(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        // Auth::attempt queries the column directly, so it never passes through the
        // model's email mutator. Emails are stored lowercased; match that here or
        // logins with different capitalisation fail on PostgreSQL.
        $credentials['email'] = strtolower(trim($credentials['email']));

        $remember = $request->boolean('remember');

        if (Auth::attempt($credentials, $remember)) {
            $request->session()->regenerate();

            $user = Auth::user();
            if ($user && ($user->status ?? 'active') === 'pending') {
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                return back()->withErrors([
                    'email' => 'Your account is pending dean approval.',
                ])->onlyInput('email');
            }
            if ($user) {
                ActivityLog::record($user, ActivityLog::LOGIN, 'Signed in to the portal.');

                $roleLabel = $user->role === 'dean' ? 'admin' : $user->role;

                if (!$roleLabel && $user->faculty) {
                    $roleLabel = 'faculty';
                } elseif (!$roleLabel && $user->student) {
                    $roleLabel = 'student';
                }

                $request->session()->flash('welcome', [
                    'name' => $user->name ?? 'User',
                    'role' => $roleLabel ?? 'user',
                ]);
            }

            if ($user && $user->role === 'dean') {
                return redirect()->route('dean.dashboard');
            }

            if ($user && ($user->role === 'faculty' || $user->faculty)) {
                return redirect()->route('faculty.dashboard');
            }

            if ($user && ($user->role === 'student' || $user->student)) {
                return redirect()->route('students.dashboard');
            }

            return redirect()->route('login');
        }

        return back()->withErrors([
            'email' => 'The provided credentials do not match our records.',
        ])->onlyInput('email');
    }

    public function forgotPassword()
    {
        return view('auth.forgotpassword');
    }

    public function forgotPasswordSubmit(Request $request)
    {
        $data = $request->validate([
            'email' => ['required', 'email', 'regex:/^[^@\s]+@hms\.edu$/i'],
            'password' => ['required', 'min:8', 'confirmed'],
        ]);

        $user = User::whereEmail($data['email'])->first();

        if (!$user) {
            return back()->withErrors([
                'email' => 'No account found with this email address.',
            ])->withInput();
        }

        $user->password = Hash::make($data['password']);
        $user->setRememberToken(Str::random(60));
        $user->save();

        return back()->with('password_reset', true);
    }

    public function checkForgotPasswordEmail(Request $request): JsonResponse
    {
        $request->validate([
            'email' => ['required', 'email', 'regex:/^[^@\s]+@hms\.edu$/i'],
        ]);

        $exists = User::whereEmail($request->input('email'))->exists();

        return response()->json([
            'exists' => $exists,
        ]);
    }

    public function logout(Request $request)
    {
        // Record before the session dies, or there is no user left to attribute.
        ActivityLog::record($request->user(), ActivityLog::LOGOUT, 'Signed out of the portal.');

        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
