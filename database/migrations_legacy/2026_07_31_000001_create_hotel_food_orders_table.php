<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hotel_food_orders', function (Blueprint $table) {
            $table->id();
            $table->string('group_name');
            $table->unsignedBigInteger('faculty_id');
            $table->string('room_number');
            $table->string('guest_name');
            $table->json('items'); // [{ name, price, qty }]
            $table->unsignedInteger('total')->default(0);
            $table->string('status')->default('Pending');
            $table->string('placed_by')->nullable();
            $table->timestamps();

            $table->index(['group_name', 'faculty_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hotel_food_orders');
    }
};
