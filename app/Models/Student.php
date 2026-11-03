<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * A student row of user_information. The primary key is user_information_id — students
 * and faculty share one table and one key space, so there is no students.student_id
 * any more. A column named student_id elsewhere (tasks, student_groups,
 * team_template_edit_grants) still means "the student this row belongs to" and now
 * points at user_information_id.
 *
 * student_number is the school-issued number shown on rosters and used for bulk import.
 */
class Student extends UserInformation
{
    use HasFactory;

    /** A student ID is the school number: digits only, at most 10. */
    public const NUMBER_PATTERN = '/^\d{1,10}$/';

    /**
     * Global scope that leaves out students archived at the end of a term. Every working
     * screen (rosters, team building, counts) reads the current batch only; the archive
     * screens and Restore lift it with withoutGlobalScope(Student::CURRENT_SCOPE).
     */
    public const CURRENT_SCOPE = 'current';

    protected static function booted(): void
    {
        static::addGlobalScope('student', function (Builder $query) {
            // Qualified through the model rather than hardcoded: a self-referencing
            // relation (Student::faculty) aliases the table, and getTable() follows it.
            $query->where(
                $query->getModel()->getTable() . '.user_type',
                UserInformation::TYPE_STUDENT
            );
        });

        static::addGlobalScope(self::CURRENT_SCOPE, function (Builder $query) {
            $query->whereNull($query->getModel()->getTable() . '.academic_term_id');
        });

        static::creating(function (Student $student) {
            $student->user_type = UserInformation::TYPE_STUDENT;
        });
    }

    public function faculty()
    {
        return $this->belongsTo(Faculty::class, 'faculty_id', 'user_information_id');
    }

    public function facultyClass()
    {
        return $this->belongsTo(FacultyClass::class, 'faculty_class_id', 'faculty_class_id');
    }

    /** The term this student was archived with; null while they are in the current batch. */
    public function term()
    {
        return $this->belongsTo(AcademicTerm::class, 'academic_term_id', 'academic_term_id');
    }

    public function tasks()
    {
        return $this->hasMany(Task::class, 'student_id', 'user_information_id');
    }
}
