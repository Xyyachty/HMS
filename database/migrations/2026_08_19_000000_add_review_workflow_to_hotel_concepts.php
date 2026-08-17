<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Turns the hotel concept from a page the team edits forever into a task the
 * faculty closes.
 *
 * The concept already had a history; what it lacked was a state. It now carries
 * one, and the state is what decides who may write:
 *
 *   draft          Front Desk proposed it, the whole team may improve it
 *   submitted      handed to faculty, nobody edits until they answer
 *   needs_revision sent back with feedback, the whole team may edit again
 *   approved       final, read-only
 *
 * status is a plain string, like every other status column here: Laravel names
 * Postgres enum CHECK constraints uncontrollably, so the value set lives in
 * HotelConceptDesk::STATUSES and is validated in PHP.
 *
 * tasks gains `kind` so the seeded "Propose a Hotel Concept" row can be told
 * apart from an ordinary assignment. A marker column rather than a title match:
 * titles are free text, and matching on one would break the moment a faculty
 * member assigned something similarly named.
 *
 * The backfill seeds that task row for every Front Desk member who already
 * exists, because the workflow is auto-assigned — nobody picks it off the
 * faculty checklist. It is written to be safe to run twice.
 *
 * down() drops the columns, which discards the review state and every seeded
 * task row. The concepts and their revision history survive.
 */
return new class extends Migration
{
    /** Kept in step with HotelConceptDesk::TASK_KIND / TASK_TITLE. */
    private const TASK_KIND = 'hotel_concept';
    private const TASK_TITLE = 'Propose a Hotel Concept';
    private const TASK_DESCRIPTION = 'Propose the hotel your team will build: its name, its type and what makes it different. Front Desk writes the first version, then the whole team can improve it before Front Desk submits it to your faculty.';
    private const OWNING_ROLE = 'front_desk';

    public function up(): void
    {
        Schema::table('hotel_concepts', function (Blueprint $table) {
            if (!Schema::hasColumn('hotel_concepts', 'status')) {
                $table->string('status', 24)->default('draft')->after('hotel_type');
            }

            // Who handed it in, and when. nullOnDelete for the same reason as
            // created_by: the concept belongs to the team, not to the member.
            if (!Schema::hasColumn('hotel_concepts', 'submitted_at')) {
                $table->timestamp('submitted_at')->nullable()->after('status');
            }

            if (!Schema::hasColumn('hotel_concepts', 'submitted_by')) {
                $table->foreignId('submitted_by')->nullable()->after('submitted_at')
                    ->constrained('users', 'user_id')->nullOnDelete();
            }

            // The faculty verdict. faculty_feedback holds the words behind a
            // "needs revision", which the team reads on their dashboard.
            if (!Schema::hasColumn('hotel_concepts', 'reviewed_at')) {
                $table->timestamp('reviewed_at')->nullable()->after('submitted_by');
            }

            if (!Schema::hasColumn('hotel_concepts', 'reviewed_by')) {
                $table->foreignId('reviewed_by')->nullable()->after('reviewed_at')
                    ->constrained('users', 'user_id')->nullOnDelete();
            }

            if (!Schema::hasColumn('hotel_concepts', 'faculty_feedback')) {
                $table->text('faculty_feedback')->nullable()->after('reviewed_by');
            }

            // How many times it came back. Mirrors tasks.revision_count so both
            // sides of the workflow count the same rounds.
            if (!Schema::hasColumn('hotel_concepts', 'revision_count')) {
                $table->unsignedTinyInteger('revision_count')->default(0)->after('faculty_feedback');
            }
        });

        // Existing concepts predate the workflow, so nobody has submitted them.
        DB::table('hotel_concepts')->whereNull('status')->update(['status' => 'draft']);

        if (!Schema::hasColumn('tasks', 'kind')) {
            Schema::table('tasks', function (Blueprint $table) {
                $table->string('kind', 32)->nullable()->after('role');
                $table->index('kind');
            });
        }

        $this->seedConceptTasks();
    }

    public function down(): void
    {
        DB::table('tasks')->where('kind', self::TASK_KIND)->delete();

        if (Schema::hasColumn('tasks', 'kind')) {
            Schema::table('tasks', function (Blueprint $table) {
                $table->dropIndex(['kind']);
                $table->dropColumn('kind');
            });
        }

        Schema::table('hotel_concepts', function (Blueprint $table) {
            foreach (['submitted_by', 'reviewed_by'] as $column) {
                if (Schema::hasColumn('hotel_concepts', $column)) {
                    $table->dropConstrainedForeignId($column);
                }
            }

            $plain = array_values(array_filter(
                ['status', 'submitted_at', 'reviewed_at', 'faculty_feedback', 'revision_count'],
                fn ($column) => Schema::hasColumn('hotel_concepts', $column)
            ));

            if ($plain !== []) {
                $table->dropColumn($plain);
            }
        });
    }

    /**
     * One concept task per Front Desk member who already exists.
     *
     * Tasks fan out one row per member everywhere else in this app, so this
     * follows suit rather than inventing a team-level row — the team's rows are
     * resolved back through student_id when the concept is submitted.
     *
     * Members who already hold one are skipped, so a re-run adds nothing.
     */
    private function seedConceptTasks(): void
    {
        $existing = DB::table('tasks')
            ->where('kind', self::TASK_KIND)
            ->whereNotNull('student_id')
            ->pluck('student_id')
            ->map(fn ($id) => (int) $id)
            ->all();
        $existing = array_flip($existing);

        // student_groups holds the membership; student_group_roles holds the real
        // roles (student_groups.role is a legacy single-role column).
        $members = DB::table('student_groups')
            ->join('student_group_roles', 'student_groups.student_group_id', '=', 'student_group_roles.student_group_id')
            ->join('user_information', 'student_groups.student_id', '=', 'user_information.user_information_id')
            ->where('student_group_roles.role', self::OWNING_ROLE)
            ->whereNotNull('student_groups.faculty_id')
            ->select([
                'student_groups.student_id',
                'student_groups.faculty_id',
                'user_information.user_id',
            ])
            ->get();

        $rows = [];
        $now = now();

        foreach ($members as $member) {
            $studentId = (int) $member->student_id;
            if ($studentId === 0 || isset($existing[$studentId])) {
                continue;
            }
            $existing[$studentId] = true;

            $rows[] = [
                'faculty_id' => (int) $member->faculty_id,
                'student_id' => $studentId,
                'assigned_to' => $member->user_id ? (int) $member->user_id : null,
                'role' => self::OWNING_ROLE,
                'kind' => self::TASK_KIND,
                'title' => self::TASK_TITLE,
                'description' => self::TASK_DESCRIPTION,
                'due_date' => null,
                'priority' => 'high',
                'status' => 'active',
                'revision_count' => 0,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        foreach (array_chunk($rows, 200) as $chunk) {
            DB::table('tasks')->insert($chunk);
        }
    }
};
