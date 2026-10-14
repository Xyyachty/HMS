<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class RedirectIfAuthenticated
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string ...$guards): Response
    {
        $guards = empty($guards) ? [null] : $guards;

        foreach ($guards as $guard) {
            if (Auth::guard($guard)->check()) {
                $user = Auth::guard($guard)->user();

                if ($user && $user->role === 'dean') {
                    return redirect()->route('dean.dashboard');
                }

                if ($user && ($user->role === 'faculty' || $user->faculty)) {
                    return redirect()->route('faculty.dashboard');
                }

                if ($user && ($user->role === 'student' || $user->student)) {
                    return redirect()->route('students.dashboard');
                }

                // No portal to send them to. Not redirect('/'): the landing page
                // runs this middleware too, so that would loop.
                return $next($request);
            }
        }

        return $next($request);
    }
}
