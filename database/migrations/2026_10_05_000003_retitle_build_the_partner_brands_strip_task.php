<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Carries the Front Desk partner brands task's rename onto rows already assigned.
 *
 * Same reason as 2026_10_05_000002: a task row is a copy of its TaskChecklist
 * entry made at assignment time, so renaming the entry alone leaves every
 * existing "Build the Partner Brands Strip" card saying the old thing.
 *
 * The steps are reset to unticked: they are new steps, not the old ones reworded.
 *
 * Kept in step with TaskChecklist's 'Customize Partner Brands' entry.
 */
return new class extends Migration
{
    private const OLD_TITLE = 'Build the Partner Brands Strip';

    private const NEW_TITLE = 'Customize Partner Brands';

    private const NEW_STEPS = [
        'Change the images of the existing partner brands.',
        'Edit the brand names to match your hotel’s actual partners.',
        'Add new partner brands using the Add Brand button.',
        'Remove unnecessary brands and review the section to make sure all partner information is correct.',
    ];

    public function up(): void
    {
        $lines = [];
        foreach (self::NEW_STEPS as $i => $step) {
            $lines[] = ($i + 1) . '. ' . $step;
        }

        DB::table('tasks')
            ->where('title', self::OLD_TITLE)
            ->update([
                'title' => self::NEW_TITLE,
                'description' => 'Customize the Partner Brands section by adding your hotel’s partner brands and updating their names and images.'
                    . "\n\nSteps:\n" . implode("\n", $lines),
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
