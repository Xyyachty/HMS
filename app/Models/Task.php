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
