<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Complaints shipped with a Low/Normal/Urgent priority the Front Desk had to set on
 * every one. It earned nothing: the department works its queue in the order things
 * come in, and a guest with a broken aircon does not call it "Normal".
 *
 * The create migration no longer adds the column, so on a fresh install there is
 * nothing here to drop — hence the guard. This exists for the one database that had
 * already run the original version.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('hotel_complaints', 'priority')) {
            Schema::table('hotel_complaints', function (Blueprint $table) {
                $table->dropColumn('priority');
            });
        }
    }

    public function down(): void
    {
        if (!Schema::hasColumn('hotel_complaints', 'priority')) {
            Schema::table('hotel_complaints', function (Blueprint $table) {
                $table->string('priority')->default('Normal');
            });
        }
    }
};
