<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Task extends Model
{
    use HasFactory;

    protected $fillable = [
        'faculty_id',
        'student_id',
        'assigned_to',
        'role',
        'title',
        'description',
        'due_date',
        'priority',
        'status',
    ];

    protected $casts = [
        'due_date' => 'date',
    ];

    public function faculty()
    {
        return $this->belongsTo(Faculty::class);
    }

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function assignedTo()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    protected static function booted(): void
    {
        static::saving(function (Task $task) {
            // Keep student_id in sync with assigned_to (users.id → students.id)
            if ($task->assigned_to && !$task->student_id) {
                $task->student_id = Student::where('user_id', $task->assigned_to)->value('id');
            }

            if ($task->student_id && !$task->assigned_to) {
                $task->assigned_to = Student::where('id', $task->student_id)->value('user_id');
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
