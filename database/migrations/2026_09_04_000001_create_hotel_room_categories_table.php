<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * The room categories a team invented for itself, on top of the five every team starts
 * with (Classic / Superior / Deluxe / Premium / Family — see App\Support\HotelRoomDefaults).
 *
 * Only the extra ones are rows. The five defaults stay in HotelRoomDefaults where they
 * already were: they are the same for every team, they seed the starting inventory, and
 * writing them here per team would mean fifty near-identical rows saying nothing.
 * A category is read as "the defaults, then whatever this team added", in that order.
 *
 * floor_number is the hundreds block the category numbers its rooms from — Classic is 1
 * (101, 102, ...), Superior is 2, so the first category a team adds gets 6 (601, 602, ...).
 * It is stored rather than derived so a category keeps its block when a later one is
 * added, which is what stops two categories numbering into each other.
 *
 * rate and description are only the openers a new room in the category starts with; the
 * room owns its own price and text from then on, exactly as the default categories work.
 *
 * There is deliberately no delete path yet. Rooms carry their category as a plain string
 * on hotel_rooms, so dropping a category would leave its rooms pointing at a name nothing
 * lists any more.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('hotel_room_categories')) {
            return;
        }

        Schema::create('hotel_room_categories', function (Blueprint $table) {
            $table->id('hotel_room_category_id');
            $table->string('group_name');
            $table->unsignedBigInteger('faculty_id');
            // The explicit 'group_id' second argument is not optional here: constrained()
            // would otherwise guess a column named "id", which groups does not have.
            $table->foreignId('group_id')->nullable()->constrained('groups', 'group_id')->nullOnDelete();
            $table->string('name', 60);
            $table->unsignedInteger('floor_number');
            $table->integer('rate')->default(0);
            $table->text('description')->nullable();
            $table->timestamps();
            $table->index(['group_name', 'faculty_id']);
            $table->unique(['group_name', 'faculty_id', 'name'], 'hotel_room_categories_team_name_unique');
        });

        // group_name to match every other team-scoped table; name so "Suite" and "suite"
        // cannot both exist and split one category into two tabs.
        if (DB::getDriverName() === 'pgsql') {
            DB::statement('ALTER TABLE "hotel_room_categories" ALTER COLUMN "group_name" TYPE citext');
            DB::statement('ALTER TABLE "hotel_room_categories" ALTER COLUMN "name" TYPE citext');
            DB::statement('ALTER TABLE "hotel_room_categories" ENABLE ROW LEVEL SECURITY');
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('hotel_room_categories');
    }
};
