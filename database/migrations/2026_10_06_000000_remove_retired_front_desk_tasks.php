<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Front Desk now has exactly eight tasks after the hotel concept (see
 * TaskChecklist's 'front_desk' list). These seven are no longer handed out, so
 * rows already assigned under them are removed: left in place they showed under
 * Other Tasks beside the eight. None had been completed when this ran.
 *
 * Nothing references a task row, so deleting one leaves no orphans. The site
 * work itself is untouched: a task row is only the checklist, never the edits.
 */
return new class extends Migration
{
    private const RETIRED_TITLES = [
        "Write Your Hotel's Story",
        "Choose the Site's Colours",
        "Set the Site's Typography",
        'Add Your Social Profiles',
        'Write the Experience Page',
        'Colour the Experience Page',
        'Illustrate the Experience Page',
    ];

    public function up(): void
    {
        DB::table('tasks')
            ->where('role', 'front_desk')
            ->whereIn('title', self::RETIRED_TITLES)
            ->delete();
    }

    public function down(): void
    {
        // Not restorable: the checklist no longer hands these out.
    }
};
