<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('faculty_classes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('faculty_id')->constrained('faculties')->cascadeOnDelete();
            $table->string('name'); // Class A, Class B, ...
            $table->string('letter', 8); // A, B, C, ...
            $table->unsignedSmallInteger('capacity')->default(40);
            $table->string('status', 20)->default('open'); // open | closed
            $table->unsignedSmallInteger('sort_order')->default(1);
            $table->timestamps();

            $table->unique(['faculty_id', 'letter']);
        });

        Schema::table('students', function (Blueprint $table) {
            $table->foreignId('faculty_id')->nullable()->after('user_id')->constrained('faculties')->nullOnDelete();
            $table->foreignId('faculty_class_id')->nullable()->after('faculty_id')->constrained('faculty_classes')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('students', function (Blueprint $table) {
            $table->dropConstrainedForeignId('faculty_class_id');
            $table->dropConstrainedForeignId('faculty_id');
        });

        Schema::dropIfExists('faculty_classes');
    }
};
