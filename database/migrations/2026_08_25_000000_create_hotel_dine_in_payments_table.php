<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * What a dine-in customer paid before the table was closed out.
 *
 * A dine-in bill has nowhere to live today: room service is charged to a stay and
 * settles through hotel_booking_payments, but a table has no booking behind it, so
 * closing it out simply threw the money away. One row here per settled table.
 *
 * Every customer-facing figure is snapshotted rather than joined, because the rows
 * it was derived from keep moving after the fact: a menu item can be repriced, an
 * order can be cancelled, and the table itself can be renamed or removed. What the
 * customer was charged must not change with any of that.
 *
 * Shaped after hotel_booking_payments — same team scoping, same decimal money, same
 * RLS-enable step needed because this table is created long after the blanket
 * 2026_08_05_000001 migration that closed the Supabase REST API off from every
 * table existing then.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('hotel_dine_in_payments')) {
            return;
        }

        Schema::create('hotel_dine_in_payments', function (Blueprint $table) {
            $table->id('hotel_dine_in_payment_id');
            $table->string('group_name');
            $table->unsignedBigInteger('faculty_id');
            // nullOnDelete matches every other hotel_* table: they orphan rather than
            // cascade, and there is still no group-deletion feature to exercise it.
            // groups' primary key is group_id, not id — constrained() has to be told,
            // its default guess fails on Postgres.
            $table->foreignId('group_id')->nullable()->constrained('groups', 'group_id')->nullOnDelete();
            // nullOnDelete, not cascade: removing a table must not erase the money it
            // took. table_name below is what the receipt still reads by then.
            $table->foreignId('hotel_dine_in_table_id')->nullable()
                ->constrained('hotel_dine_in_tables', 'hotel_dine_in_table_id')->nullOnDelete();
            $table->string('table_name');
            $table->string('guest_name')->nullable();
            $table->unsignedTinyInteger('party_size')->nullable();
            // Money is decimal for the same reason booking payments are: the amount
            // handed over is typed freehand even though every menu price is whole pesos.
            $table->decimal('total_due', 12, 2)->default(0);
            $table->decimal('amount_paid', 12, 2)->default(0);
            $table->decimal('balance', 12, 2)->default(0);
            $table->string('method')->default('Cash');
            $table->string('reference')->nullable();
            // The itemised bill as it was presented: one entry per order line, so the
            // receipt survives the orders being completed, cancelled or repriced.
            $table->json('items')->nullable();
            // Which food orders this settled, by id — the audit trail back to the
            // kitchen tickets without depending on them still being reachable.
            $table->json('order_ids')->nullable();
            $table->string('collected_by')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();
            $table->index(['group_name', 'faculty_id']);
            $table->index('hotel_dine_in_table_id');
        });

        if (DB::getDriverName() === 'pgsql') {
            DB::statement('ALTER TABLE "hotel_dine_in_payments" ENABLE ROW LEVEL SECURITY');
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('hotel_dine_in_payments');
    }
};
