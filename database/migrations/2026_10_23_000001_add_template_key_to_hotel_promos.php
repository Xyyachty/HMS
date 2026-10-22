<?php

use App\Models\HotelPromo;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Which Home page promo card a starting promo is ("promo-1" to "promo-4"), so the
 * card's title, description and picture edited in the builder carry over to the
 * promo Front Desk runs. Null on promos Front Desk added itself.
 *
 * Starting promos seeded before this column are matched back to their card by
 * name, and take whatever the team had already customized on those cards.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('hotel_promos') || Schema::hasColumn('hotel_promos', 'template_key')) {
            return;
        }

        Schema::table('hotel_promos', function (Blueprint $table) {
            $table->string('template_key', 20)->nullable();
        });

        foreach (HotelPromo::DEFAULTS as $i => $promo) {
            DB::table('hotel_promos')
                ->whereNull('template_key')
                ->where('name', $promo['name'])
                ->where('description', $promo['description'])
                ->update(['template_key' => 'promo-' . ($i + 1)]);
        }

        DB::table('hotel_promos')
            ->whereNotNull('template_key')
            ->select('group_name', 'faculty_id')
            ->distinct()
            ->get()
            ->each(fn ($team) => HotelPromo::adoptTemplateContent($team->group_name, (int) $team->faculty_id));
    }

    public function down(): void
    {
        if (Schema::hasColumn('hotel_promos', 'template_key')) {
            Schema::table('hotel_promos', function (Blueprint $table) {
                $table->dropColumn('template_key');
            });
        }
    }
};
