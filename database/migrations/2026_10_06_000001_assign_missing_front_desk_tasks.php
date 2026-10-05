<?php

use App\Support\TaskChecklist;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Hands Front Desk students the checklist tasks they were never given.
 *
 * A team's tasks are copies made when faculty ticked them, so a team assigned
 * its Front Desk set before Hotel Experiences, the Footer and Hotel Highlights
 * existed holds FD TASK 1-4 and 6 only. Every student already holding at least
 * one numbered Front Desk task gets the ones missing, with the faculty, team
 * and due date of the tasks they already hold, steps unticked - what ticking
 * Task 01 again on Create Task would have written. A student holding only the
 * hotel concept has not been given the set yet and is left to faculty.
 */
return new class extends Migration
{
    public function up(): void
    {
        $checklist = array_values(array_filter(
            TaskChecklist::forRole('front_desk'),
            fn ($task) => TaskChecklist::taskNumber($task['title'], 'front_desk') !== null
        ));
        $numbered = array_map(fn ($task) => mb_strtolower($task['title']), $checklist);

        $rows = DB::table('tasks')->where('role', 'front_desk')->orderBy('task_id')->get();
        $hasActivities = Schema::hasColumn('tasks', 'activities');

        foreach ($rows->groupBy(fn ($row) => $row->faculty_id . '|' . $row->group_name . '|' . $row->student_id) as $held) {
            $model = $held->last(fn ($row) => in_array(mb_strtolower($row->title), $numbered, true));
            if (!$model) {
                continue;
            }
            $titles = $held->map(fn ($row) => mb_strtolower($row->title))->all();

            foreach ($checklist as $task) {
                if (in_array(mb_strtolower($task['title']), $titles, true)) {
                    continue;
                }
                $row = [
                    'faculty_id' => $model->faculty_id,
                    'group_name' => $model->group_name,
                    'group_id' => $model->group_id,
                    'student_id' => $model->student_id,
                    'assigned_to' => $model->assigned_to,
                    'role' => 'front_desk',
                    'title' => $task['title'],
                    'description' => $task['description'],
                    'due_date' => $model->due_date,
                    'status' => 'active',
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
                if ($hasActivities) {
                    $row['activities'] = json_encode(array_map(
                        fn ($text) => ['text' => $text, 'done' => false],
                        $task['activities']
                    ));
                }
                DB::table('tasks')->insert($row);
            }
        }
    }

    public function down(): void
    {
        // Left in place: by then a student may have started them.
    }
};
