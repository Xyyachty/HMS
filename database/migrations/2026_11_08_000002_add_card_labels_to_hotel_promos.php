<?php

use App\Models\HotelPromo;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * The offer badge ("Save 20%") and terms line ("Friday to Sunday stays") a team
 * wrote on its Promos and Packages cards, so the Simulation's promos show the
 * same words instead of a stock price label. Null means "not customized": the
 * card then falls back to the price and dates Front Desk sets.
 *
 * Teams that customized those lines before this column existed get them now.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('hotel_promos') || Schema::hasColumn('hotel_promos', 'offer_label')) {
            return;
        }

        Schema::table('hotel_promos', function (Blueprint $table) {
            $table->string('offer_label', 60)->nullable();
            $table->string('terms_label', 160)->nullable();
        });

        DB::table('hotel_promos')
            ->whereNotNull('template_key')
            ->select('group_name', 'faculty_id')
            ->distinct()
            ->get()
            ->each(fn ($team) => HotelPromo::adoptTemplateContent($team->group_name, (int) $team->faculty_id));
    }

    public function down(): void
    {
        if (Schema::hasColumn('hotel_promos', 'offer_label')) {
            Schema::table('hotel_promos', function (Blueprint $table) {
                $table->dropColumn(['offer_label', 'terms_label']);
            });
        }
    }
};
