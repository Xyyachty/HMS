<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            $table->enum('role', ['front_desk', 'restaurant_management', 'room_management', 'maintenance', 'housekeeping'])->change();
        });
    }

    public function down(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            $table->enum('role', ['front_desk', 'restaurant_management', 'room_management', 'maintenance'])->change();
        });
    }
};
