<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Restaurant Services now records its own complaints, from a Dine-In or a Room
 * Service guest. `source` says which; `table_name` is the dining table a Dine-In
 * guest sat at (stored as written, like room_number, so renaming a table later does
 * not rewrite the complaint). Both stay null on every other complaint and on
 * restaurant complaints filed before this.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('hotel_complaints', function (Blueprint $table) {
            if (!Schema::hasColumn('hotel_complaints', 'source')) {
                $table->string('source')->nullable();
            }
            if (!Schema::hasColumn('hotel_complaints', 'table_name')) {
                $table->string('table_name')->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('hotel_complaints', function (Blueprint $table) {
            if (Schema::hasColumn('hotel_complaints', 'table_name')) {
                $table->dropColumn('table_name');
            }
            if (Schema::hasColumn('hotel_complaints', 'source')) {
                $table->dropColumn('source');
            }
        });
    }
};
