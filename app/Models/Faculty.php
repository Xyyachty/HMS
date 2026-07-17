<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\StudentGroup;

class Faculty extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'phone_number',
        'status',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function studentGroups()
    {
        return $this->hasMany(StudentGroup::class);
    }

    public function classes()
    {
        return $this->hasMany(FacultyClass::class);
    }

    public function students()
    {
        return $this->hasMany(Student::class);
    }
}
