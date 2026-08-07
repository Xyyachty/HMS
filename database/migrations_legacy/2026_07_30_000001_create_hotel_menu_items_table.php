<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hotel_menu_items', function (Blueprint $table) {
            $table->id();
            $table->string('group_name');
            $table->unsignedBigInteger('faculty_id');
            $table->string('name');
            $table->string('category')->default('Main Dishes');
            $table->unsignedInteger('price')->default(0);
            $table->string('description')->nullable();
            $table->longText('image')->nullable(); // base64 data URL or remote URL
            $table->timestamps();

            $table->index(['group_name', 'faculty_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hotel_menu_items');
    }
};
