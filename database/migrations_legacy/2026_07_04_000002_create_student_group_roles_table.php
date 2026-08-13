<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('student_group_roles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_group_id')->constrained('student_groups')->cascadeOnDelete();
            $table->string('role');
            $table->timestamps();

            $table->unique(['student_group_id', 'role']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('student_group_roles');
    }
};
