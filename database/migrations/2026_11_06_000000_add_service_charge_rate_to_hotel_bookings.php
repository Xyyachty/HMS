<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * The service charge rate (percent) a stay is billed at, fixed when it is booked
 * from config('hms.service_charge_rate'). Null on every stay booked before this,
 * so their bills, payments and receipts stay exactly as they were.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('hotel_bookings', function (Blueprint $table) {
            if (!Schema::hasColumn('hotel_bookings', 'service_charge_rate')) {
                $table->decimal('service_charge_rate', 5, 2)->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('hotel_bookings', function (Blueprint $table) {
            if (Schema::hasColumn('hotel_bookings', 'service_charge_rate')) {
                $table->dropColumn('service_charge_rate');
            }
        });
    }
};
