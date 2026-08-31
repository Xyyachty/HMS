<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * What Restaurant Services sells to a function room booking — a buffet, a plated set menu.
 *
 * Not a hotel_menu_items row, and the difference is not cosmetic. A menu item is one dish
 * with a stock count, and the order pipeline decrements that stock per unit ordered; a
 * catering package is priced per head and has no stock at all, so putting one in the menu
 * table would have the kitchen trying to deduct a hundred buffets from a shelf. It would
 * also put things that are not dishes in front of the room-service picker.
 *
 * So it is its own catalogue, shaped like hotel_addons: Restaurant Services owns and
 * prices it, Front Desk reads it to sell it and cannot create one. That is the same split
 * the add-ons catalogue makes between Housekeeping and Front Desk, and it is what keeps
 * the requirement that catering "comes from the existing Restaurant Management module"
 * true rather than decorative.
 *
 * price_per_head is an integer — a rate card, whole pesos, like every other catalogue
 * here. The charge that lands on a bill (price_per_head x guests) is decimal, because by
 * then it is money someone owes rather than a price someone published.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('hotel_catering_packages')) {
            return;
        }

        Schema::create('hotel_catering_packages', function (Blueprint $table) {
            $table->id('hotel_catering_package_id');
            $table->string('group_name');
            $table->unsignedBigInteger('faculty_id');
            // The explicit 'group_id' second argument is not optional here: constrained()
            // would otherwise guess a column named "id", which groups does not have.
            $table->foreignId('group_id')->nullable()->constrained('groups', 'group_id')->nullOnDelete();
            $table->string('name');
            $table->text('description')->nullable();
            // What is on the table — the line the desk reads to the customer. Free text
            // rather than a join, because it is copy, not a menu the kitchen picks from.
            $table->text('inclusions')->nullable();
            // Whole pesos per head. The event's catering charge is this times the guest
            // count, snapshotted onto the order so repricing cannot rewrite a sold event.
            $table->integer('price_per_head')->default(0);
            $table->unsignedSmallInteger('min_guests')->default(1);
            // A path on the media disk — see App\Support\HotelImageStore. Never base64.
            $table->text('image')->nullable();
            // Retired rather than deleted, so booked events keep pointing at something.
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->index(['group_name', 'faculty_id']);
        });

        if (DB::getDriverName() === 'pgsql') {
            DB::statement('ALTER TABLE "hotel_catering_packages" ALTER COLUMN "group_name" TYPE citext');
        }

        $this->enableRowLevelSecurity('hotel_catering_packages');
    }

    public function down(): void
    {
        Schema::dropIfExists('hotel_catering_packages');
    }

    private function enableRowLevelSecurity(string $table): void
    {
        if (DB::getDriverName() === 'pgsql') {
            DB::statement('ALTER TABLE "' . $table . '" ENABLE ROW LEVEL SECURITY');
        }
    }
};
