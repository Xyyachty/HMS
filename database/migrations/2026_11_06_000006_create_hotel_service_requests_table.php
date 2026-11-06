<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Housekeeping requests during a stay: room cleaning, fresh towels and linen,
 * toiletries and the like, asked for by the guest on their My Stay page or taken by
 * the Front Desk. Housekeeping moves each one Pending > Accepted > In Progress >
 * Completed (or Cancelled), and can pass a defect it finds to Maintenance.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('hotel_service_requests')) {
            return;
        }
        Schema::create('hotel_service_requests', function (Blueprint $table) {
            $table->id('hotel_service_request_id');
            $table->string('group_name');
            $table->unsignedBigInteger('faculty_id');
            $table->unsignedBigInteger('group_id')->nullable();
            $table->unsignedBigInteger('hotel_booking_id')->nullable()->index();
            // Kept as written, like a complaint's room_number, so a renamed room does
            // not rewrite the request.
            $table->string('room_name')->nullable();
            $table->string('guest_name')->nullable();
            $table->string('type', 60);
            $table->string('preferred_time', 60)->nullable();
            $table->text('notes')->nullable();
            // Guest (My Stay page) or Front Desk (a call or a word at the desk).
            $table->string('source', 20)->default('Front Desk');
            $table->string('status', 20)->default('Pending');
            $table->string('requested_by')->nullable();
            $table->string('handled_by')->nullable();
            $table->text('completion_notes')->nullable();
            // The Maintenance complaint a defect found during the job was sent as.
            $table->unsignedBigInteger('hotel_complaint_id')->nullable();
            $table->timestamp('accepted_at')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->timestamps();
            $table->index(['group_name', 'faculty_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hotel_service_requests');
    }
};
