<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * One device per account, and a fifteen-minute inactivity limit, for every role.
 *
 * Single device: each sign-in writes a fresh token to users.remember_token and
 * keeps a copy in that session (AuthController::authenticate). A session whose
 * copy no longer matches was replaced by a newer sign-in and ends on its next
 * request. The column already exists; remember-me cookies are no longer issued,
 * so nothing else reads it.
 *
 * Inactivity: the session keeps when the user last did something. Only real
 * interaction counts — a page navigation, or a ping from partials.session-guard
 * flagged active=1 when the user clicks, types or scrolls. Background polls
 * (the bell, team presence, review pulses) never extend the session, or a page
 * left open would stay signed in forever.
 *
 * At IDLE_SECONDS the page warns for WARNING_SECONDS, then signs out. The server
 * ends the session itself once both have passed, plus GRACE_SECONDS so a "Stay
 * Logged In" click in the last second still lands.
 *
 * Deactivation: the dean can set a faculty account Inactive, and dean or faculty
 * a student's. That ends every session the account holds on its next request,
 * so an open page signs out within one ping of partials.session-guard.
 *
 * Ending a session uses logoutCurrentDevice(), not logout(): logout() cycles
 * remember_token, which would end the newer session too.
 */
class EnforceSessionRules
{
    public const IDLE_SECONDS = 900;
    public const WARNING_SECONDS = 10;
    private const GRACE_SECONDS = 5;

    public const TOKEN_KEY = 'login_token';
    public const ACTIVITY_KEY = 'last_activity_at';
    public const ENDED_KEY = 'session_ended_reason';

    public const REASONS = [
        'idle' => [
            'title' => 'Session Expired',
            'message' => 'You were logged out because you were inactive for too long. Please log in again.',
        ],
        'replaced' => [
            'title' => 'Signed In Elsewhere',
            'message' => 'Your account was logged in on another device. Please log in again.',
        ],
        'deactivated' => [
            'title' => 'Account Deactivated',
            'message' => 'Your account has been deactivated. Contact your instructor or the dean.',
        ],
    ];

    /**
     * Faculty and student accounts set Inactive, and students archived at the end of
     * their term, can no longer sign in or stay signed in.
     */
    public static function isDeactivated($user): bool
    {
        return in_array($user->role, ['faculty', 'student'], true)
            && in_array($user->status, ['inactive', 'archived'], true);
    }

    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();
        if (!$user) {
            return $next($request);
        }

        $session = $request->session();

        // Signing out needs no alert, even from a deactivated account.
        if (self::isDeactivated($user) && !$request->routeIs('logout')) {
            return self::end($request, 'deactivated');
        }

        // A remember-me cookie from before this rule signs in without a session
        // that can show recent activity; the user has been away, so make them
        // sign in properly.
        if (Auth::viaRemember()) {
            return self::end($request, 'idle');
        }

        $token = $session->get(self::TOKEN_KEY);
        if ($token === null) {
            // Sessions opened before this rule existed adopt the account's
            // current token; the next sign-in anywhere replaces it.
            $session->put(self::TOKEN_KEY, (string) $user->getRememberToken());
        } elseif (!hash_equals((string) $user->getRememberToken(), (string) $token)) {
            return self::end($request, 'replaced');
        }

        $last = $session->get(self::ACTIVITY_KEY);
        if ($last !== null && now()->timestamp - (int) $last >= self::IDLE_SECONDS + self::WARNING_SECONDS + self::GRACE_SECONDS) {
            return self::end($request, 'idle');
        }

        if ($last === null || self::isInteraction($request)) {
            $session->put(self::ACTIVITY_KEY, now()->timestamp);
        }

        return $next($request);
    }

    /** Sign this session out only, then send the user to the login drawer with why. */
    public static function end(Request $request, string $reason): Response
    {
        $reason = isset(self::REASONS[$reason]) ? $reason : 'idle';

        if (Auth::check()) {
            Auth::guard('web')->logoutCurrentDevice();
        }
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        // Kept on the fresh guest session so other open tabs, whose next ping
        // finds no user, can still say why.
        $request->session()->put(self::ENDED_KEY, $reason);

        if (!self::isNavigation($request)) {
            return response()->json([
                'message' => self::REASONS[$reason]['message'],
                'session_ended' => $reason,
            ], 401);
        }

        // withErrors() flashes straight away, so it has to run after invalidate().
        // A deactivation also pops a SweetAlert on the landing page.
        return redirect()->to('/?login=1')
            ->withErrors(['email' => self::REASONS[$reason]['message']])
            ->with('error_title', self::REASONS[$reason]['title'])
            ->with('session_alert', $reason === 'deactivated' ? self::REASONS[$reason] : null);
    }

    private static function isInteraction(Request $request): bool
    {
        if ($request->routeIs('session.ping')) {
            return $request->boolean('active');
        }

        return self::isNavigation($request);
    }

    /**
     * A page load or form post the user made, as opposed to a script's request.
     * Browsers label these with Sec-Fetch-Mode; older ones that do not fall
     * back on the XHR/JSON headers.
     */
    private static function isNavigation(Request $request): bool
    {
        $mode = $request->headers->get('Sec-Fetch-Mode');
        if ($mode !== null) {
            return $mode === 'navigate';
        }

        return !$request->ajax() && !$request->wantsJson() && !$request->isJson();
    }
}
