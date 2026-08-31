<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Tells the desks apart: how a guest gets at each facility, and what it costs.
 *
 * The five seeded amenities behave in four different ways and the table could not say so.
 * A guest walks into the playground; a guest is signed in and out of the pool because
 * somebody has to know who is in the water; the spa is booked by the hour against a named
 * service; the function room is booked for an event with a package, catering and a bill.
 * Without access_type every screen would need a hardcoded list of amenity names, and a team
 * that renamed "Gym" to "Fitness Centre" would break it.
 *
 * So the column is the switch. One value per row, set by Housekeeping, and Front Desk's
 * screen, the route guards and the available actions all read it rather than the name.
 *
 * rate and setup_fee are integers because they are a rate card — the same call
 * hotel_rooms.price and hotel_menu_items.price make. Only money someone types in freehand
 * (a payment, a hand-added charge) is decimal in this project. They sit at 0 for the pool,
 * the gym and the playground, which cost a guest nothing.
 *
 * capacity is nullable rather than 0 because "no limit" and "nobody may enter" are
 * different facts, and 0 would read as the second.
 */
return new class extends Migration
{
    /**
     * Which of the five is which. Keyed by the name HotelAmenityAccess::seedDefaults()
     * writes, so a team that has since renamed one keeps whatever it has — this only
     * fills in rows that still carry the seeded name.
     */
    private const SEEDED = [
        // Signed in and out: somebody has to know who is in the water, and who is
        // on the equipment if it fails.
        'Swimming Pool'  => ['access_type' => 'registered', 'capacity' => 30],
        'Gym'            => ['access_type' => 'registered', 'capacity' => 20],
        // Open access. A guest walks in; there is nothing to book and nobody to sign.
        'Playground'     => ['access_type' => 'open',        'capacity' => null],
        // Booked by the hour against a named service, so it needs a slot, not a queue.
        'Spa'            => ['access_type' => 'appointment', 'capacity' => null],
        // An event: a date, a package, catering, setup and a bill at the end.
        'Function Room'  => ['access_type' => 'event',       'capacity' => 120,
                             'rate' => 5000, 'setup_fee' => 1500],
    ];

    public function up(): void
    {
        Schema::table('hotel_amenities', function (Blueprint $table) {
            if (!Schema::hasColumn('hotel_amenities', 'access_type')) {
                // Defaults to the least privileged shape: a facility nobody has classified
                // yet is one a guest may look at and no desk may book.
                $table->string('access_type')->default('open')->after('status');
            }
            if (!Schema::hasColumn('hotel_amenities', 'rate')) {
                // Whole pesos, like hotel_rooms.price. 0 for anything free to use.
                $table->integer('rate')->default(0)->after('access_type');
            }
            if (!Schema::hasColumn('hotel_amenities', 'setup_fee')) {
                // Charged only when an event picks a package that includes setup.
                $table->integer('setup_fee')->default(0)->after('rate');
            }
            if (!Schema::hasColumn('hotel_amenities', 'capacity')) {
                // Null means no limit, which is not the same as 0.
                $table->unsignedSmallInteger('capacity')->nullable()->after('setup_fee');
            }
        });

        $this->backfillSeededRows();
    }

    public function down(): void
    {
        Schema::table('hotel_amenities', function (Blueprint $table) {
            foreach (['capacity', 'setup_fee', 'rate', 'access_type'] as $column) {
                if (Schema::hasColumn('hotel_amenities', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }

    /**
     * Every team that has already loaded the Housekeeping screen has the five seeded rows
     * sitting at the 'open' default, which would leave the pool unregisterable and the
     * function room unbookable until somebody edited each one by hand.
     *
     * Matched on name and only where the row is still at the default, so a team that has
     * already classified an amenity is never overwritten.
     */
    private function backfillSeededRows(): void
    {
        foreach (self::SEEDED as $name => $values) {
            DB::table('hotel_amenities')
                ->where('name', $name)
                ->where('access_type', 'open')
                ->update($values + ['updated_at' => now()]);
        }
    }
};
