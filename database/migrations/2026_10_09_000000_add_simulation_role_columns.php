<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * The two-phase workflow: a member's Simulation role is stored apart from the
 * Customization seat in student_group_roles, so moving into the simulation
 * never rewrites the seat a student customized under.
 *
 * simulation_role is null until faculty sets it, and a null reads as the same
 * key as the seat (SimulationPhase::seatFor). simulation_roles_confirmed_at is
 * faculty signing the assignment off; the simulation does not open before it.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('student_groups') && !Schema::hasColumn('student_groups', 'simulation_role')) {
            Schema::table('student_groups', function (Blueprint $table) {
                $table->string('simulation_role', 40)->nullable();
            });
        }

        if (Schema::hasTable('groups') && !Schema::hasColumn('groups', 'simulation_roles_confirmed_at')) {
            Schema::table('groups', function (Blueprint $table) {
                $table->timestamp('simulation_roles_confirmed_at')->nullable();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('student_groups', 'simulation_role')) {
            Schema::table('student_groups', function (Blueprint $table) {
                $table->dropColumn('simulation_role');
            });
        }

        if (Schema::hasColumn('groups', 'simulation_roles_confirmed_at')) {
            Schema::table('groups', function (Blueprint $table) {
                $table->dropColumn('simulation_roles_confirmed_at');
            });
        }
    }
};
