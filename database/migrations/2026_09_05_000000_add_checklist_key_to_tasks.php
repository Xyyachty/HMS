<?php

use App\Support\TaskChecklist;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Give a handed-out task a stable identity.
 *
 * Which part of the hotel site a student may edit is decided by which tasks they
 * are holding (App\Support\TemplateSectionMap), so a task row has to say which
 * checklist entry it came from. The title cannot answer that: it is prose, it
 * has already been rewritten once, and a rewrite would silently stop unlocking
 * anything rather than fail loudly.
 *
 * Existing rows are backfilled by title, which is the only thing they carry.
 * A row whose title matches nothing — a retired one, or the hotel-concept task,
 * which is seeded by HotelConceptDesk rather than ticked off the checklist —
 * keeps a null key and unlocks nothing, which is what it did before.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('tasks', 'checklist_key')) {
            Schema::table('tasks', function (Blueprint $table) {
                $table->string('checklist_key', 64)->nullable()->after('role');
                $table->index('checklist_key');
            });
        }

        foreach (TaskChecklist::keysByTitle() as $title => $key) {
            DB::table('tasks')
                ->where('title', $title)
                ->whereNull('checklist_key')
                ->update(['checklist_key' => $key]);
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('tasks', 'checklist_key')) {
            Schema::table('tasks', function (Blueprint $table) {
                $table->dropIndex(['checklist_key']);
                $table->dropColumn('checklist_key');
            });
        }
    }
};
