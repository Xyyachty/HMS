<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * What a dine-in bill was made of when it was paid, beside total_due: the food
 * subtotal, the Senior Citizen / PWD discounts given (who, ID number, how much) and
 * the service charge. All null on payments recorded before this, which had neither.
 */
return new class extends Migration
{
    private const COLUMNS = ['subtotal', 'discounts', 'discount_total', 'service_charge_rate', 'service_charge'];

    public function up(): void
    {
        if (!Schema::hasTable('hotel_dine_in_payments')) {
            return;
        }
        Schema::table('hotel_dine_in_payments', function (Blueprint $table) {
            if (!Schema::hasColumn('hotel_dine_in_payments', 'subtotal')) {
                $table->decimal('subtotal', 12, 2)->nullable();
            }
            if (!Schema::hasColumn('hotel_dine_in_payments', 'discounts')) {
                $table->json('discounts')->nullable();
            }
            if (!Schema::hasColumn('hotel_dine_in_payments', 'discount_total')) {
                $table->decimal('discount_total', 12, 2)->nullable();
            }
            if (!Schema::hasColumn('hotel_dine_in_payments', 'service_charge_rate')) {
                $table->decimal('service_charge_rate', 5, 2)->nullable();
            }
            if (!Schema::hasColumn('hotel_dine_in_payments', 'service_charge')) {
                $table->decimal('service_charge', 12, 2)->nullable();
            }
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('hotel_dine_in_payments')) {
            return;
        }
        Schema::table('hotel_dine_in_payments', function (Blueprint $table) {
            foreach (self::COLUMNS as $column) {
                if (Schema::hasColumn('hotel_dine_in_payments', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
