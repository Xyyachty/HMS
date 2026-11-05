<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Two or more dining tables reserved together for one guest share a party_ref:
 * they are seated, billed and closed as one, and paid with one payment and one
 * receipt. Null on a table reserved alone, and on every row written before this.
 */
return new class extends Migration
{
    private const TABLES = ['hotel_dine_in_tables', 'hotel_dine_in_reservations', 'hotel_dine_in_payments'];

    public function up(): void
    {
        foreach (self::TABLES as $name) {
            if (Schema::hasTable($name) && !Schema::hasColumn($name, 'party_ref')) {
                Schema::table($name, function (Blueprint $table) {
                    $table->string('party_ref', 40)->nullable()->index();
                });
            }
        }
    }

    public function down(): void
    {
        foreach (self::TABLES as $name) {
            if (Schema::hasTable($name) && Schema::hasColumn($name, 'party_ref')) {
                Schema::table($name, function (Blueprint $table) {
                    $table->dropColumn('party_ref');
                });
            }
        }
    }
};
