<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * What Front Desk records when it reserves a table for a customer.
 *
 * The reservation held a name and a party size and nothing else, so there was no way
 * to ring the customer back and no way to say what time they were expected — the only
 * timestamp on the row was assigned_at, which is when the desk typed it in, not when
 * the customer is due.
 *
 * Both columns are nullable: a table reserved before this migration has neither, and
 * the screens fall back to assigned_at for the time.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('hotel_dine_in_tables')) {
            return;
        }

        Schema::table('hotel_dine_in_tables', function (Blueprint $table) {
            if (!Schema::hasColumn('hotel_dine_in_tables', 'contact_no')) {
                $table->string('contact_no')->nullable()->after('guest_name');
            }
            if (!Schema::hasColumn('hotel_dine_in_tables', 'reserved_for')) {
                // When the customer is due, as opposed to assigned_at — when the desk
                // wrote the reservation down.
                $table->timestamp('reserved_for')->nullable()->after('party_size');
            }
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('hotel_dine_in_tables')) {
            return;
        }

        Schema::table('hotel_dine_in_tables', function (Blueprint $table) {
            if (Schema::hasColumn('hotel_dine_in_tables', 'contact_no')) {
                $table->dropColumn('contact_no');
            }
            if (Schema::hasColumn('hotel_dine_in_tables', 'reserved_for')) {
                $table->dropColumn('reserved_for');
            }
        });
    }
};
