<?php

namespace App\Console\Commands;

use App\Models\Task;
use App\Models\TeamRoleTemplate;
use App\Support\HotelTemplateBuilder;
use App\Support\TemplateCustomizationStore;
use Illuminate\Console\Command;

/**
 * One-off data fix for the Experience page's ownership move from Housekeeping to
 * Front Desk (see HotelTemplateBuilder::ROLE_EDITABLE_PAGES). Any team whose
 * Housekeeping student already customized Experience has that content sitting in
 * the team's housekeeping team_role_template row; left there, Housekeeping's next
 * save would silently delete it (filterCustomizationsForRole() now drops
 * page === 'experience' entries for that role). This moves it to the team's
 * front_desk row instead, where it belongs going forward.
 *
 * Only ever touches live (version_id = 0) rows — a submitted task's snapshot
 * stays exactly as it was reviewed. Safe to re-run: a team with nothing tagged
 * 'experience' on its housekeeping row is simply skipped.
 */
class ReassignExperiencePage extends Command
{
    protected $signature = 'hms:reassign-experience-page {--dry-run : Report what would move without writing}';

    protected $description = "Move Housekeeping's already-saved Experience page content to Front Desk";

    /** Titles that used to belong to housekeeping and now belong to front_desk. */
    private const MOVED_TITLES = [
        'write the experience page',
        'colour the amenities and experience pages',
        'illustrate the experience page',
    ];

    public function handle(): int
    {
        $dryRun = (bool) $this->option('dry-run');
        if ($dryRun) {
            $this->warn('Dry run — nothing will be written.');
        }

        $moved = 0;

        TeamRoleTemplate::where('role', 'housekeeping')->each(function (TeamRoleTemplate $hk) use ($dryRun, &$moved) {
            $data = TemplateCustomizationStore::readCustomizations((int) $hk->team_role_template_id, 0);
            $extract = $this->extractExperienceEntries($data);

            if ($extract['userElements'] === [] && $extract['deleted'] === [] && $extract['generic'] === [] && $extract['cardImages'] === []) {
                return;
            }

            $moved++;
            $count = count($extract['userElements']) + count($extract['deleted']) + count($extract['generic']) + count($extract['cardImages']);
            $this->line(sprintf(
                '%s: %d Experience entr%s on team "%s" (faculty #%d)',
                $dryRun ? 'Would move' : 'Moving',
                $count,
                $count === 1 ? 'y' : 'ies',
                $hk->group_name,
                $hk->faculty_id
            ));

            if ($dryRun) {
                return;
            }

            $fd = TeamRoleTemplate::firstOrCreate(
                ['group_name' => $hk->group_name, 'faculty_id' => $hk->faculty_id, 'role' => 'front_desk'],
                ['selected_template' => $hk->selected_template, 'is_published' => false, 'version' => 1]
            );
            if ($fd->wasRecentlyCreated) {
                $fd->customizations = [];
                $fd->layout = HotelTemplateBuilder::defaultLayout();
                $fd->save();
            }

            $fdData = TemplateCustomizationStore::readCustomizations((int) $fd->team_role_template_id, 0);
            $fdLayout = TemplateCustomizationStore::readLayout((int) $fd->team_role_template_id, 0);
            $fdData[HotelTemplateBuilder::USER_ELEMENTS_KEY] = array_merge(
                $fdData[HotelTemplateBuilder::USER_ELEMENTS_KEY] ?? [],
                $extract['userElements']
            );
            $fdData[HotelTemplateBuilder::DELETED_KEY] = array_merge(
                $fdData[HotelTemplateBuilder::DELETED_KEY] ?? [],
                $extract['deleted']
            );
            foreach ($extract['generic'] as $key => $value) {
                $fdData[$key] = $value;
            }
            if ($extract['cardImages'] !== []) {
                $fdMap = $fdData[HotelTemplateBuilder::CARD_IMAGES_KEY]['map'] ?? [];
                $fdData[HotelTemplateBuilder::CARD_IMAGES_KEY] = ['map' => array_merge($fdMap, $extract['cardImages'])];
            }
            TemplateCustomizationStore::write($fd, $fdData, $fdLayout, 0);

            $hkLayout = TemplateCustomizationStore::readLayout((int) $hk->team_role_template_id, 0);
            TemplateCustomizationStore::write($hk, $extract['remaining'], $hkLayout, 0);
        });

        $this->newLine();
        $this->info($dryRun
            ? "Would touch {$moved} team(s)."
            : "Moved Experience content for {$moved} team(s).");

        $this->reportStrandedTasks();

        return self::SUCCESS;
    }

    /**
     * Split $data into what stays on Housekeeping's row and what belongs to
     * Front Desk, following the exact same page-tag rules
     * HotelTemplateBuilder::filterCustomizationsForRole() saves by.
     *
     * @return array{userElements: list<array>, deleted: list<array>, generic: array<string, array>, cardImages: array<string, string>, remaining: array}
     */
    private function extractExperienceEntries(array $data): array
    {
        $userElements = [];
        $keepUserElements = [];
        foreach ($data[HotelTemplateBuilder::USER_ELEMENTS_KEY] ?? [] as $item) {
            if (is_array($item) && ($item['page'] ?? null) === 'experience') {
                $userElements[] = $item;
            } else {
                $keepUserElements[] = $item;
            }
        }

        $deleted = [];
        $keepDeleted = [];
        foreach ($data[HotelTemplateBuilder::DELETED_KEY] ?? [] as $item) {
            if (is_array($item) && ($item['page'] ?? null) === 'experience') {
                $deleted[] = $item;
            } else {
                $keepDeleted[] = $item;
            }
        }

        $cardImages = [];
        $keepMap = $data[HotelTemplateBuilder::CARD_IMAGES_KEY]['map'] ?? [];
        foreach ($keepMap as $key => $url) {
            if (str_starts_with((string) $key, 'exp:') || str_starts_with((string) $key, 'testimonial:')) {
                $cardImages[$key] = $url;
                unset($keepMap[$key]);
            }
        }

        $generic = [];
        $remaining = $data;
        $remaining[HotelTemplateBuilder::USER_ELEMENTS_KEY] = $keepUserElements;
        $remaining[HotelTemplateBuilder::DELETED_KEY] = $keepDeleted;
        if ($keepMap !== ($data[HotelTemplateBuilder::CARD_IMAGES_KEY]['map'] ?? [])) {
            $remaining[HotelTemplateBuilder::CARD_IMAGES_KEY] = $keepMap === [] ? null : ['map' => $keepMap];
            if ($remaining[HotelTemplateBuilder::CARD_IMAGES_KEY] === null) {
                unset($remaining[HotelTemplateBuilder::CARD_IMAGES_KEY]);
            }
        }

        $skip = [HotelTemplateBuilder::USER_ELEMENTS_KEY, HotelTemplateBuilder::DELETED_KEY, HotelTemplateBuilder::CARD_IMAGES_KEY];
        foreach ($data as $key => $value) {
            if (in_array($key, $skip, true) || !is_array($value)) {
                continue;
            }
            if (($value['page'] ?? null) === 'experience') {
                $generic[$key] = $value;
                unset($remaining[$key]);
            }
        }

        return [
            'userElements' => $userElements,
            'deleted' => $deleted,
            'generic' => $generic,
            'cardImages' => $cardImages,
            'remaining' => $remaining,
        ];
    }

    /**
     * Task rows are not touched here — reassigning role on an already-baselined
     * task risks pointing tasks.complete's snapshot at the wrong
     * team_role_template row. Report them for faculty to delete and reassign.
     */
    private function reportStrandedTasks(): void
    {
        $stranded = Task::where('role', 'housekeeping')
            ->where('status', 'active')
            ->get(['task_id', 'title', 'group_name'])
            ->filter(fn ($task) => in_array(mb_strtolower(trim((string) $task->title)), self::MOVED_TITLES, true));

        if ($stranded->isEmpty()) {
            return;
        }

        $this->newLine();
        $this->warn('Active Housekeeping tasks still assigned under a moved title — delete and reassign these as Front Desk tasks:');
        foreach ($stranded as $task) {
            $this->line(sprintf('  #%d  "%s"  (team %s)', $task->task_id, $task->title, $task->group_name));
        }
    }
}
