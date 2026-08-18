<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Dean and faculty can flip a student's account to 'inactive' at any time. The login
 * gate in AuthController stops a fresh sign in, but a student already holding a session
 * would keep it forever — this drops that session on their very next request.
 */
class EnsureStudentIsActive
{
    public const MESSAGE = 'Your account has been deactivated. Contact your instructor or the dean.';

    public function handle(Request $request, Closure $next): Response
    {
        // Let a deactivated student sign themselves out without a confusing alert.
        if ($request->routeIs('logout')) {
            return $next($request);
        }

        $user = Auth::user();

        if ($user && $user->role === 'student' && ($user->status ?? 'active') === 'inactive') {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            // withErrors() flashes straight away, so it has to run after invalidate().
            return redirect()->to('/?login=1')
                ->withErrors(['email' => self::MESSAGE])
                ->with('error_title', 'Account Deactivated');
        }

        return $next($request);
    }
}
