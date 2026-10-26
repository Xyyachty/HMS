<?php

use App\Support\TaskChecklist;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Room Management now has exactly five tasks, RM TASK 1-5, all on the Rooms
 * page (see TaskChecklist's 'room_management' list).
 *
 * Every student holding the old Room Management tasks loses them - none
 * had been completed when this ran, and the old follow-up "Detail Every Room
 * Category" is RM TASK 3 now - and is given the five new ones with the faculty,
 * team and due date of the tasks they held, steps unticked: what ticking
 * Task 02 on Create Task would write. The rooms and categories are untouched.
 */
return new class extends Migration
{
    private const RETIRED_TITLES = [
        'Create Your Room Categories',
        'Build Your Room Types',
        'Photograph and Price Every Room',
        'Style the Rooms Page',
        'List What Each Room Includes',
        'Write the Rooms Page Introduction',
        'Name the Category Tabs',
        'Set the Room Booking Popup',
        'Check the Rooms Page on a Phone',
        'Detail Every Room Category',
    ];

    public function up(): void
    {
        $checklist = TaskChecklist::forRole('room_management');
        $current = array_map(fn ($task) => mb_strtolower($task['title']), $checklist);
        $website = array_merge($current, array_map('mb_strtolower', self::RETIRED_TITLES));

        // Whoever held Room Management website work before the change, one row each.
        $holders = DB::table('tasks')
            ->where('role', 'room_management')
            ->orderBy('task_id')
            ->get()
            ->filter(fn ($row) => in_array(mb_strtolower($row->title), $website, true))
            ->groupBy(fn ($row) => $row->faculty_id . '|' . $row->group_name . '|' . $row->student_id);

        DB::table('tasks')
            ->where('role', 'room_management')
            ->whereIn('title', self::RETIRED_TITLES)
            ->delete();

        $hasActivities = Schema::hasColumn('tasks', 'activities');
        foreach ($holders as $held) {
            $model = $held->last();
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
                    'role' => 'room_management',
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
        // Not restorable: the checklist no longer hands the old tasks out.
    }
};
