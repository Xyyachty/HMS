<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * cascadeOnDelete for student_groups, group_settings, team_role_templates matches
     * their existing faculty_id onDelete behavior. The rest — hotel_customers,
     * hotel_rooms, hotel_menu_items, hotel_food_orders, team_template_edit_grants,
     * reservation_notifications — carry no faculty_id FK at all today and orphan
     * silently forever; nullOnDelete keeps that same conservative behavior rather than
     * introducing a new, unreviewed cascade path with no group-deletion feature to
     * exercise it.
     */
    private array $onDelete = [
        'student_groups' => 'null',
        'group_settings' => 'cascade',
        'team_role_templates' => 'cascade',
        'team_template_edit_grants' => 'null',
        'hotel_customers' => 'null',
        'hotel_rooms' => 'null',
        'hotel_menu_items' => 'null',
        'hotel_food_orders' => 'null',
        'reservation_notifications' => 'null',
    ];

    public function up(): void
    {
        foreach ($this->onDelete as $table => $mode) {
            if (Schema::hasTable($table) && !Schema::hasColumn($table, 'group_id')) {
                Schema::table($table, function (Blueprint $blueprint) use ($mode) {
                    $column = $blueprint->foreignId('group_id')->nullable()->constrained('groups');
                    $mode === 'cascade' ? $column->cascadeOnDelete() : $column->nullOnDelete();
                });
            }
        }
    }

    public function down(): void
    {
        foreach (array_keys($this->onDelete) as $table) {
            if (Schema::hasTable($table) && Schema::hasColumn($table, 'group_id')) {
                Schema::table($table, function (Blueprint $blueprint) {
                    $blueprint->dropConstrainedForeignId('group_id');
                });
            }
        }
    }
};
