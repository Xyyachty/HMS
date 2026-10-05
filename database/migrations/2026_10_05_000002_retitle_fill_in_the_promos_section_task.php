<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Carries the Front Desk promos task's rename onto rows already assigned.
 *
 * Same reason as 2026_10_05_000001: a task row is a copy of its TaskChecklist
 * entry made at assignment time, so renaming the entry alone leaves every
 * existing "Fill In the Promos Section" card saying the old thing.
 *
 * The steps are reset to unticked: they are new steps, not the old ones reworded.
 *
 * Kept in step with TaskChecklist's 'Customize Promos and Packages' entry.
 */
return new class extends Migration
{
    private const OLD_TITLE = 'Fill In the Promos Section';

    private const NEW_TITLE = 'Customize Promos and Packages';

    private const NEW_STEPS = [
        'Change the images of the featured promo and the promo cards.',
        'Edit the promo titles and labels, such as the discount or offer name.',
        'Edit the descriptions and other details of each promo or package.',
        'Review the Promos and Packages section and make sure all information and images match your hotel.',
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
                'description' => 'Customize the Promos and Packages section by updating the images, titles, descriptions, and other promo details to match your hotel.'
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
