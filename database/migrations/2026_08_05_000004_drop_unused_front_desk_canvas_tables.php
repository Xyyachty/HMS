<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * front_desk_activities / front_desk_canvases had no code referencing them
     * anywhere in app/ or routes/ and were empty on the live database. Dropping
     * the dead tables instead of carrying them forward indefinitely.
     */
    public function up(): void
    {
        Schema::dropIfExists('front_desk_activities');
        Schema::dropIfExists('front_desk_canvases');
    }

    public function down(): void
    {
        Schema::create('front_desk_canvases', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('faculty_id')->nullable()->constrained('faculties')->nullOnDelete();
            $table->foreignId('student_group_id')->nullable()->constrained('student_groups')->nullOnDelete();
            $table->string('canvas_mode', 16)->default('custom');
            $table->json('widgets')->nullable();
            $table->longText('default_html')->nullable();
            $table->string('status', 16)->default('draft');
            $table->timestamps();
            $table->index(['user_id', 'faculty_id']);
        });

        Schema::create('front_desk_activities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('canvas_id')->constrained('front_desk_canvases')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('action');
            $table->text('description')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
            $table->index(['user_id', 'action']);
            $table->index('canvas_id');
        });
    }
};
