<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Carries the Front Desk branding task's rename onto rows already assigned.
 *
 * A task row is a copy of its TaskChecklist entry made at assignment time, so
 * renaming the entry alone leaves every existing "Brand Your Hotel" card saying
 * the old thing. Rewritten here, as 2026_09_04_000000 did for the concept task.
 *
 * The steps are reset to unticked: the old four do not map one-to-one onto the
 * new four, so carrying ticks across would mark work done that was not asked for.
 *
 * Kept in step with TaskChecklist's 'Customize Your Hotel Branding' entry.
 */
return new class extends Migration
{
    private const OLD_TITLE = 'Brand Your Hotel';

    private const NEW_TITLE = 'Customize Your Hotel Branding';

    private const NEW_DESCRIPTION = "Make the website your own by changing the hotel logo, hotel name, and navigation names.\n\nSteps:\n"
        . "1. Change the Logo: upload your team's hotel logo.\n"
        . "2. Change the Hotel Name: replace the default hotel name with your team's hotel name.\n"
        . "3. Rename the Navigation: change the names of the main navigation items, such as Home, Rooms, Restaurant, Amenities, and Highlights.\n"
        . "4. Check Your Changes: click View Live and make sure your logo, hotel name, and navigation names appear correctly.";

    private const NEW_STEPS = [
        "Change the Logo: upload your team's hotel logo.",
        "Change the Hotel Name: replace the default hotel name with your team's hotel name.",
        'Rename the Navigation: change the names of the main navigation items, such as Home, Rooms, Restaurant, Amenities, and Highlights.',
        'Check Your Changes: click View Live and make sure your logo, hotel name, and navigation names appear correctly.',
    ];

    public function up(): void
    {
        DB::table('tasks')
            ->where('title', self::OLD_TITLE)
            ->update([
                'title' => self::NEW_TITLE,
                'description' => self::NEW_DESCRIPTION,
                'activities' => json_encode(array_map(
                    fn ($text) => ['text' => $text, 'done' => false],
                    self::NEW_STEPS
                )),
            ]);
    }

    public function down(): void
    {
        // The old text is not worth restoring: the checklist no longer hands it out.
    }
};
