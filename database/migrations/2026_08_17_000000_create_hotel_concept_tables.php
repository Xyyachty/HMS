<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * The Front Desk team's first task: the hotel concept the rest of the simulation
 * is built on.
 *
 * One row per team, keyed the way every other team-owned table here is keyed
 * (group_name + faculty_id, with group_id carried along once groups exist), so a
 * team reads its own concept and nobody else's.
 *
 * Every save also writes a hotel_concept_revisions row — who touched it, what
 * moved, and when. The snapshot columns on the revision are deliberate: the
 * history has to stay readable after the concept itself is edited again, so each
 * row keeps the values as they stood plus the field-by-field diff.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('hotel_concepts')) {
            Schema::create('hotel_concepts', function (Blueprint $table) {
                $table->id('hotel_concept_id');
                $table->string('group_name');
                $table->unsignedBigInteger('faculty_id');
                $table->foreignId('group_id')->nullable()->constrained('groups', 'group_id')->nullOnDelete();
                $table->string('title');
                $table->text('description');
                // One of HotelConcept::HOTEL_TYPES. Plain string: this repo validates
                // enums in PHP rather than trusting a DB CHECK constraint.
                $table->string('hotel_type');
                // Who opened it and who touched it last. nullOnDelete, not cascade:
                // the concept belongs to the team, not to the member who typed it.
                $table->foreignId('created_by')->nullable()->constrained('users', 'user_id')->nullOnDelete();
                $table->foreignId('updated_by')->nullable()->constrained('users', 'user_id')->nullOnDelete();
                $table->timestamps();
                // A team has exactly one concept; the upsert in HotelConceptController
                // leans on this.
                $table->unique(['group_name', 'faculty_id']);
            });

            $this->enableRowLevelSecurity('hotel_concepts');
        }

        if (!Schema::hasTable('hotel_concept_revisions')) {
            Schema::create('hotel_concept_revisions', function (Blueprint $table) {
                $table->id('hotel_concept_revision_id');
                $table->foreignId('hotel_concept_id')->constrained('hotel_concepts', 'hotel_concept_id')->cascadeOnDelete();
                $table->foreignId('user_id')->nullable()->constrained('users', 'user_id')->nullOnDelete();
                // Display name snapshot, so a deleted account still reads as the editor.
                $table->string('editor_name')->nullable();
                // created | updated — see HotelConceptRevision::ACTIONS.
                $table->string('action');
                // Field-by-field diff: { field: { from, to } }. Empty on creation,
                // where the snapshot columns below are the whole story. Not named
                // "changes" — Eloquent\Model owns a property of that name, which
                // shadows the attribute on read.
                $table->json('field_changes')->nullable();
                $table->string('title');
                $table->text('description');
                $table->string('hotel_type');
                // A revision is never updated, so it carries only created_at.
                $table->timestamp('created_at')->nullable();
                $table->index(['hotel_concept_id', 'created_at']);
            });

            $this->enableRowLevelSecurity('hotel_concept_revisions');
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('hotel_concept_revisions');
        Schema::dropIfExists('hotel_concepts');
    }

    /**
     * The 2026_08_05_000001 migration closed the Supabase REST API off from every table
     * that existed then. Tables created afterwards need the same treatment: default
     * privileges are already revoked, RLS still has to be switched on.
     */
    private function enableRowLevelSecurity(string $table): void
    {
        if (DB::getDriverName() === 'pgsql') {
            DB::statement('ALTER TABLE "' . $table . '" ENABLE ROW LEVEL SECURITY');
        }
    }
};
