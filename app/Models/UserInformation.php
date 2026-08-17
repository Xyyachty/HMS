<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * The single table behind both kinds of account detail — see the
 * 2026_08_18_000000 migration, which merged `students` and `faculties` into it.
 *
 * Student and Faculty extend this class rather than replacing it. Each adds a global
 * scope on user_type, so Student::where(...) still sees only students and
 * Faculty::where(...) only faculty, and the two keep their own domain helpers and
 * relationships. Query this class directly only when a row's kind is genuinely
 * irrelevant (or unknown).
 */
class UserInformation extends Model
{
    public const TYPE_STUDENT = 'student';
    public const TYPE_FACULTY = 'faculty';

    protected $table = 'user_information';

    protected $primaryKey = 'user_information_id';

    /**
     * faculty_id is a self-reference: on a student row it is that student's faculty, and
     * on a faculty row it is null — the faculty's own id is user_information_id.
     */
    protected $fillable = [
        'user_type',
        'user_id',
        'faculty_id',
        'faculty_class_id',
        'student_number',
        'phone_number',
        'status',
        'block',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }

    public function isStudent(): bool
    {
        return $this->user_type === self::TYPE_STUDENT;
    }

    public function isFaculty(): bool
    {
        return $this->user_type === self::TYPE_FACULTY;
    }
}
