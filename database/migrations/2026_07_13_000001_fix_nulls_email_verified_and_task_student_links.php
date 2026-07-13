<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Clean invalid "None"/empty strings, verify emails, and link tasks.student_id.
     */
    public function up(): void
    {
        $nullish = ['None', 'none', 'NONE', 'null', 'NULL', 'Null', ''];

        DB::table('users')
            ->whereIn('middle_name', $nullish)
            ->update(['middle_name' => null]);

        DB::table('users')
            ->whereIn('phone_number', $nullish)
            ->update(['phone_number' => null]);

        if (DB::getSchemaBuilder()->hasColumn('faculties', 'phone_number')) {
            DB::table('faculties')
                ->whereIn('phone_number', $nullish)
                ->update(['phone_number' => null]);
        }

        DB::table('users')
            ->whereNull('email_verified_at')
            ->update(['email_verified_at' => now()]);

        // Link tasks.student_id from assigned_to → students.user_id
        $tasks = DB::table('tasks')
            ->whereNotNull('assigned_to')
            ->whereNull('student_id')
            ->get(['id', 'assigned_to']);

        foreach ($tasks as $task) {
            $studentId = DB::table('students')
                ->where('user_id', $task->assigned_to)
                ->value('id');

            if ($studentId) {
                DB::table('tasks')
                    ->where('id', $task->id)
                    ->update(['student_id' => $studentId]);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Data cleanup is not reversible.
    }
};
