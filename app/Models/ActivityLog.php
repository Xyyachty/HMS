<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Centralized activity log shared by dean, faculty and student portals.
 *
 * Write with ActivityLog::record(); read through App\Support\ActivityLogAccess
 * so authorization stays in one place.
 */
class ActivityLog extends Model
{
    use HasFactory;

    protected $primaryKey = 'activity_log_id';

    /** Only created_at is tracked — a log entry is never updated. */
    public const UPDATED_AT = null;

    protected $fillable = [
        'user_id',
        'role',
        'activity',
        'description',
    ];

    protected $casts = [
        'created_at' => 'datetime',
    ];

    /* ── Activity types ──────────────────────────────────────────────────
       Only important actions are recorded — never routine navigation. */
    public const LOGIN = 'login';
    public const LOGOUT = 'logout';
    public const ACCOUNT_CREATED = 'account_created';
    public const ACCOUNT_UPDATED = 'account_updated';
    public const ACCOUNT_DELETED = 'account_deleted';
    public const ACCOUNT_APPROVED = 'account_approved';
    public const TEAM_CREATED = 'team_created';
    public const TEAM_UPDATED = 'team_updated';
    public const STUDENT_ASSIGNED = 'student_assigned';
    public const ROLE_ASSIGNED = 'role_assigned';
    public const TASK_CREATED = 'task_created';
    public const TASK_UPDATED = 'task_updated';
    public const TASK_DELETED = 'task_deleted';
    public const TASK_SUBMITTED = 'task_submitted';
    public const WEBSITE_CUSTOMIZED = 'website_customized';
    public const TEMPLATE_RESTORED = 'template_restored';
    public const OUTPUT_UPLOADED = 'output_uploaded';
    public const EVALUATION_RECORDED = 'evaluation_recorded';
    public const REPORT_GENERATED = 'report_generated';
    public const PERMISSION_GRANTED = 'permission_granted';
    public const COMPLAINT_FILED = 'complaint_filed';
    public const COMPLAINT_RESOLVED = 'complaint_resolved';

    /** Human labels for the portals' activity tables. */
    public const LABELS = [
        self::LOGIN => 'Login',
        self::LOGOUT => 'Logout',
        self::ACCOUNT_CREATED => 'Account Created',
        self::ACCOUNT_UPDATED => 'Account Updated',
        self::ACCOUNT_DELETED => 'Account Deleted',
        self::ACCOUNT_APPROVED => 'Account Approved',
        self::TEAM_CREATED => 'Team Created',
        self::TEAM_UPDATED => 'Team Updated',
        self::STUDENT_ASSIGNED => 'Student Assigned',
        self::ROLE_ASSIGNED => 'Role Assigned',
        self::TASK_CREATED => 'Task Created',
        self::TASK_UPDATED => 'Task Updated',
        self::TASK_DELETED => 'Task Deleted',
        self::TASK_SUBMITTED => 'Task Submitted',
        self::WEBSITE_CUSTOMIZED => 'Website Customized',
        self::TEMPLATE_RESTORED => 'Template Restored',
        self::OUTPUT_UPLOADED => 'Output Uploaded',
        self::EVALUATION_RECORDED => 'Evaluation Recorded',
        self::REPORT_GENERATED => 'Report Generated',
        self::PERMISSION_GRANTED => 'Permission Granted',
        self::COMPLAINT_FILED => 'Complaint Filed',
        self::COMPLAINT_RESOLVED => 'Complaint Resolved',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }

    /**
     * Record one important action. Never throws — a logging failure must not
     * break the action the user actually asked for.
     */
    public static function record(?User $user, string $activity, string $description = ''): ?self
    {
        if (!$user || !$user->exists) {
            return null;
        }

        try {
            return static::create([
                'user_id' => $user->user_id,
                'role' => static::resolveRole($user),
                'activity' => $activity,
                'description' => mb_substr(trim($description), 0, 500),
            ]);
        } catch (\Throwable $e) {
            report($e);
            return null;
        }
    }

    /** Convenience wrapper for the currently authenticated user. */
    public static function recordFor(string $activity, string $description = ''): ?self
    {
        return static::record(auth()->user(), $activity, $description);
    }

    /** Roles are stored on users.role, but fall back to the linked profile. */
    public static function resolveRole(User $user): string
    {
        $role = trim((string) ($user->role ?? ''));
        if ($role !== '') {
            return $role;
        }

        if ($user->relationLoaded('faculty') ? $user->faculty : $user->faculty()->exists()) {
            return 'faculty';
        }

        if ($user->relationLoaded('student') ? $user->student : $user->student()->exists()) {
            return 'student';
        }

        return 'user';
    }

    public function getActivityLabelAttribute(): string
    {
        return self::LABELS[$this->activity] ?? ucwords(str_replace('_', ' ', (string) $this->activity));
    }

    /** Shape used by every portal's activity table / modal. */
    public function toPortalArray(): array
    {
        // The JSON key stays "id": this is the portal's wire format, read by the
        // activity tables' JavaScript, and it does not follow the column naming.
        return [
            'id' => $this->activity_log_id,
            'activity' => $this->activity,
            'activity_label' => $this->activity_label,
            'description' => $this->description,
            'role' => $this->role,
            'created_at' => optional($this->created_at)->format('M d, Y g:i A'),
            'created_at_human' => optional($this->created_at)->diffForHumans(),
        ];
    }
}
