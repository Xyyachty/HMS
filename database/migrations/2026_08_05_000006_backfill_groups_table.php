<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() !== 'pgsql') {
            return;
        }

        // student_groups is authoritative — group_settings/team_role_templates rows
        // only exist after a team's site is first opened, so student_groups is a
        // superset. Rows with no faculty_id are skipped: a group can't be uniquely
        // identified without one.
        DB::statement(<<<'SQL'
            INSERT INTO groups (group_name, faculty_id, created_at, updated_at)
            SELECT DISTINCT group_name, faculty_id, now(), now()
            FROM student_groups
            WHERE faculty_id IS NOT NULL
            ON CONFLICT (group_name, faculty_id) DO NOTHING
        SQL);
    }

    public function down(): void
    {
        // No-op on purpose: truncating groups here would cascade-delete every
        // group_id-linked row across 9 tables once later migrations have run.
    }
};
