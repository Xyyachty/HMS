<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * A team now proposes two hotel concepts, not one, and faculty judges each on its
 * own merits.
 *
 * The concepts are addressed by slot rather than by insertion order: slot 1 and
 * slot 2 are stable names the whole workflow can point at, so "Concept 2 needs
 * revision" still means the same row after either one is edited. Order by id
 * would have been free, but it moves the moment a row is deleted and recreated,
 * and it gives the review dialog nothing durable to label.
 *
 * The old unique(group_name, faculty_id) said "one concept per team" — that was
 * the whole point of it, and the upsert in HotelConceptController leaned on it.
 * It is replaced by unique(group_name, faculty_id, slot), which keeps the same
 * protection one level down: a team cannot end up with two rows in the same slot,
 * and the locked read in the controller still has an index to rely on.
 *
 * Every existing concept becomes slot 1. Nothing becomes slot 2 — the columns
 * that carry the text are NOT NULL, so a slot exists once somebody writes it,
 * and the portals render the empty slot from the absence of a row.
 *
 * Per-concept status, feedback and history need no work here: status and
 * faculty_feedback are already columns on this table rather than on the team, and
 * hotel_concept_revisions already hangs off hotel_concept_id.
 */
return new class extends Migration
{
    /** Kept in step with HotelConceptDesk. */
    private const TASK_KIND = 'hotel_concept';
    private const TASK_TITLE = 'Propose Two Hotel Concepts';
    private const TASK_DESCRIPTION = 'Propose two hotel concepts your team could build: for each one, its name, its type and what makes it different. Front Desk writes the first version of each, then the whole team can improve them before Front Desk submits both to your faculty, who reviews each concept separately.';

    public function up(): void
    {
        if (!Schema::hasColumn('hotel_concepts', 'slot')) {
            Schema::table('hotel_concepts', function (Blueprint $table) {
                // Default 1 so the rows that exist land in the first slot.
                $table->unsignedTinyInteger('slot')->default(1)->after('group_id');
            });
        }

        // Existing rows predate slots and are each their team's only concept.
        DB::table('hotel_concepts')->whereNull('slot')->update(['slot' => 1]);

        $this->swapUniqueIndex();

        // The seeded task asked for one concept. Retitled here rather than left for
        // the next time a team is edited, so no card asks for the wrong thing.
        DB::table('tasks')
            ->where('kind', self::TASK_KIND)
            ->update([
                'title' => self::TASK_TITLE,
                'description' => self::TASK_DESCRIPTION,
            ]);
    }

    public function down(): void
    {
        // Only slot 1 can survive a table that allows one concept per team. The
        // second concept, and its history, are dropped — this is not reversible
        // in any other sense.
        $this->restoreUniqueIndex();

        if (Schema::hasColumn('hotel_concepts', 'slot')) {
            Schema::table('hotel_concepts', function (Blueprint $table) {
                $table->dropColumn('slot');
            });
        }
    }

    /**
     * One concept per team becomes one concept per team per slot.
     *
     * Guarded on the index names rather than blindly dropped, so a re-run after a
     * partial failure does not error on an index that is already gone.
     */
    private function swapUniqueIndex(): void
    {
        if ($this->hasIndex('hotel_concepts_group_name_faculty_id_unique')) {
            Schema::table('hotel_concepts', function (Blueprint $table) {
                $table->dropUnique(['group_name', 'faculty_id']);
            });
        }

        if (!$this->hasIndex('hotel_concepts_group_name_faculty_id_slot_unique')) {
            Schema::table('hotel_concepts', function (Blueprint $table) {
                $table->unique(['group_name', 'faculty_id', 'slot']);
            });
        }
    }

    private function restoreUniqueIndex(): void
    {
        if ($this->hasIndex('hotel_concepts_group_name_faculty_id_slot_unique')) {
            Schema::table('hotel_concepts', function (Blueprint $table) {
                $table->dropUnique(['group_name', 'faculty_id', 'slot']);
            });
        }

        // Anything past the first slot has to go before one-per-team can hold again.
        DB::table('hotel_concepts')->where('slot', '>', 1)->delete();

        if (!$this->hasIndex('hotel_concepts_group_name_faculty_id_unique')) {
            Schema::table('hotel_concepts', function (Blueprint $table) {
                $table->unique(['group_name', 'faculty_id']);
            });
        }
    }

    private function hasIndex(string $name): bool
    {
        if (DB::getDriverName() !== 'pgsql') {
            return true;
        }

        return DB::table('pg_indexes')
            ->where('tablename', 'hotel_concepts')
            ->where('indexname', $name)
            ->exists();
    }
};
