<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Migrate existing roles from student_groups to student_group_roles
        $studentGroups = DB::table('student_groups')->select('id', 'role')->get();
        
        foreach ($studentGroups as $group) {
            DB::table('student_group_roles')->insert([
                'student_group_id' => $group->id,
                'role' => $group->role,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No need to reverse - the pivot table will be dropped
    }
};
