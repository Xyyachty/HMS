<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Canonical "one row per group" table. group_name has always been a bare string
 * duplicated across 9 tables (student_groups, group_settings, team_role_templates,
 * team_template_edit_grants, hotel_customers, hotel_rooms, hotel_menu_items,
 * hotel_food_orders, reservation_notifications) with no FK integrity between them —
 * this is the target those tables' new group_id column will point at.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('groups')) {
            Schema::create('groups', function (Blueprint $table) {
                $table->id();
                $table->string('group_name');
                $table->foreignId('faculty_id')->constrained('faculties')->cascadeOnDelete();
                $table->timestamps();
                $table->unique(['group_name', 'faculty_id']);
            });
        }

        if (DB::getDriverName() === 'pgsql') {
            DB::statement('ALTER TABLE "groups" ALTER COLUMN "group_name" TYPE citext');

            // The earlier security migration only covered tables that existed when it ran.
            DB::statement('ALTER TABLE "groups" ENABLE ROW LEVEL SECURITY');
            foreach (['anon', 'authenticated'] as $role) {
                DB::statement(sprintf('REVOKE ALL ON "groups" FROM "%s"', $role));
            }
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('groups');
    }
};
