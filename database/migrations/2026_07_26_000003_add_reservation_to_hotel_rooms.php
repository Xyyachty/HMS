<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // A fresh PostgreSQL database is created by the baseline migration dated
        // 2026-08-05. This older compatibility migration runs first, so it must wait
        // until hotel_rooms exists instead of stopping the entire deployment.
        if (!Schema::hasTable('hotel_rooms') || Schema::hasColumn('hotel_rooms', 'reservation')) {
            return;
        }

        Schema::table('hotel_rooms', function (Blueprint $table) {
            $table->json('reservation')->nullable()->after('status');
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('hotel_rooms') || !Schema::hasColumn('hotel_rooms', 'reservation')) {
            return;
        }

        Schema::table('hotel_rooms', function (Blueprint $table) {
            $table->dropColumn('reservation');
        });
    }
};
