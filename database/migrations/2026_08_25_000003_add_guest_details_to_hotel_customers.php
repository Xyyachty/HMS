<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Guest sign-up asks for the details a hotel actually needs.
 *
 * The form took one free-text "Full name" and an email, which is not enough to
 * greet a guest properly or to ring them about a booking. It now takes a first
 * and last name separately and a contact number.
 *
 * `name` stays as the composed display name. Everything that greets a guest —
 * the navigation, the session payload — reads it, and splitting those call sites
 * to rebuild the same string everywhere would be churn for nothing.
 *
 * Backfill splits existing names on the last space: "Maria Dela Cruz" becomes
 * first "Maria", last "Dela Cruz". Wrong for some names, but it is a guess made
 * once over simulation data, and either half stays editable as plain text.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('hotel_customers', function (Blueprint $table) {
            if (!Schema::hasColumn('hotel_customers', 'first_name')) {
                $table->string('first_name')->nullable()->after('name');
            }
            if (!Schema::hasColumn('hotel_customers', 'last_name')) {
                $table->string('last_name')->nullable()->after('first_name');
            }
            if (!Schema::hasColumn('hotel_customers', 'contact_number')) {
                $table->string('contact_number', 32)->nullable()->after('last_name');
            }
        });

        DB::table('hotel_customers')
            ->whereNull('first_name')
            ->orderBy('hotel_customer_id')
            ->select('hotel_customer_id', 'name')
            ->chunk(200, function ($customers) {
                foreach ($customers as $customer) {
                    $name = trim((string) $customer->name);
                    if ($name === '') {
                        continue;
                    }

                    $cut = mb_strrpos($name, ' ');

                    DB::table('hotel_customers')
                        ->where('hotel_customer_id', $customer->hotel_customer_id)
                        ->update([
                            'first_name' => $cut === false ? $name : mb_substr($name, 0, $cut),
                            'last_name' => $cut === false ? '' : mb_substr($name, $cut + 1),
                        ]);
                }
            });
    }

    public function down(): void
    {
        Schema::table('hotel_customers', function (Blueprint $table) {
            foreach (['first_name', 'last_name', 'contact_number'] as $column) {
                if (Schema::hasColumn('hotel_customers', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
