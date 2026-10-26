<?php

use App\Support\TaskChecklist;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Housekeeping now has exactly five tasks, HK TASK 1-5, all on the Amenities
 * page (see TaskChecklist's 'housekeeping' list).
 *
 * Every student holding the old Housekeeping website tasks loses them - none
 * had been completed when this ran, and three were Experience page work that
 * belongs to Front Desk now - and is given the five new ones with the faculty,
 * team and due date of the tasks they held, steps unticked: what ticking
 * Task 04 on Create Task would write. The facilities themselves are untouched.
 */
return new class extends Migration
{
    private const RETIRED_TITLES = [
        'Customize Hotel Amenities',
        'Build the Amenities Page',
        'Colour the Amenities Page',
        'Colour the Amenities and Experience Pages',
        'Write the Amenities Page Introduction',
        'Check the Amenities Page on a Phone',
        'Check Your Pages on a Phone',
        'Review the Amenities Page Against the Site',
        'Review Your Pages Against the Site',
        'Write the Experience Page',
        'Illustrate the Experience Page',
    ];

    public function up(): void
    {
        $checklist = TaskChecklist::forRole('housekeeping');
        $current = array_map(fn ($task) => mb_strtolower($task['title']), $checklist);
        $website = array_merge($current, array_map('mb_strtolower', self::RETIRED_TITLES));

        // Whoever held Housekeeping website work before the change, one row each.
        $holders = DB::table('tasks')
            ->where('role', 'housekeeping')
            ->orderBy('task_id')
            ->get()
            ->filter(fn ($row) => in_array(mb_strtolower($row->title), $website, true))
            ->groupBy(fn ($row) => $row->faculty_id . '|' . $row->group_name . '|' . $row->student_id);

        DB::table('tasks')
            ->where('role', 'housekeeping')
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
                    'role' => 'housekeeping',
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
