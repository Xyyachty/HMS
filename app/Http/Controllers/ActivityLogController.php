<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Support\ActivityLogAccess;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * One controller serves every portal's activity view. The route prefix decides
 * which portal is asking; ActivityLogAccess decides what it may see.
 */
class ActivityLogController extends Controller
{
    /** Dean & faculty: read one member's activity log. */
    public function forUser(Request $request, User $user): JsonResponse
    {
        $viewer = $request->user();

        if (!ActivityLogAccess::canView($viewer, $user)) {
            return response()->json([
                'error' => 'You are not authorized to view this member\'s activity.',
            ], 403);
        }

        return response()->json([
            'user' => [
                'id' => $user->user_id,
                'name' => ActivityLogAccess::displayName($user),
                'role' => \App\Models\ActivityLog::resolveRole($user),
            ],
            'logs' => ActivityLogAccess::logsFor($user, (int) $request->query('limit', ActivityLogAccess::DEFAULT_LIMIT)),
        ]);
    }

    /** Students: own activity only — the target is always the caller. */
    public function mine(Request $request): JsonResponse
    {
        $viewer = $request->user();

        if (!$viewer) {
            return response()->json(['error' => 'Not authenticated.'], 401);
        }

        return response()->json([
            'user' => [
                'id' => $viewer->user_id,
                'name' => ActivityLogAccess::displayName($viewer),
                'role' => \App\Models\ActivityLog::resolveRole($viewer),
            ],
            'logs' => ActivityLogAccess::logsFor($viewer, (int) $request->query('limit', ActivityLogAccess::DEFAULT_LIMIT)),
        ]);
    }
}
