<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Guest complaints taken at the Front Desk and worked by Maintenance or Housekeeping.
 *
 * Shaped after hotel_food_orders: one row per team (group_name + faculty_id), a status
 * the receiving department moves along, and no foreign key to the guest — a complaint
 * names the room and the guest as they were reported, so editing a reservation later
 * cannot rewrite history.
 *
 * `department` is the routing decision, not the category. The category suggests a
 * department and the Front Desk may override it, and the receiving department may hand
 * a mis-routed complaint to the other one, so the two have to be stored separately.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('hotel_complaints')) {
            return;
        }

        Schema::create('hotel_complaints', function (Blueprint $table) {
            $table->id('hotel_complaint_id');
            $table->string('group_name');
            $table->unsignedBigInteger('faculty_id');
            // nullOnDelete matches every other hotel_* table: they orphan rather than
            // cascade, and there is still no group-deletion feature to exercise it.
            // The referenced column has to be named: the 2026_08_10_000000 rename moved
            // groups.id to groups.group_id, so constrained()'s "id" guess no longer holds.
            $table->foreignId('group_id')->nullable()->constrained('groups', 'group_id')->nullOnDelete();
            $table->string('room_number');
            $table->string('guest_name')->nullable();
            // Label from HotelComplaint::CATEGORY_DEPARTMENTS, stored as written.
            $table->string('category');
            // 'maintenance' or 'housekeeping' — who is expected to act on it.
            $table->string('department');
            $table->string('priority')->default('Normal');
            $table->text('details');
            $table->string('status')->default('Open');
            $table->text('resolution_note')->nullable();
            // Display names, not user ids: the roster is scoped to one simulation run
            // and these are only ever shown back to the team.
            $table->string('filed_by')->nullable();
            $table->string('handled_by')->nullable();
            $table->timestamp('resolved_at')->nullable();
            $table->timestamps();
            $table->index(['group_name', 'faculty_id']);
            // The department pages read their own open queue first.
            $table->index(['department', 'status']);
        });

        // The 2026_08_05_000001 migration closed the Supabase REST API off from every
        // table that existed then. A table created afterwards needs the same treatment:
        // default privileges are already revoked, RLS still has to be switched on.
        if (DB::getDriverName() === 'pgsql') {
            DB::statement('ALTER TABLE "hotel_complaints" ENABLE ROW LEVEL SECURITY');
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('hotel_complaints');
    }
};
