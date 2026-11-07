<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Verify final charges: when the Front Desk starts a check-out, Restaurant Services
 * confirms that every food order on the stay is delivered and on the bill. The
 * confirmation is kept on the booking; an order placed after it asks again.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('hotel_bookings') && !Schema::hasColumn('hotel_bookings', 'orders_confirmed_at')) {
            Schema::table('hotel_bookings', function (Blueprint $table) {
                $table->timestamp('orders_confirmed_at')->nullable();
                $table->string('orders_confirmed_by')->nullable();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('hotel_bookings') && Schema::hasColumn('hotel_bookings', 'orders_confirmed_at')) {
            Schema::table('hotel_bookings', function (Blueprint $table) {
                $table->dropColumn(['orders_confirmed_at', 'orders_confirmed_by']);
            });
        }
    }
};
