<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Keeps each portal's routes to its own accounts: `role:dean`, `role:faculty`,
 * `role:student`, or several at once (`role:faculty,dean`).
 *
 * The role comes from the signed-in account (User::portal()), never from the URL,
 * so editing /students/dashboard into /dean/users gets a dean's page for deans only.
 * Anyone else typing another portal's address is sent back to their own dashboard;
 * a fetch or form post gets a plain 403 instead, since a redirect would hand the
 * page's script a dashboard's HTML where it expects JSON.
 *
 * Runs after `auth`, so there is always a user here.
 */
class EnsureRole
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();
        $portal = $user?->portal();

        if ($portal !== null && in_array($portal, $roles, true)) {
            return $next($request);
        }

        if ($portal !== null && $request->isMethod('GET') && !$request->expectsJson()) {
            return redirect()->route($user->dashboardRoute());
        }

        abort(403, 'You do not have access to this page.');
    }
}
