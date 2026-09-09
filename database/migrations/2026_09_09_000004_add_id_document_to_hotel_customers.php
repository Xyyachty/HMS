<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * The photograph of the ID a guest signs up with.
 *
 * A path on the media disk, like every other picture the hotel stores — Supabase
 * Storage in production, the public disk locally — never the bytes: an ID scan in
 * a column would be read back on every query that touches the account.
 *
 * Nullable because every account made before this existed has none, and because
 * the front desk still verifies the physical ID at check-in either way. What is
 * uploaded here is the head start, not the check.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('hotel_customers')) {
            return;
        }

        Schema::table('hotel_customers', function (Blueprint $table) {
            if (!Schema::hasColumn('hotel_customers', 'id_document')) {
                $table->string('id_document', 2048)->nullable()->after('email');
            }
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('hotel_customers')) {
            return;
        }

        Schema::table('hotel_customers', function (Blueprint $table) {
            if (Schema::hasColumn('hotel_customers', 'id_document')) {
                $table->dropColumn('id_document');
            }
        });
    }
};
