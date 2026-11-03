<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * A guest's own record of a table they reserved on the hotel's website.
 *
 * The reservation itself still lives on its hotel_dine_in_tables row while it is
 * open, and the table's status (Reserved, Occupied) stays the one status the staff
 * screens read. This row only holds what the table forgets once it is freed: the
 * guest's private link (stored hashed), the reference they were given, and how the
 * visit ended. Additive only; nothing existing is changed.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('hotel_dine_in_reservations')) {
            return;
        }

        Schema::create('hotel_dine_in_reservations', function (Blueprint $table) {
            $table->id('hotel_dine_in_reservation_id');
            $table->string('group_name');
            $table->unsignedBigInteger('faculty_id');
            $table->foreignId('group_id')->nullable()->constrained('groups', 'group_id')->nullOnDelete();
            // No foreign key: a table can be removed and the guest's record kept.
            $table->unsignedBigInteger('hotel_dine_in_table_id')->nullable();
            $table->string('table_name');
            $table->unsignedBigInteger('hotel_customer_id')->nullable();
            $table->string('guest_name')->nullable();
            $table->unsignedTinyInteger('party_size')->nullable();
            // Manila wall-clock, like hotel_dine_in_tables.reserved_for.
            $table->timestamp('reserved_for')->nullable();
            // sha256 of the token in the guest's link; the token itself is never kept.
            $table->char('token_hash', 64)->unique();
            // Null while the reservation is on its table. Completed, Cancelled or
            // No-Show once the table is freed.
            $table->string('outcome', 20)->nullable();
            $table->timestamp('ended_at')->nullable();
            $table->timestamps();
            $table->index(['group_name', 'faculty_id']);
            $table->index(['hotel_dine_in_table_id', 'outcome']);
        });

        if (DB::getDriverName() === 'pgsql') {
            DB::statement('ALTER TABLE "hotel_dine_in_reservations" ENABLE ROW LEVEL SECURITY');
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('hotel_dine_in_reservations');
    }
};
