<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('team_role_templates', function (Blueprint $table) {
            $table->id();
            $table->string('group_name');
            $table->foreignId('faculty_id')->constrained('faculties')->cascadeOnDelete();
            $table->string('role', 64); // front_desk, room_management, etc.
            $table->string('selected_template')->nullable();
            $table->json('customizations')->nullable();
            $table->json('layout')->nullable(); // ordered section ids / visibility
            $table->boolean('is_published')->default(false);
            $table->unsignedInteger('version')->default(1);
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['group_name', 'faculty_id', 'role'], 'team_role_templates_unique');
            $table->index(['faculty_id', 'group_name']);
        });

        Schema::create('team_role_template_versions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('team_role_template_id')->constrained('team_role_templates')->cascadeOnDelete();
            $table->unsignedInteger('version');
            $table->string('selected_template')->nullable();
            $table->json('customizations')->nullable();
            $table->json('layout')->nullable();
            $table->boolean('is_published')->default(false);
            $table->string('label')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['team_role_template_id', 'version'], 'team_role_template_versions_unique');
        });

        Schema::create('team_template_edit_grants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('faculty_id')->constrained('faculties')->cascadeOnDelete();
            $table->string('group_name');
            $table->foreignId('student_id')->constrained('students')->cascadeOnDelete();
            $table->string('role', 64); // role template they may edit
            $table->foreignId('granted_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['faculty_id', 'group_name', 'student_id', 'role'], 'team_template_edit_grants_unique');
        });

        // Seed from existing group_settings (legacy front desk)
        if (Schema::hasTable('group_settings')) {
            $rows = DB::table('group_settings')->get();
            foreach ($rows as $row) {
                $templateId = DB::table('team_role_templates')->insertGetId([
                    'group_name' => $row->group_name,
                    'faculty_id' => $row->faculty_id,
                    'role' => 'front_desk',
                    'selected_template' => $row->selected_template ?? null,
                    'customizations' => $row->customizations ?? null,
                    'layout' => null,
                    'is_published' => (bool) ($row->is_published ?? false),
                    'version' => 1,
                    'updated_by' => null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                DB::table('team_role_template_versions')->insert([
                    'team_role_template_id' => $templateId,
                    'version' => 1,
                    'selected_template' => $row->selected_template ?? null,
                    'customizations' => $row->customizations ?? null,
                    'layout' => null,
                    'is_published' => (bool) ($row->is_published ?? false),
                    'label' => 'Initial import',
                    'created_by' => null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('team_template_edit_grants');
        Schema::dropIfExists('team_role_template_versions');
        Schema::dropIfExists('team_role_templates');
    }
};
