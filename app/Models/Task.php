<?php

namespace App\Models;

use App\Support\HotelConceptDesk;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Task extends Model
{
    use HasFactory;

    protected $primaryKey = 'task_id';

    protected $fillable = [
        'faculty_id',
        // The team this task was assigned to. Null only on rows that predate
        // per-team assignment; those stay visible to whoever holds the role.
        'group_name',
        'group_id',
        'student_id',
        'assigned_to',
        'role',
        // null for an ordinary assignment; HotelConceptDesk::TASK_KIND for the
        // seeded hotel concept task, which has its own submit and review path.
        'kind',
        'title',
        'description',
        // The four activities this task is worked through as, copied off the
        // checklist when it was assigned: [['text' => …, 'done' => bool], …].
        'activities',
        'due_date',
        'status',
        'feedback',
        'feedback_at',
        'feedback_by',
        'revision_count',
        // Template snapshots bracketing this task's last two submissions — the
        // Before/After sides of the faculty review comparison.
        'submitted_version_id',
        'previous_version_id',
    ];

    protected $casts = [
        // Timestamp, not date: faculty set the hour a task is due, and isPast()
        // has to respect it rather than treating everything as midnight.
        'due_date' => 'datetime',
        'feedback_at' => 'datetime',
        'revision_count' => 'integer',
        'activities' => 'array',
    ];

    /**
     * Whether this database has the `activities` column yet.
     *
     * The column arrives in a migration of its own, so between pulling the code
     * and running that migration a write carrying activities would be an INSERT
     * against a column that is not there. Answered once per request.
     */
    public static function supportsActivities(): bool
    {
        static $has = null;

        if ($has === null) {
            $has = \Illuminate\Support\Facades\Schema::hasColumn('tasks', 'activities');
        }

        return $has;
    }

    /**
     * The activities as a list of ['text' => string, 'done' => bool].
     *
     * Shaped here rather than trusted from the column: rows written before the
     * column existed hold null, and an older row could hold plain strings.
     *
     * @return list<array{text: string, done: bool}>
     */
    public function activityList(): array
    {
        $raw = is_array($this->activities) ? $this->activities : [];
        $out = [];

        foreach ($raw as $item) {
            if (is_string($item)) {
                $out[] = ['text' => $item, 'done' => false];
                continue;
            }
            if (is_array($item) && filled($item['text'] ?? null)) {
                $out[] = ['text' => (string) $item['text'], 'done' => (bool) ($item['done'] ?? false)];
            }
        }

        return $out;
    }

    /** How many of the activities are ticked. */
    public function activitiesDoneCount(): int
    {
        return count(array_filter($this->activityList(), fn ($a) => $a['done']));
    }

    /**
     * Whether the work may be submitted yet.
     *
     * A task carrying no activities — one assigned before they existed — is
     * submittable as it always was, rather than being locked by a list it never
     * had.
     */
    public function activitiesComplete(): bool
    {
        $list = $this->activityList();

        return $list === [] || $this->activitiesDoneCount() === count($list);
    }

    public function feedbackBy()
    {
        return $this->belongsTo(User::class, 'feedback_by', 'user_id');
    }

    /**
     * Sent back by faculty: the row is active again but carries feedback, so the
     * student sees what to fix. Avoids widening the status enum.
     */
    public function getNeedsRevisionAttribute(): bool
    {
        return $this->status === 'active' && filled($this->feedback);
    }

    public function faculty()
    {
        return $this->belongsTo(Faculty::class, 'faculty_id', 'user_information_id');
    }

    public function student()
    {
        return $this->belongsTo(Student::class, 'student_id', 'user_information_id');
    }

    public function assignedTo()
    {
        return $this->belongsTo(User::class, 'assigned_to', 'user_id');
    }

    protected static function booted(): void
    {
        static::saving(function (Task $task) {
            // Keep student_id in sync with assigned_to
            // (users.user_id → user_information.user_information_id)
            if ($task->assigned_to && !$task->student_id) {
                $task->student_id = Student::where('user_id', $task->assigned_to)->value('user_information_id');
            }

            if ($task->student_id && !$task->assigned_to) {
                $task->assigned_to = Student::whereKey($task->student_id)->value('user_id');
            }
        });
    }

    /**
     * Scope to only active tasks.
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * The tasks one team may read.
     *
     * Faculty assign to a single team, so a row states its team and is filtered on
     * it directly — the same way every hotel_* table scopes by group_name. Rows with
     * no team are the ones created before assignment was team-scoped; they carry no
     * submission and so no feedback, and stay visible to whoever holds the role
     * rather than disappearing from every dashboard at once. Delete this null branch
     * once those rows are gone and the scope becomes a plain equality.
     */
    public function scopeForTeam($query, ?string $groupName)
    {
        return $query->where(function ($q) use ($groupName) {
            $q->whereNull('group_name');

            if (filled($groupName)) {
                $q->orWhere('group_name', $groupName);
            }
        });
    }

    /** The one task that gates the rest of the simulation. */
    public function getIsHotelConceptAttribute(): bool
    {
        return $this->kind === HotelConceptDesk::TASK_KIND;
    }

    /**
     * The hotel concept heads every list — it is the team's first task.
     *
     * Priority alone would not do it: the orderings this is chained ahead of sort
     * by due_date first, and the concept task has none, which sorts last in
     * PostgreSQL.
     */
    public function scopeConceptFirst($query)
    {
        return $query->orderByRaw(
            "CASE WHEN kind = ? THEN 0 ELSE 1 END",
            [HotelConceptDesk::TASK_KIND]
        );
    }

    /**
     * Human-readable role label.
     */
    public function getRoleLabelAttribute(): string
    {
        return [
            'front_desk'             => 'Front Desk',
            'restaurant_management'  => 'Restaurant Management',
            'room_management'        => 'Room Management',
            'maintenance'            => 'Maintenance',
            'housekeeping'           => 'Housekeeping Services',
        ][$this->role] ?? $this->role;
    }
}
