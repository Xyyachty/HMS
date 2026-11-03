<?php

namespace App\Support;

use App\Models\AcademicTerm;
use App\Models\ActivityLog;
use App\Models\FacultyClass;
use App\Models\Student;
use App\Models\User;
use Illuminate\Support\Facades\DB;

/**
 * Every write to a school term goes through here: starting one, ending it (which
 * archives that batch of students), and bringing one student back.
 *
 * Only the dean calls these. Faculty keep their own Active / Inactive switch for one
 * student at a time; ending a term is for the whole batch at once, so the records of
 * every block close on the same day.
 */
class AcademicTermDesk
{
    /** @throws \RuntimeException when a term is already running */
    public static function start(string $name, ?string $endsOn): AcademicTerm
    {
        return DB::transaction(function () use ($name, $endsOn) {
            if (AcademicTerm::whereNull('ended_at')->lockForUpdate()->exists()) {
                throw new \RuntimeException('A term is already running. End it before starting the next one.');
            }

            return AcademicTerm::create(['name' => trim($name), 'ends_on' => $endsOn ?: null]);
        });
    }

    /**
     * Archives every current student with $term and closes it. Each student keeps their
     * account, tasks, team and hotel data; they lose the sign-in and their seat, and they
     * drop out of the working screens (see Student::CURRENT_SCOPE). Their block goes back
     * to empty, ready for the next batch.
     *
     * @return int how many students were archived
     * @throws \RuntimeException when the term has already ended
     */
    public static function end(AcademicTerm $term, ?User $actor): int
    {
        return DB::transaction(function () use ($term, $actor) {
            $term = AcademicTerm::whereKey($term->getKey())->lockForUpdate()->first();
            if (!$term || $term->ended_at !== null) {
                throw new \RuntimeException('This term has already ended.');
            }

            $students = Student::with('facultyClass.faculty')->get();

            foreach ($students as $student) {
                $student->forceFill([
                    'academic_term_id' => $term->academic_term_id,
                    // "Block B" as it read on the day, since the seat is given back.
                    'archived_block'   => $student->facultyClass?->name,
                    'faculty_class_id' => null,
                ])->save();
            }

            User::whereIn('user_id', $students->pluck('user_id')->filter())->update(['status' => 'archived']);

            DB::table('student_groups')
                ->whereIn('student_id', $students->pluck('user_information_id'))
                ->whereNull('archived_at')
                ->update(['archived_at' => now()]);

            // Empty blocks fold back to each faculty's first one, open for the new batch.
            foreach ($students->pluck('faculty_id')->filter()->unique() as $facultyId) {
                FacultyClass::reconcileForFaculty((int) $facultyId);
            }

            $term->update(['ended_at' => now(), 'ended_by' => $actor?->name]);

            ActivityLog::record(
                $actor,
                ActivityLog::ACCOUNT_UPDATED,
                'Ended the term ' . $term->name . ' and archived ' . $students->count() . ' student(s).'
            );

            return $students->count();
        });
    }

    /**
     * Brings one archived student into the current batch: a retake, or a student
     * archived by mistake. They get a seat in their faculty's open block and can sign
     * in again. Their old team stays archived with the term; the faculty puts them on
     * a new one.
     *
     * @throws \RuntimeException when the student is not archived
     */
    public static function restore(Student $student, ?User $actor): Student
    {
        if ($student->academic_term_id === null) {
            throw new \RuntimeException('This student is not archived.');
        }

        return DB::transaction(function () use ($student, $actor) {
            $seat = $student->faculty_id ? FacultyClass::claimSeat((int) $student->faculty_id) : null;

            $student->forceFill([
                'academic_term_id' => null,
                'archived_block'   => null,
                'faculty_class_id' => $seat?->faculty_class_id,
            ])->save();

            $student->user?->forceFill(['status' => 'active'])->save();

            ActivityLog::record(
                $actor,
                ActivityLog::ACCOUNT_UPDATED,
                'Restored archived student ' . ($student->user?->name ?? $student->student_number) . '.'
            );

            return $student;
        });
    }
}
