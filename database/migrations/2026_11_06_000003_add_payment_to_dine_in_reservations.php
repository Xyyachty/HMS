<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * The payment that settled a guest's online table reservation, so the guest's own
 * reservation page can show their receipt. Null until it is paid, and on every
 * reservation from before this.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('hotel_dine_in_reservations') && !Schema::hasColumn('hotel_dine_in_reservations', 'hotel_dine_in_payment_id')) {
            Schema::table('hotel_dine_in_reservations', function (Blueprint $table) {
                $table->unsignedBigInteger('hotel_dine_in_payment_id')->nullable();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('hotel_dine_in_reservations') && Schema::hasColumn('hotel_dine_in_reservations', 'hotel_dine_in_payment_id')) {
            Schema::table('hotel_dine_in_reservations', function (Blueprint $table) {
                $table->dropColumn('hotel_dine_in_payment_id');
            });
        }
    }
};
