<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * School terms, so one batch of students can be archived before the next one starts.
 *
 * The dean names the current term ("1st Sem 2026-2027") and may give it an end date,
 * which only drives a reminder. Ending the term archives every current student:
 *   user_information.academic_term_id  the term they were archived with; null for a
 *                                      current student
 *   user_information.archived_block    the block they sat in ("Block B"), since their
 *                                      seat is given back for the next batch
 *   student_groups.archived_at         their team memberships, kept for the records
 *                                      but out of the working screens
 * Nothing is deleted: tasks, reports and hotel data stay with the old team names, and
 * those names are never handed to a new team.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('academic_terms')) {
            Schema::create('academic_terms', function (Blueprint $table) {
                $table->id('academic_term_id');
                $table->string('name', 60);
                $table->date('ends_on')->nullable();
                // Null while the term is running. At most one term is running at a time
                // (AcademicTermDesk refuses to start a second).
                $table->timestamp('ended_at')->nullable();
                $table->string('ended_by')->nullable();
                $table->timestamps();
            });

            if (DB::getDriverName() === 'pgsql') {
                DB::statement('ALTER TABLE "academic_terms" ENABLE ROW LEVEL SECURITY');
            }
        }

        if (!Schema::hasColumn('user_information', 'academic_term_id')) {
            Schema::table('user_information', function (Blueprint $table) {
                $table->foreignId('academic_term_id')->nullable()
                    ->constrained('academic_terms', 'academic_term_id')->nullOnDelete();
                $table->string('archived_block', 20)->nullable();
            });
        }

        if (!Schema::hasColumn('student_groups', 'archived_at')) {
            Schema::table('student_groups', function (Blueprint $table) {
                $table->timestamp('archived_at')->nullable();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('student_groups', 'archived_at')) {
            Schema::table('student_groups', function (Blueprint $table) {
                $table->dropColumn('archived_at');
            });
        }

        if (Schema::hasColumn('user_information', 'academic_term_id')) {
            Schema::table('user_information', function (Blueprint $table) {
                $table->dropForeign(['academic_term_id']);
                $table->dropColumn(['academic_term_id', 'archived_block']);
            });
        }

        Schema::dropIfExists('academic_terms');
    }
};
