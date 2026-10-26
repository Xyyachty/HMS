<?php

namespace App\Support;

use App\Models\StudentGroup;
use App\Models\Task;
use App\Models\User;
use App\Models\UserNotification;

/**
 * Where a bell notification takes its reader, settled when it is clicked.
 *
 * The stored url is the starting point, checked two ways first:
 *   - it must be in the reader's own portal. Dean rows used to carry faculty
 *     links, which the role middleware refused.
 *   - the task or team it names must still exist and be the reader's to see,
 *     so a stale notification says so instead of opening an unrelated page.
 * Rows written before deep links pointed at a bare dashboard; those get the
 * page that type of notification is about.
 */
class NotificationTarget
{
    private const PORTAL_PREFIX = ['dean' => '/dean', 'faculty' => '/faculty', 'student' => '/students'];

    private const TASK_GONE = 'This task no longer exists. It may have been removed.';
    private const TEAM_GONE = 'This team no longer exists. It may have been removed or renamed.';

    /** @return array{url: string}|array{error: string} */
    public static function resolve(UserNotification $notification, User $user): array
    {
        $portal = $user->portal();
        if ($portal === null) {
            return ['error' => 'This notification has no page to open.'];
        }

        $path = self::path((string) $notification->url);
        parse_str((string) parse_url((string) $notification->url, PHP_URL_QUERY), $query);

        $ownPortal = $notification->url && str_starts_with($path, self::PORTAL_PREFIX[$portal]);
        if (!$ownPortal || ($query === [] && $path === self::path(route(self::DASHBOARDS[$portal])))) {
            return ['url' => self::fallback((string) $notification->type, $portal)];
        }

        if (preg_match('#^/dean/tasks/(\d+)/review$#', $path, $m) && !Task::whereKey($m[1])->exists()) {
            return ['error' => self::TASK_GONE];
        }
        if (isset($query['review']) && !self::facultyHasTask($user, (int) $query['review'])) {
            return ['error' => self::TASK_GONE];
        }
        if (isset($query['task']) && !self::studentHasTask($user, (int) $query['task'])) {
            return ['error' => self::TASK_GONE];
        }
        if (isset($query['team'])) {
            // A faculty only ever reads their own teams; a dean link names the faculty.
            $facultyId = $portal === 'faculty'
                ? (int) $user->faculty?->user_information_id
                : (int) ($query['faculty'] ?? 0);
            $exists = StudentGroup::where('faculty_id', $facultyId)
                ->where('group_name', (string) $query['team'])
                ->exists();
            if (!$exists) {
                return ['error' => self::TEAM_GONE];
            }
        }

        // Rebuilt on this host, so a row written under another one still opens here.
        return ['url' => url($path) . ($query ? '?' . http_build_query($query) : '')];
    }

    private const DASHBOARDS = [
        'dean' => 'dean.dashboard',
        'faculty' => 'faculty.dashboard',
        'student' => 'students.dashboard',
    ];

    /** The page a notification of this type is about, for rows with no better link. */
    private static function fallback(string $type, string $portal): string
    {
        $student = [
            UserNotification::TASK_ASSIGNED => ['section' => 'tasks'],
            UserNotification::TASK_FEEDBACK => ['section' => 'tasks'],
            UserNotification::SIMULATION_ROLES => ['section' => 'tasks'],
            UserNotification::TEAM_CREATED => ['section' => 'group'],
            UserNotification::TEAM_MEMBER_ADDED => ['section' => 'group'],
            UserNotification::CONCEPT_REVIEWED => ['section' => 'group', 'focus' => 'concepts'],
        ];

        $url = match ($portal) {
            'student' => isset($student[$type]) ? route('students.dashboard', $student[$type]) : null,
            'faculty' => match ($type) {
                UserNotification::STUDENT_ADDED, UserNotification::CLASS_OPENED => route('faculty.students'),
                UserNotification::TASK_SUBMITTED, UserNotification::CONCEPT_SUBMITTED,
                UserNotification::SITE_PUBLISHED, UserNotification::TEAM_CREATED,
                UserNotification::TEAM_MEMBER_ADDED => route('faculty.role', ['tab' => 'teams']),
                default => null,
            },
            'dean' => match ($type) {
                UserNotification::STUDENT_ADDED => route('dean.users', ['tab' => 'student']),
                UserNotification::CLASS_OPENED => route('dean.users', ['tab' => 'faculty']),
                UserNotification::TEAM_CREATED, UserNotification::TASK_ASSIGNED,
                UserNotification::TASK_SUBMITTED => route('dean.faculties'),
                default => null,
            },
        };

        return $url ?? route(self::DASHBOARDS[$portal]);
    }

    private static function facultyHasTask(User $user, int $taskId): bool
    {
        return Task::whereKey($taskId)
            ->where('faculty_id', (int) $user->faculty?->user_information_id)
            ->exists();
    }

    private static function studentHasTask(User $user, int $taskId): bool
    {
        $studentId = $user->student?->user_information_id;

        return Task::whereKey($taskId)
            ->where(fn ($q) => $q->where('assigned_to', $user->user_id)
                ->when($studentId, fn ($q) => $q->orWhere('student_id', $studentId)))
            ->exists();
    }

    /** The url's path inside the app, without any folder the app is served from. */
    private static function path(string $url): string
    {
        $path = '/' . ltrim((string) parse_url($url, PHP_URL_PATH), '/');
        $base = rtrim((string) parse_url(url('/'), PHP_URL_PATH), '/');

        return $base !== '' && str_starts_with($path, $base . '/') ? substr($path, strlen($base)) : $path;
    }
}
