<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Room add-ons: the small things a guest asks for on top of the room — a folding bed,
 * an extra towel — owned by Housekeeping and attached to a stay by Front Desk.
 *
 * Two tables, because an add-on is loanable rather than consumable. hotel_addons.quantity
 * is how many the hotel OWNS and never moves on its own; what is currently out is the sum
 * of hotel_booking_addons rows whose booking is still open (HotelBooking::OPEN_STATUSES).
 * Availability is that subtraction, computed on read — see HotelAddonDesk::reservedFor().
 *
 * Deriving it rather than decrementing a counter is what makes "the folding bed comes back
 * at check-out" free: the stay leaves the open statuses and drops out of the sum by itself,
 * with no reversal step to forget and no counter that can drift. hotel_menu_items.stock
 * mutates instead, which is right for a dish — that one is eaten.
 *
 * name and unit_price on the assignment are snapshots, the same call room_rate makes on
 * hotel_bookings: repricing an add-on must not rewrite a stay that was already sold.
 * hotel_addon_id is kept alongside them so returning stock is an id lookup rather than a
 * description-string match — the failure mode HotelFoodOrder::sanitizeItems calls out.
 */
return new class extends Migration
{
    public function up(): void
    {
        $this->createAddons();
        $this->createBookingAddons();
    }

    public function down(): void
    {
        Schema::dropIfExists('hotel_booking_addons');
        Schema::dropIfExists('hotel_addons');
    }

    private function createAddons(): void
    {
        if (Schema::hasTable('hotel_addons')) {
            return;
        }

        Schema::create('hotel_addons', function (Blueprint $table) {
            $table->id('hotel_addon_id');
            $table->string('group_name');
            $table->unsignedBigInteger('faculty_id');
            // The explicit 'group_id' second argument is not optional here: constrained()
            // would otherwise guess a column named "id", which groups does not have.
            $table->foreignId('group_id')->nullable()->constrained('groups', 'group_id')->nullOnDelete();
            $table->string('name');
            // Whole pesos, like hotel_rooms.price and hotel_menu_items.price. Only
            // typed-in money (payments, hand-added charges) is decimal.
            $table->integer('price')->default(0);
            // How many the hotel owns, not how many are free right now.
            $table->integer('quantity')->default(0);
            // A path on the media disk — see App\Support\HotelImageStore. Never base64.
            $table->text('image')->nullable();
            $table->timestamps();
            $table->index(['group_name', 'faculty_id']);
        });

        $this->enableRowLevelSecurity('hotel_addons');
    }

    private function createBookingAddons(): void
    {
        if (Schema::hasTable('hotel_booking_addons')) {
            return;
        }

        Schema::create('hotel_booking_addons', function (Blueprint $table) {
            $table->id('hotel_booking_addon_id');
            $table->string('group_name');
            $table->unsignedBigInteger('faculty_id');
            $table->foreignId('group_id')->nullable()->constrained('groups', 'group_id')->nullOnDelete();
            // cascadeOnDelete: a line item is not a record of anything without its stay.
            $table->foreignId('hotel_booking_id')->constrained('hotel_bookings', 'hotel_booking_id')->cascadeOnDelete();
            // nullOnDelete: retiring a catalogue row must not erase what a guest was billed.
            $table->foreignId('hotel_addon_id')->nullable()->constrained('hotel_addons', 'hotel_addon_id')->nullOnDelete();
            $table->string('name');
            $table->integer('unit_price')->default(0);
            $table->integer('qty')->default(1);
            $table->timestamps();
            $table->index(['group_name', 'faculty_id']);
            $table->index('hotel_booking_id');
            // "how many of this add-on are currently out" — the availability query joins
            // these rows to their booking and groups by this column.
            $table->index('hotel_addon_id');
        });

        $this->enableRowLevelSecurity('hotel_booking_addons');
    }

    /**
     * The 2026_08_05_000001 migration closed the Supabase REST API off from every table
     * that existed then. Tables created afterwards need the same treatment: default
     * privileges are already revoked, RLS still has to be switched on.
     */
    private function enableRowLevelSecurity(string $table): void
    {
        if (DB::getDriverName() === 'pgsql') {
            DB::statement('ALTER TABLE "' . $table . '" ENABLE ROW LEVEL SECURITY');
        }
    }
};
