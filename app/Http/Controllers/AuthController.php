<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use App\Models\User;
use Illuminate\Http\JsonResponse;

class AuthController extends Controller
{
    public function login()
    {
        return view('auth.login');
    }

    public function authenticate(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

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

    public function signup()
    {
        return view('auth.signup');
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

        $user = User::where('email', $data['email'])->first();

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

        $exists = User::where('email', $request->input('email'))->exists();

        return response()->json([
            'exists' => $exists,
        ]);
    }

    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
