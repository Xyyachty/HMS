<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class Student extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'faculty_id',
        'faculty_class_id',
        'student_id',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function faculty()
    {
        return $this->belongsTo(Faculty::class);
    }

    public function facultyClass()
    {
        return $this->belongsTo(FacultyClass::class, 'faculty_class_id');
    }

    public function tasks()
    {
        return $this->hasMany(Task::class);
    }
}
