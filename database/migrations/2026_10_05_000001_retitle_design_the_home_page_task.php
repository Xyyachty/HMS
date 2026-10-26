<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Carries the Front Desk Home Page task's rename onto rows already assigned.
 *
 * Same reason as 2026_10_05_000000: a task row is a copy of its TaskChecklist
 * entry made at assignment time, so renaming the entry alone leaves every
 * existing "Design the Home Page" card saying the old thing.
 *
 * The steps are reset to unticked: the old fourth step (renaming the menu) is
 * gone, now covered by the branding task, so the old ticks do not carry across.
 *
 * Kept in step with TaskChecklist's 'Customize the Home Page' entry.
 */
return new class extends Migration
{
    private const OLD_TITLE = 'Design the Home Page';

    private const NEW_TITLE = 'Customize the Home Page';

    private const NEW_STEPS = [
        'Change the 5 Slider Images: replace all 5 images in the Home page slider with images that represent your hotel.',
        'Edit the Small Heading: change the small text above the main heading, such as "COMFORT BY THE SEA."',
        'Edit the Main Heading: change the main Hero text, such as "Where Elegance Meets Comfort."',
        'Edit the Description: change the short description below the main heading to describe your hotel.',
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
                'description' => 'Customize the Home page by changing the 5 slider images and editing the text in the Hero section to match your hotel.'
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
