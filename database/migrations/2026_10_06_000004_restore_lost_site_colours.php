<?php

use App\Models\TeamRoleTemplate;
use App\Models\TeamRoleTemplateVersion;
use App\Support\TemplateCustomizationStore;
use Illuminate\Database\Migrations\Migration;

/**
 * Puts back site background colours a team chose and then lost without
 * touching them.
 *
 * Colours used to be saved in whichever role's row set them. When they became
 * Front Desk's alone, a teammate's next save dropped the copy in their own row
 * (filterCustomizationsForRole keeps __siteColors for front_desk only) and
 * nothing moved it to Front Desk, so the site fell back to the template's
 * default. Team 3's #e0d9cc went that way, from the Room Management row.
 *
 * For each Front Desk row holding no colours, the newest snapshot of any of
 * its team's rows that still holds some is copied into it, one entry per area.
 * Saving the row moves its revision on, so a builder tab opened earlier has to
 * reload before it can save over the restored colours.
 */
return new class extends Migration
{
    public function up(): void
    {
        foreach (TeamRoleTemplate::where('role', 'front_desk')->get() as $frontDesk) {
            $live = TemplateCustomizationStore::readCustomizations((int) $frontDesk->team_role_template_id, 0);
            if (!empty($live['__siteColors']['items'])) {
                continue;
            }

            $teamRows = TeamRoleTemplate::where('group_name', $frontDesk->group_name)
                ->where('faculty_id', $frontDesk->faculty_id)
                ->pluck('team_role_template_id');

            $items = null;
            $versions = TeamRoleTemplateVersion::whereIn('team_role_template_id', $teamRows)
                ->orderByDesc('created_at')
                ->get();
            foreach ($versions as $version) {
                $saved = TemplateCustomizationStore::readCustomizations(
                    (int) $version->team_role_template_id,
                    (int) $version->getKey()
                );
                if (!empty($saved['__siteColors']['items'])) {
                    $items = $saved['__siteColors']['items'];
                    break;
                }
            }
            if ($items === null) {
                continue;
            }

            // Older saves wrote the same area many times over; keep one each.
            $byArea = [];
            foreach ($items as $item) {
                if (is_array($item) && !empty($item['id']) && !empty($item['bg'])) {
                    $byArea[(string) $item['id']] = (string) $item['bg'];
                }
            }
            if ($byArea === []) {
                continue;
            }

            $live['__siteColors'] = [
                'page' => 'home',
                'items' => array_map(fn ($id, $bg) => ['id' => $id, 'bg' => $bg], array_keys($byArea), $byArea),
            ];
            $frontDesk->customizations = $live;
            $frontDesk->save();
        }
    }

    public function down(): void
    {
        // Nothing to undo: the colours were the team's own.
    }
};
