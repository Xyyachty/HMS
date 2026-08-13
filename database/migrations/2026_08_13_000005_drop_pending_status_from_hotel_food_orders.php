<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Retires the "Pending" stage of a food order.
 *
 * Pending existed because the kitchen had to accept a ticket someone else had taken.
 * Restaurant Services now owns the order from the moment it is placed, so there is
 * nothing to accept — an order arrives already Preparing, and the stage in front of
 * it was only ever a button press with no work behind it.
 *
 * Anything still sitting at Pending is moved forward rather than left on a status the
 * flow no longer knows about, which would strand it: isForwardTransition() cannot move
 * a row whose status is off the FLOW.
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::table('hotel_food_orders')
            ->where('status', 'Pending')
            ->update(['status' => 'Preparing']);

        // The column default still named the retired status. Nothing relies on it —
        // every insert sets status explicitly — but a fresh row falling back to a
        // stage the flow does not know about would be stranded. Raw SQL because the
        // project has no doctrine/dbal to change a column with.
        DB::statement("ALTER TABLE hotel_food_orders ALTER COLUMN status SET DEFAULT 'Preparing'");
    }

    /**
     * Deliberately empty. Preparing is a real stage both before and after this
     * migration, so there is no way to tell which rows used to be Pending — guessing
     * would push live orders backwards.
     */
    public function down(): void
    {
    }
};
