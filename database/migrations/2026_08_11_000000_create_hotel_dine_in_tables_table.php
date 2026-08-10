<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * A physical dining table: Restaurant Management adds and removes them, Front Desk
 * assigns a guest to an Available one, Restaurant Management closes it out when the
 * guest leaves. Shaped after hotel_complaints — same team scoping, same RLS-enable
 * step needed because this table is created after the blanket 2026_08_05_000001
 * migration that closed the Supabase REST API off from every table that existed then.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('hotel_dine_in_tables')) {
            return;
        }

        Schema::create('hotel_dine_in_tables', function (Blueprint $table) {
            $table->id('hotel_dine_in_table_id');
            $table->string('group_name');
            $table->unsignedBigInteger('faculty_id');
            // nullOnDelete matches every other hotel_* table: they orphan rather than
            // cascade, and there is still no group-deletion feature to exercise it.
            // groups' primary key is group_id, not id — constrained() has to be told,
            // its default guess fails on Postgres.
            $table->foreignId('group_id')->nullable()->constrained('groups', 'group_id')->nullOnDelete();
            $table->string('name');
            $table->unsignedTinyInteger('capacity');
            $table->string('status')->default('Available');
            $table->string('guest_name')->nullable();
            $table->unsignedTinyInteger('party_size')->nullable();
            $table->string('assigned_by')->nullable();
            $table->timestamp('assigned_at')->nullable();
            $table->timestamps();
            $table->index(['group_name', 'faculty_id']);
        });

        if (DB::getDriverName() === 'pgsql') {
            DB::statement('ALTER TABLE "hotel_dine_in_tables" ENABLE ROW LEVEL SECURITY');
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('hotel_dine_in_tables');
    }
};
