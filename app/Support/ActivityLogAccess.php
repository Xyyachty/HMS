<?php

namespace App\Support;

use App\Models\ActivityLog;
use App\Models\Student;
use App\Models\StudentGroup;
use App\Models\User;
use Illuminate\Support\Collection;

/**
 * Single authorization gate for reading the centralized activity_logs table.
 *
 * Dean    — any user (the dean administers every faculty).
 * Faculty — themselves, plus students that belong to their own faculty.
 * Student — themselves, plus members of their own group. Never outside it.
 */
class ActivityLogAccess
{
    public const DEFAULT_LIMIT = 100;

    public static function canView(?User $viewer, ?User $target): bool
    {
        if (!$viewer || !$target) {
            return false;
        }

        if ((int) $viewer->id === (int) $target->id) {
            return true;
        }

        $role = ActivityLog::resolveRole($viewer);

        if ($role === 'dean') {
            return true;
        }

        if ($role === 'faculty') {
            return static::facultyManages($viewer, $target);
        }

        // Students may read their own history and that of their own group mates.
        return static::sharesGroup($viewer, $target);
    }

    /** True when both users sit in the same group under the same faculty. */
    public static function sharesGroup(User $viewer, User $target): bool
    {
        $viewerStudent = $viewer->student;
        $targetStudent = $target->student;

        if (!$viewerStudent || !$targetStudent) {
            return false;
        }

        $membership = StudentGroup::where('student_id', $viewerStudent->id)->first();
        if (!$membership) {
            return false;
        }

        return StudentGroup::where('group_name', $membership->group_name)
            ->where('faculty_id', $membership->faculty_id)
            ->where('student_id', $targetStudent->id)
            ->exists();
    }

    /** True when $target is a student enrolled under $viewer's faculty. */
    public static function facultyManages(User $viewer, User $target): bool
    {
        $facultyId = $viewer->faculty?->id;
        if (!$facultyId) {
            return false;
        }

        $student = $target->student;
        if (!$student) {
            return false;
        }

        if ((int) $student->faculty_id === (int) $facultyId) {
            return true;
        }

        // Also honour group membership, which is the faculty's real unit of work.
        return StudentGroup::where('faculty_id', $facultyId)
            ->where('student_id', $student->id)
            ->exists();
    }

    /** Logs for one user, newest first, already shaped for the portals. */
    public static function logsFor(User $target, int $limit = self::DEFAULT_LIMIT): Collection
    {
        return ActivityLog::where('user_id', $target->id)
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->limit(max(1, $limit))
            ->get()
            ->map(fn (ActivityLog $log) => $log->toPortalArray())
            ->values();
    }

    /** Display name used in the activity modal header. */
    public static function displayName(User $user): string
    {
        $name = trim(implode(' ', array_filter([
            $user->last_name,
            $user->first_name,
            $user->middle_name,
        ])));

        return $name !== '' ? $name : ($user->name ?? 'User');
    }

    /**
     * Every user id a faculty member is allowed to inspect — used to keep the
     * team modals from offering buttons that would 403.
     */
    public static function studentUserIdsForFaculty(User $viewer): array
    {
        $facultyId = $viewer->faculty?->id;
        if (!$facultyId) {
            return [];
        }

        return Student::where('faculty_id', $facultyId)
            ->pluck('user_id')
            ->filter()
            ->map(fn ($id) => (int) $id)
            ->values()
            ->all();
    }
}
