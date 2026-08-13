<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class Student extends Model
{
    use HasFactory;

    protected $primaryKey = 'student_id';

    /**
     * student_number is the school-issued number shown on rosters and used for bulk
     * import. It used to be called student_id, which collided with the name every other
     * table uses for "foreign key to students" — and with this table's own key.
     */
    protected $fillable = [
        'user_id',
        'faculty_id',
        'faculty_class_id',
        'student_number',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }

    public function faculty()
    {
        return $this->belongsTo(Faculty::class, 'faculty_id', 'faculty_id');
    }

    public function facultyClass()
    {
        return $this->belongsTo(FacultyClass::class, 'faculty_class_id', 'faculty_class_id');
    }

    public function tasks()
    {
        return $this->hasMany(Task::class, 'student_id', 'student_id');
    }
}
