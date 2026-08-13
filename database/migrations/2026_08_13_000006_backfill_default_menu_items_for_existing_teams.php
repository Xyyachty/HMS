<?php

use App\Models\HotelMenuItem;
use App\Support\HotelMenuAccess;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Tops every existing team's menu up to the full house catalog.
 *
 * HotelMenuAccess::seedDefaults() only fires for a team with no menu rows at all, so
 * a team that started before the catalog grew to ten dishes per category would never
 * see the new ones. This fills the gap once.
 *
 * Only dishes the team is missing are inserted, matched on name case-insensitively.
 * Anything a team already has — house dish or its own invention, at whatever price it
 * set — is left exactly as it is.
 */
return new class extends Migration
{
    public function up(): void
    {
        $defaults = HotelMenuAccess::defaultMenu();
        $now = now();

        // One row per team that already has a menu. A team with none is left to
        // seedDefaults(), which gives it the whole catalog on first load anyway.
        $teams = DB::table('hotel_menu_items')
            ->select('group_name', 'faculty_id')
            ->selectRaw('MAX(group_id) as group_id')
            ->groupBy('group_name', 'faculty_id')
            ->get();

        foreach ($teams as $team) {
            $existing = DB::table('hotel_menu_items')
                ->where('group_name', $team->group_name)
                ->where('faculty_id', $team->faculty_id)
                ->pluck('name')
                ->map(fn ($name) => mb_strtolower(trim((string) $name)))
                ->all();

            $rows = [];
            foreach ($defaults as $item) {
                if (in_array(mb_strtolower($item['name']), $existing, true)) {
                    continue;
                }

                $rows[] = $item + [
                    'group_name' => $team->group_name,
                    'faculty_id' => $team->faculty_id,
                    'group_id'   => $team->group_id,
                    'stock'      => 20,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }

            if ($rows) {
                HotelMenuItem::insert($rows);
            }
        }
    }

    /**
     * Deliberately empty. Once a team has these rows they are ordinary menu items —
     * staff can re-price them, restock them or build orders against them, and there is
     * nothing on the row that marks it as having come from here. Deleting by name
     * would throw away edits that are not this migration's to undo.
     */
    public function down(): void
    {
    }
};
