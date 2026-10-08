<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Guest complaints now go to four departments (Housekeeping, Maintenance, Room
 * Management, Restaurant Services) under a new category list, and run
 * Pending → In Progress → Resolved → Closed, where Closed is the Front Desk
 * confirming the fix with the guest.
 *
 * Adds a history column (one entry per filing, status change and hand-over) and
 * moves existing rows onto the new names so nothing already filed drops out of the
 * department queues: Open becomes Pending, and each old category becomes the
 * nearest new one. The category rename is one-way — down() restores the statuses
 * and drops the column, but leaves the new category names in place.
 */
return new class extends Migration
{
    private const CATEGORY_RENAMES = [
        'Aircon / Cooling'      => 'Air Conditioner Not Working',
        'Plumbing / Water'      => 'Plumbing or Water Problems',
        'Electrical / Lighting' => 'Broken Lights',
        'Appliance / TV'        => 'Broken Television or Appliances',
        'Furniture / Fixtures'  => 'Damaged Furniture',
        'Room Cleanliness'      => 'Dirty Room',
        'Linens / Towels'       => 'Missing Towels or Toiletries',
        'Toiletries / Supplies' => 'Missing Towels or Toiletries',
        'Trash / Odor'          => 'Bad Room Odor',
        'Noise / Disturbance'   => 'Other Housekeeping Problems',
    ];

    public function up(): void
    {
        if (!Schema::hasColumn('hotel_complaints', 'history')) {
            Schema::table('hotel_complaints', function (Blueprint $table) {
                $table->json('history')->nullable();
            });
        }

        DB::table('hotel_complaints')->where('status', 'Open')->update(['status' => 'Pending']);

        foreach (self::CATEGORY_RENAMES as $old => $new) {
            DB::table('hotel_complaints')->where('category', $old)->update(['category' => $new]);
        }

        // "Other" went to whichever department it was routed to.
        DB::table('hotel_complaints')->where('category', 'Other')->where('department', 'housekeeping')
            ->update(['category' => 'Other Housekeeping Problems']);
        DB::table('hotel_complaints')->where('category', 'Other')
            ->update(['category' => 'Other Maintenance Problems']);
    }

    public function down(): void
    {
        DB::table('hotel_complaints')->where('status', 'Pending')->update(['status' => 'Open']);
        DB::table('hotel_complaints')->where('status', 'Closed')->update(['status' => 'Resolved']);

        if (Schema::hasColumn('hotel_complaints', 'history')) {
            Schema::table('hotel_complaints', function (Blueprint $table) {
                $table->dropColumn('history');
            });
        }
    }
};
