<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * A dine-in bill paid by more than one method (cash, then the rest by GCash or
 * card): one row per bill as before, with each part in `splits`
 * ([{method, amount, reference}]). Null on a bill paid one way, and on every
 * payment from before this.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('hotel_dine_in_payments') && !Schema::hasColumn('hotel_dine_in_payments', 'splits')) {
            Schema::table('hotel_dine_in_payments', function (Blueprint $table) {
                $table->json('splits')->nullable();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('hotel_dine_in_payments') && Schema::hasColumn('hotel_dine_in_payments', 'splits')) {
            Schema::table('hotel_dine_in_payments', function (Blueprint $table) {
                $table->dropColumn('splits');
            });
        }
    }
};
