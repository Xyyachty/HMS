<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TeamTemplateEditGrant extends Model
{
    protected $fillable = [
        'faculty_id',
        'group_name',
        'student_id',
        'role',
        'granted_by',
    ];

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function granter()
    {
        return $this->belongsTo(User::class, 'granted_by');
    }
}
