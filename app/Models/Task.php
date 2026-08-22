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
        'student_id',
        'assigned_to',
        'role',
        // null for an ordinary assignment; HotelConceptDesk::TASK_KIND for the
        // seeded hotel concept task, which has its own submit and review path.
        'kind',
        'title',
        'description',
        'due_date',
        'priority',
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
    ];

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
     * Highest priority first. Written as a CASE rather than MySQL's FIELD(),
     * which does not exist in PostgreSQL.
     */
    public function scopeOrderByPriority($query)
    {
        return $query->orderByRaw(
            "CASE priority WHEN 'high' THEN 1 WHEN 'medium' THEN 2 WHEN 'low' THEN 3 ELSE 4 END"
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
