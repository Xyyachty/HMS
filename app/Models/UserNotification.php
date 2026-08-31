<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * One in-app notification addressed to one user.
 *
 * Write through App\Support\Notifier — it owns recipient fan-out so the "who
 * should see this" rules stay in one place instead of every controller.
 */
class UserNotification extends Model
{
    /** Kept short and stable: the bell dropdown maps these to icons/colours. */
    public const STUDENT_ADDED = 'student_added';
    public const CLASS_OPENED = 'class_opened';
    public const TEAM_CREATED = 'team_created';
    public const TEAM_MEMBER_ADDED = 'team_member_added';
    public const TASK_ASSIGNED = 'task_assigned';
    public const TASK_SUBMITTED = 'task_submitted';
    public const TASK_FEEDBACK = 'task_feedback';
    public const SITE_PUBLISHED = 'site_published';
    public const COMPLAINT_FILED = 'complaint_filed';
    public const COMPLAINT_UPDATED = 'complaint_updated';
    public const TABLE_ASSIGNED = 'table_assigned';
    public const AMENITY_BOOKED = 'amenity_booked';
    public const CATERING_ORDER = 'catering_order';
    public const ROOM_INSPECTION = 'room_inspection';
    public const ROOM_READY = 'room_ready';
    public const CONCEPT_SUBMITTED = 'concept_submitted';
    public const CONCEPT_REVIEWED = 'concept_reviewed';

    /** Icon + accent per type, consumed by the dropdown partial. */
    public const STYLES = [
        self::STUDENT_ADDED => ['icon' => 'mdi:account-plus', 'accent' => 'sky'],
        self::CLASS_OPENED => ['icon' => 'mdi:door-open', 'accent' => 'violet'],
        self::TEAM_CREATED => ['icon' => 'mdi:account-group', 'accent' => 'indigo'],
        self::TEAM_MEMBER_ADDED => ['icon' => 'mdi:account-multiple-plus', 'accent' => 'indigo'],
        self::TASK_ASSIGNED => ['icon' => 'mdi:clipboard-text', 'accent' => 'amber'],
        self::TASK_SUBMITTED => ['icon' => 'mdi:clipboard-check', 'accent' => 'emerald'],
        self::TASK_FEEDBACK => ['icon' => 'mdi:comment-quote', 'accent' => 'rose'],
        self::SITE_PUBLISHED => ['icon' => 'mdi:web-check', 'accent' => 'emerald'],
        self::COMPLAINT_FILED => ['icon' => 'mdi:alert-octagon', 'accent' => 'rose'],
        self::COMPLAINT_UPDATED => ['icon' => 'mdi:clipboard-check-outline', 'accent' => 'emerald'],
        self::TABLE_ASSIGNED => ['icon' => 'mdi:table-chair', 'accent' => 'amber'],
        self::AMENITY_BOOKED => ['icon' => 'mdi:calendar-star', 'accent' => 'amber'],
        self::CATERING_ORDER => ['icon' => 'mdi:silverware-fork-knife', 'accent' => 'amber'],
        self::ROOM_INSPECTION => ['icon' => 'mdi:broom', 'accent' => 'amber'],
        self::ROOM_READY => ['icon' => 'mdi:bed-check', 'accent' => 'emerald'],
        self::CONCEPT_SUBMITTED => ['icon' => 'mdi:lightbulb-on', 'accent' => 'amber'],
        self::CONCEPT_REVIEWED => ['icon' => 'mdi:lightbulb-check', 'accent' => 'emerald'],
    ];

    protected $primaryKey = 'user_notification_id';

    protected $fillable = [
        'user_id',
        'actor_id',
        'type',
        'title',
        'body',
        'url',
        'read_at',
    ];

    protected $casts = [
        'read_at' => 'datetime',
        'created_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }

    public function actor()
    {
        return $this->belongsTo(User::class, 'actor_id', 'user_id');
    }

    public function scopeUnread(Builder $query): Builder
    {
        return $query->whereNull('read_at');
    }

    public function scopeForUser(Builder $query, int $userId): Builder
    {
        return $query->where('user_id', $userId);
    }

    public function isRead(): bool
    {
        return $this->read_at !== null;
    }

    /** Shape consumed by the bell dropdown's JSON endpoint. */
    public function toFeedArray(): array
    {
        $style = self::STYLES[$this->type] ?? ['icon' => 'mdi:bell-outline', 'accent' => 'slate'];

        // "id" is the dropdown's key for a notification, not the column name.
        return [
            'id' => $this->user_notification_id,
            'type' => $this->type,
            'title' => $this->title,
            'body' => $this->body,
            'url' => $this->url,
            'icon' => $style['icon'],
            'accent' => $style['accent'],
            'read' => $this->isRead(),
            'created_at' => optional($this->created_at)->format('M d, Y g:i A'),
            'created_at_human' => optional($this->created_at)->diffForHumans(),
        ];
    }
}
