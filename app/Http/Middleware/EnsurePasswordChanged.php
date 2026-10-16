<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * A faculty account the dean creates signs in with a generated password sent to
 * their Gmail (users.must_change_password). Until they replace it, every page
 * sends them to their profile, where the Change Password form is; a fetch or form
 * post gets a 403. AuthController::updatePassword clears the flag.
 */
class EnsurePasswordChanged
{
    /** What stays reachable meanwhile: the form itself, signing out, and the shell's polls. */
    private const ALLOWED = [
        'account.password',
        'logout',
        'session.ping',
        'session.ended',
        'notifications.*',
        'public.hotel*',
    ];

    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (!$user?->must_change_password || $request->routeIs(...self::ALLOWED)) {
            return $next($request);
        }

        // The page holding the Change Password form; a student's is on the dashboard.
        $profile = ['dean' => 'dean.profile', 'faculty' => 'faculty.profile', 'student' => 'students.dashboard'][$user->portal()] ?? null;
        if (!$profile || $request->routeIs($profile)) {
            return $next($request);
        }

        if ($request->isMethod('GET') && !$request->expectsJson()) {
            // Keeps the sign-in's welcome message for the page they land on.
            $request->session()->reflash();

            return redirect()->route($profile);
        }

        abort(403, 'Change your temporary password first.');
    }
}
