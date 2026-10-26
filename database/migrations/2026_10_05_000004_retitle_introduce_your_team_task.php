<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Carries the Front Desk team task's rename onto rows already assigned.
 *
 * Same reason as 2026_10_05_000003: a task row is a copy of its TaskChecklist
 * entry made at assignment time, so renaming the entry alone leaves every
 * existing "Introduce Your Team" card saying the old thing.
 *
 * The steps are reset to unticked: there are three new steps, not the old four
 * reworded.
 *
 * Kept in step with TaskChecklist's 'Customize Our Team' entry.
 */
return new class extends Migration
{
    private const OLD_TITLE = 'Introduce Your Team';

    private const NEW_TITLE = 'Customize Our Team';

    private const NEW_STEPS = [
        'Change the photos of each team member.',
        'Edit the names to show the correct team members.',
        'Edit their positions or roles to match their responsibilities in the hotel.',
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
                'description' => 'Customize the Our Team section by updating the photos, names, and positions of the hotel team members.'
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
