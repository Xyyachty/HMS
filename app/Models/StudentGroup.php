<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Student;
use App\Models\Faculty;

class StudentGroup extends Model
{
    use HasFactory;

    protected $primaryKey = 'student_group_id';

    protected $fillable = [
        'group_name',
        'faculty_id',
        'group_id',
        'student_id',
        'role',
    ];

    public function student()
    {
        return $this->belongsTo(Student::class, 'student_id', 'user_information_id');
    }

    public function faculty()
    {
        return $this->belongsTo(Faculty::class, 'faculty_id', 'user_information_id');
    }

    public function group()
    {
        return $this->belongsTo(Group::class, 'group_id', 'group_id');
    }

    public function roles()
    {
        return $this->hasMany(StudentGroupRole::class, 'student_group_id', 'student_group_id');
    }

    /**
     * Get all role values for this group membership
     */
    public function getRoleValuesAttribute()
    {
        return $this->roles->pluck('role')->toArray();
    }

    /**
     * Check if this group membership has a specific role
     */
    public function hasRole($role)
    {
        return $this->roles->contains('role', $role);
    }
}
