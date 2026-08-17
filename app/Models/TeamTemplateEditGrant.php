<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TeamTemplateEditGrant extends Model
{
    protected $primaryKey = 'team_template_edit_grant_id';

    protected $fillable = [
        'faculty_id',
        'group_name',
        'group_id',
        'student_id',
        'role',
        'granted_by',
    ];

    public function student()
    {
        return $this->belongsTo(Student::class, 'student_id', 'user_information_id');
    }

    public function granter()
    {
        return $this->belongsTo(User::class, 'granted_by', 'user_id');
    }
}
