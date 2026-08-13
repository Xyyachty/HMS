<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * One centralized activity log for every role (dean, faculty, student).
 * Role-based authorization decides who may read which rows — never a
 * separate table per portal.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('activity_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('role', 32)->default('');
            $table->string('activity', 64);
            $table->string('description', 500)->default('');
            $table->timestamp('created_at')->nullable();

            $table->index(['user_id', 'created_at']);
            $table->index('activity');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('activity_logs');
    }
};
