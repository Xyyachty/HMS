<?php

namespace App\Http\Controllers;

use App\Models\UserNotification;
use Illuminate\Http\Request;

/**
 * JSON backing for the notification bell. Shared by all three portals — the
 * dropdown partial is identical everywhere, only the rows differ.
 *
 * A user can only ever touch their own rows: every query is scoped by
 * auth()->id(), so there is no id to guess your way into.
 */
class NotificationController extends Controller
{
    private const FEED_LIMIT = 25;

    /** Newest notifications plus the badge count. */
    public function index(Request $request)
    {
        $userId = (int) auth()->id();

        $notifications = UserNotification::forUser($userId)
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->limit(self::FEED_LIMIT)
            ->get()
            ->map(fn (UserNotification $n) => $n->toFeedArray())
            ->values();

        return response()->json([
            'notifications' => $notifications,
            'unread_count' => UserNotification::forUser($userId)->unread()->count(),
        ]);
    }

    /** Badge-only poll — cheaper than pulling the whole feed on a timer. */
    public function unreadCount()
    {
        return response()->json([
            'unread_count' => UserNotification::forUser((int) auth()->id())->unread()->count(),
        ]);
    }

    public function markRead($id)
    {
        $userId = (int) auth()->id();

        $updated = UserNotification::forUser($userId)
            ->whereKey($id)
            ->unread()
            ->update(['read_at' => now()]);

        if ($updated === 0 && !UserNotification::forUser($userId)->whereKey($id)->exists()) {
            return response()->json(['error' => 'Notification not found.'], 404);
        }

        return response()->json([
            'success' => true,
            'unread_count' => UserNotification::forUser($userId)->unread()->count(),
        ]);
    }

    public function markAllRead()
    {
        $userId = (int) auth()->id();

        UserNotification::forUser($userId)->unread()->update(['read_at' => now()]);

        return response()->json(['success' => true, 'unread_count' => 0]);
    }
}
