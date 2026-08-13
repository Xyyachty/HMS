<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Renames the "Delivered" stage of a food order to "Delivering".
 *
 * The stage used to be the moment Front Desk handed the tray over, so the past
 * tense was right. Restaurant Services now walks the order up to the room itself,
 * which makes this the stage where a runner is still on their way — Completed is
 * the hand-over. The name follows the meaning.
 *
 * status is a plain varchar with no enum or check constraint behind it, so this is
 * a value rewrite only; the column itself does not change.
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::table('hotel_food_orders')
            ->where('status', 'Delivered')
            ->update(['status' => 'Delivering']);
    }

    public function down(): void
    {
        DB::table('hotel_food_orders')
            ->where('status', 'Delivering')
            ->update(['status' => 'Delivered']);
    }
};
