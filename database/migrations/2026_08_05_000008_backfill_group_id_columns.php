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

        $tables = [
            'student_groups',
            'group_settings',
            'team_role_templates',
            'team_template_edit_grants',
            'hotel_customers',
            'hotel_rooms',
            'hotel_menu_items',
            'hotel_food_orders',
            'reservation_notifications',
        ];

        // LOWER(x::text) on both sides regardless of citext-ness, so this is correct
        // whether the source column is citext or plain varchar. Only touches rows
        // still missing a group_id, so this migration is safe to re-run.
        foreach ($tables as $table) {
            DB::statement(<<<SQL
                UPDATE "{$table}" t
                SET group_id = g.id
                FROM groups g
                WHERE t.faculty_id = g.faculty_id
                  AND LOWER(t.group_name::text) = LOWER(g.group_name::text)
                  AND t.group_id IS NULL
            SQL);
        }
    }

    public function down(): void
    {
        // No-op on purpose: left as a manual revert to avoid masking a real problem.
    }
};
