<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StudentGroupRole extends Model
{
    use HasFactory;

    protected $fillable = [
        'student_group_id',
        'role',
    ];

    public function studentGroup()
    {
        return $this->belongsTo(StudentGroup::class);
    }
}
