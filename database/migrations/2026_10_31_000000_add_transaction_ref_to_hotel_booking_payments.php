<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * One payment covering several rooms — a family paying for three at the walk-in desk —
 * is split into one row per room so each stay keeps its own balance. transaction_ref is
 * the same on every one of those rows, so the split reads back as the single payment it
 * was. Null on rows from before this column, which are each their own payment.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('hotel_booking_payments') && !Schema::hasColumn('hotel_booking_payments', 'transaction_ref')) {
            Schema::table('hotel_booking_payments', function (Blueprint $table) {
                $table->string('transaction_ref', 36)->nullable()->index();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('hotel_booking_payments', 'transaction_ref')) {
            Schema::table('hotel_booking_payments', function (Blueprint $table) {
                $table->dropIndex(['transaction_ref']);
                $table->dropColumn('transaction_ref');
            });
        }
    }
};
