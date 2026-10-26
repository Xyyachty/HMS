<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * A member of a team short of students can run more than one Simulation desk,
 * stored comma-separated in simulation_role (SimulationPhase::seatsFor). All four
 * desk keys together do not fit the original 40 characters.
 *
 * Raw SQL because Laravel 10 needs doctrine/dbal for ->change(). SQLite does
 * not enforce varchar lengths, so it needs nothing.
 */
return new class extends Migration
{
    public function up(): void
    {
        $this->resize(255);
    }

    public function down(): void
    {
        $this->resize(40);
    }

    private function resize(int $length): void
    {
        if (!Schema::hasColumn('student_groups', 'simulation_role')) {
            return;
        }

        match (DB::getDriverName()) {
            'pgsql' => DB::statement("ALTER TABLE student_groups ALTER COLUMN simulation_role TYPE varchar({$length})"),
            'mysql', 'mariadb' => DB::statement("ALTER TABLE student_groups MODIFY simulation_role varchar({$length}) NULL"),
            default => null,
        };
    }
};
