<?php

namespace App\Support;

use App\Models\GroupSettings;
use App\Models\StudentGroup;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;

class StudentGroupSync
{
    /** Consider a student online if seen within this many seconds. */
    public const PRESENCE_WINDOW_SECONDS = 60;

    public const TEMPLATE_EDIT_ROLE = 'front_desk';

    public static function groupPresenceKey(int $facultyId, string $groupName): string
    {
        return 'group_presence_' . $facultyId . '_' . md5($groupName);
    }

    public static function heartbeat(User $user, ?StudentGroup $membership = null): void
    {
        // Durable presence (shared across all PHP processes / browsers)
        $user->forceFill(['last_seen_at' => now()])->saveQuietly();

        // Also keep a group map in cache as a fast path
        if ($membership && $membership->faculty_id && $membership->group_name) {
            $key = self::groupPresenceKey((int) $membership->faculty_id, (string) $membership->group_name);
            $map = Cache::get($key, []);
            if (!is_array($map)) {
                $map = [];
            }
            $map[(string) $user->id] = now()->timestamp;
            // Drop stale entries
            $cutoff = now()->timestamp - self::PRESENCE_WINDOW_SECONDS;
            $map = array_filter($map, fn ($ts) => (int) $ts >= $cutoff);
            Cache::put($key, $map, self::PRESENCE_WINDOW_SECONDS * 2);
        }
    }

    /**
     * @param  iterable<int>  $userIds
     * @return array<string, bool>  string keys so JSON stays stable
     */
    public static function onlineMap(iterable $userIds, ?StudentGroup $membership = null): array
    {
        $ids = [];
        foreach ($userIds as $userId) {
            $id = (int) $userId;
            if ($id > 0) {
                $ids[] = $id;
            }
        }
        $ids = array_values(array_unique($ids));

        $map = [];
        foreach ($ids as $id) {
            $map[(string) $id] = false;
        }

        if ($ids === []) {
            return $map;
        }

        // 1) Cache group map
        if ($membership && $membership->faculty_id && $membership->group_name) {
            $cached = Cache::get(
                self::groupPresenceKey((int) $membership->faculty_id, (string) $membership->group_name),
                []
            );
            if (is_array($cached)) {
                $cutoff = now()->timestamp - self::PRESENCE_WINDOW_SECONDS;
                foreach ($ids as $id) {
                    $ts = (int) ($cached[(string) $id] ?? $cached[$id] ?? 0);
                    if ($ts >= $cutoff) {
                        $map[(string) $id] = true;
                    }
                }
            }
        }

        // 2) Database last_seen_at (source of truth across sessions)
        $cutoffAt = Carbon::now()->subSeconds(self::PRESENCE_WINDOW_SECONDS);
        $seen = User::query()
            ->whereIn('id', $ids)
            ->where('last_seen_at', '>=', $cutoffAt)
            ->pluck('id')
            ->all();

        foreach ($seen as $id) {
            $map[(string) (int) $id] = true;
        }

        return $map;
    }

    public static function membershipForStudent(?int $studentId): ?StudentGroup
    {
        if (!$studentId) {
            return null;
        }

        return StudentGroup::with('roles')->where('student_id', $studentId)->first();
    }

    public static function roleKeys(StudentGroup $membership): array
    {
        return $membership->roles->pluck('role')->filter()->values()->all();
    }

    public static function canEditTemplate(StudentGroup $membership): bool
    {
        // Legacy helper — Front Desk module only
        $user = auth()->user();
        if (!$user) {
            return false;
        }

        return \App\Support\HotelTemplateBuilder::canEdit($user, $membership, self::TEMPLATE_EDIT_ROLE);
    }

    public static function canEditRoleTemplate(StudentGroup $membership, string $role): bool
    {
        $user = auth()->user();
        if (!$user) {
            return false;
        }

        return \App\Support\HotelTemplateBuilder::canEdit($user, $membership, $role);
    }

    public static function settingsFor(StudentGroup $membership): ?GroupSettings
    {
        return GroupSettings::where('group_name', $membership->group_name)
            ->where('faculty_id', $membership->faculty_id)
            ->first();
    }
}
