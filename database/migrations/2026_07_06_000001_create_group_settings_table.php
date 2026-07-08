<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('group_settings', function (Blueprint $table) {
            $table->id();
            $table->string('group_name');
            $table->foreignId('faculty_id')->constrained('faculties')->cascadeOnDelete();
            $table->string('selected_template')->nullable();
            $table->timestamps();

            $table->unique(['group_name', 'faculty_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('group_settings');
    }
};
