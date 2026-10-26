<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Repoints the seeded concept task's description at where the work now happens.
 *
 * Proposing the two concepts moved out of the dashboard's My Team section and onto
 * this task's own row in Tasks, and only Front Desk can reach it there — so the
 * description's "then the whole team can improve them" no longer describes
 * anything a member can do.
 *
 * Rewritten here rather than left for the next team to be created, for the same
 * reason 2026_08_19_000001 rewrote it: the text is copied into every existing task
 * row at seed time, so a constant alone would leave every card that already exists
 * saying the wrong thing.
 *
 * Kept in step with HotelConceptDesk::TASK_DESCRIPTION. down() restores the text
 * that migration left behind.
 */
return new class extends Migration
{
    private const TASK_KIND = 'hotel_concept';

    private const NEW_DESCRIPTION = 'Propose two hotel concepts your team could build: for each one, its title, its type and what makes it different. Write both here, improve them as often as you like, then submit the pair to your faculty, who reviews each concept separately and approves one.';

    private const OLD_DESCRIPTION = 'Propose two hotel concepts your team could build: for each one, its name, its type and what makes it different. Front Desk writes the first version of each, then the whole team can improve them before Front Desk submits both to your faculty, who reviews each concept separately.';

    public function up(): void
    {
        DB::table('tasks')
            ->where('kind', self::TASK_KIND)
            ->update(['description' => self::NEW_DESCRIPTION]);
    }

    public function down(): void
    {
        DB::table('tasks')
            ->where('kind', self::TASK_KIND)
            ->update(['description' => self::OLD_DESCRIPTION]);
    }
};
