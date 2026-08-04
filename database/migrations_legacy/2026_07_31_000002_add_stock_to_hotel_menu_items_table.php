<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('hotel_menu_items', function (Blueprint $table) {
            // Portions still available to order. Room-service orders draw it down.
            $table->unsignedInteger('stock')->default(0)->after('price');
        });

        // Items that predate this column would otherwise read as out of stock.
        \Illuminate\Support\Facades\DB::table('hotel_menu_items')->update(['stock' => 20]);
    }

    public function down(): void
    {
        Schema::table('hotel_menu_items', function (Blueprint $table) {
            $table->dropColumn('stock');
        });
    }
};
