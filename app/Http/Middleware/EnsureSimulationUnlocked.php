<?php

namespace App\Http\Middleware;

use App\Support\SimulationPhase;
use App\Support\StudentGroupSync;
use Closure;
use Illuminate\Http\Request;

/**
 * Keeps a team out of the Hotel Simulation until faculty has approved every
 * required customization task and confirmed the simulation roles.
 *
 * Gates the Staff Tools pages (the ones on students.builder.ops-shell) by route
 * name. The JSON endpoints behind them are left alone: the Customization editor
 * reads and writes rooms, menus and amenities through the same ones.
 */
class EnsureSimulationUnlocked
{
    public const PAGES = [
        'students.frontdesk.verify-guest',
        'students.frontdesk.walk-in',
        'students.frontdesk.dine-in',
        'students.frontdesk.room-service',
        'students.frontdesk.reports',
        'students.frontdesk.amenities',
        'students.frontdesk.complaints',
        'students.roommanagement.manage',
        'students.roommanagement.complaints',
        'students.restaurant.manage',
        'students.restaurant.reports',
        'students.restaurant.complaints',
        'students.housekeeping.inspections',
        'students.housekeeping.addons',
        'students.housekeeping.amenities',
        'students.housekeeping.complaints',
        'students.maintenance.complaints',
    ];

    public function handle(Request $request, Closure $next)
    {
        if (!in_array($request->route()?->getName(), self::PAGES, true)) {
            return $next($request);
        }

        $student = $request->user()?->student;
        $membership = StudentGroupSync::membershipForStudent($student?->user_information_id);

        if ($membership && !SimulationPhase::canStart($membership)) {
            return redirect()
                ->route('students.dashboard', ['section' => 'tasks'])
                ->with('simulation_locked', true);
        }

        return $next($request);
    }
}
