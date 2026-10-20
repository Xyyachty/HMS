<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use App\Http\Middleware\EnforceSessionRules;
use App\Mail\PasswordResetCodeMail;
use App\Models\ActivityLog;
use App\Models\User;
use App\Support\StudentWelcomeMailer;
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

        // No remember-me cookie: it would sign a user back in after the
        // fifteen-minute inactivity limit (EnforceSessionRules) had ended them.
        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();

            $user = Auth::user();
            if ($user && ($user->status ?? 'active') === 'pending') {
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                return back()->withErrors([
                    'email' => 'Your account is pending dean approval.',
                ])->with('error_title', 'Account Pending')->onlyInput('email');
            }

            // The dean can deactivate a faculty account, dean or faculty a student's;
            // block the sign-in here. (An already-open session is ended on its next
            // request by EnforceSessionRules.)
            if ($user && EnforceSessionRules::isDeactivated($user)) {
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                return back()->withErrors([
                    'email' => EnforceSessionRules::REASONS['deactivated']['message'],
                ])->with('error_title', EnforceSessionRules::REASONS['deactivated']['title'])->onlyInput('email');
            }

            // The first sign-in on a faculty account the dean created, with the
            // emailed temporary password, is what makes it Active. Manage Users
            // picks the change up on its next poll.
            if ($user && $user->status === User::PENDING_ACTIVATION) {
                $user->activate();
                ActivityLog::record($user, ActivityLog::ACCOUNT_UPDATED, 'Activated their account on first sign-in.');
            }

            // A faculty deleted outside the app leaves their block vacant; the
            // faculty after them moves into it here (Faculty::closeBlockGaps).
            if ($user && $user->role === 'faculty') {
                \App\Models\Faculty::closeBlockGaps();
            }

            if ($user) {
                // This sign-in becomes the account's only session: any other
                // device holding the old token ends on its next request.
                $loginToken = Str::random(60);
                Auth::getProvider()->updateRememberToken($user, $loginToken);
                $request->session()->put(EnforceSessionRules::TOKEN_KEY, $loginToken);
                $request->session()->put(EnforceSessionRules::ACTIVITY_KEY, now()->timestamp);
                $request->session()->forget(EnforceSessionRules::ENDED_KEY);

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

            $dashboard = $user?->dashboardRoute();

            return $dashboard ? redirect()->route($dashboard) : redirect()->route('login');
        }

        return back()->withErrors([
            'email' => 'The email or password is incorrect.',
        ])->with('error_title', 'Login Failed')->onlyInput('email');
    }

    /* Forgot-password state lives in the session: the code proves the inbox, and
       tying it to the session means a code read off someone else's screen is no
       use in another browser. Shape: email, code (hash), expires, attempts, verified. */
    private const RESET_KEY = 'forgot_password';
    private const RESET_CODE_MINUTES = 10;
    private const RESET_MAX_ATTEMPTS = 5;

    /** The email this session has proven it owns, or null. */
    private function verifiedResetEmail(Request $request): ?string
    {
        $state = $request->session()->get(self::RESET_KEY);

        if (!$state || empty($state['verified']) || now()->timestamp > $state['expires']) {
            return null;
        }

        return $state['email'];
    }

    public function forgotPassword(Request $request)
    {
        // Lets the page reopen on the password step after a validation error.
        return view('auth.forgotpassword', ['verifiedEmail' => $this->verifiedResetEmail($request)]);
    }

    public function sendResetCode(Request $request): JsonResponse
    {
        $request->validate([
            'email' => ['required', 'email'],
        ]);

        $user = $this->resettableAccount($request->input('email'));

        if (!$user) {
            return response()->json(['message' => 'This account is not registered. Please contact your Faculty.'], 422);
        }

        if (!StudentWelcomeMailer::isDeliverable($user->email)) {
            return response()->json(['message' => 'This account has no real email address on file. Please contact your Faculty.'], 422);
        }

        $code = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        // A new code replaces the old one and starts the attempts over.
        $request->session()->put(self::RESET_KEY, [
            'email' => $user->email,
            'code' => Hash::make($code),
            'expires' => now()->addMinutes(self::RESET_CODE_MINUTES)->timestamp,
            'attempts' => 0,
            'verified' => false,
        ]);

        try {
            Mail::to($user->email)->send(new PasswordResetCodeMail(
                userName: trim((string) ($user->first_name ?: $user->name)) ?: 'there',
                code: $code,
                expiresInMinutes: self::RESET_CODE_MINUTES,
            ));
        } catch (\Throwable $e) {
            Log::error('Password reset code email failed for ' . $user->email . ': ' . $e->getMessage());
            $request->session()->forget(self::RESET_KEY);

            return response()->json(['message' => 'We could not send the code right now. Please try again later.'], 500);
        }

        return response()->json(['sent' => true]);
    }

    public function verifyResetCode(Request $request): JsonResponse
    {
        $data = $request->validate([
            'email' => ['required', 'email'],
            'code' => ['required', 'digits:6'],
        ]);

        $state = $request->session()->get(self::RESET_KEY);

        if (!$state || $state['email'] !== strtolower(trim($data['email'])) || now()->timestamp > $state['expires']) {
            return response()->json(['message' => 'This code has expired. Please send a new one.', 'expired' => true], 422);
        }

        if (!Hash::check($data['code'], $state['code'])) {
            $state['attempts']++;
            $left = self::RESET_MAX_ATTEMPTS - $state['attempts'];

            // Six digits fall to guessing without a cap, so a code dies after five misses.
            if ($left <= 0) {
                $request->session()->forget(self::RESET_KEY);

                return response()->json(['message' => 'Too many incorrect codes. Please send a new one.', 'expired' => true], 422);
            }

            $request->session()->put(self::RESET_KEY, $state);

            return response()->json(['message' => "Incorrect code. {$left} " . ($left === 1 ? 'try' : 'tries') . ' left.'], 422);
        }

        $state['verified'] = true;
        // Fresh window to choose a password, counted from the moment of proof.
        $state['expires'] = now()->addMinutes(self::RESET_CODE_MINUTES)->timestamp;
        $request->session()->put(self::RESET_KEY, $state);

        return response()->json(['verified' => true]);
    }

    /* A password may only be reset onto an account that exists and is usable. Pending
       and deactivated accounts are excluded, or a student the faculty switched off
       could reset their way back in. Pending Activation counts as usable: it is a
       faculty account that can already sign in, just not yet signed in to. Both
       the live check and the submit run through here so the two never disagree. */
    private function resettableAccount(?string $email): ?User
    {
        if (!$email) {
            return null;
        }

        $user = User::whereEmail($email)->first();

        if (!$user || !in_array($user->status ?? 'active', ['active', User::PENDING_ACTIVATION], true)) {
            return null;
        }

        return $user;
    }

    public function forgotPasswordSubmit(Request $request)
    {
        $data = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'min:8', 'confirmed'],
        ]);

        // The emailed code is the only proof of ownership; without it, knowing an
        // address would be enough to take over its account.
        if ($this->verifiedResetEmail($request) !== strtolower(trim($data['email']))) {
            return back()->withErrors([
                'email' => 'Please verify your email with the code we sent first.',
            ])->withInput($request->only('email'));
        }

        $user = $this->resettableAccount($data['email']);

        if (!$user) {
            return back()->withErrors([
                'email' => 'This account is not registered. Please contact your Faculty.',
            ])->withInput();
        }

        // One code, one reset.
        $request->session()->forget(self::RESET_KEY);

        $user->password = Hash::make($data['password']);
        // A password they chose themselves, so the temporary one no longer needs replacing.
        $user->must_change_password = false;
        $user->setRememberToken(Str::random(60));
        $user->save();

        return back()->with('password_reset', true);
    }

    /**
     * Change the signed-in user's own password from their profile page. Dean,
     * faculty and students share this one form, so errors go in their own bag
     * and never mix with the profile form's.
     */
    public function updatePassword(Request $request)
    {
        $data = $request->validateWithBag('password', [
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'string', 'min:8', 'confirmed', 'different:current_password'],
        ], [
            'current_password.current_password' => 'Your current password is incorrect.',
            'password.different' => 'The new password must be different from your current password.',
        ]);

        $user = $request->user();
        $user->password = Hash::make($data['password']);
        // Lifts EnsurePasswordChanged off an account still on its temporary password.
        $user->must_change_password = false;
        $user->save();

        ActivityLog::recordFor(ActivityLog::ACCOUNT_UPDATED, 'Changed their own password.');

        return back()->with('password_success', 'Password changed successfully.');
    }

    public function checkForgotPasswordEmail(Request $request): JsonResponse
    {
        $request->validate([
            'email' => ['required', 'email'],
        ]);

        return response()->json([
            'exists' => $this->resettableAccount($request->input('email')) !== null,
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
