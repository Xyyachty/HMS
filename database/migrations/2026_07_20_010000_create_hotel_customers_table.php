<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hotel_customers', function (Blueprint $table) {
            $table->id();
            $table->string('group_name');
            $table->unsignedBigInteger('faculty_id');
            $table->string('name');
            $table->string('email');
            $table->string('password');
            $table->timestamps();

            $table->unique(['group_name', 'faculty_id', 'email'], 'hotel_customers_team_email_unique');
            $table->index(['group_name', 'faculty_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hotel_customers');
    }
};
