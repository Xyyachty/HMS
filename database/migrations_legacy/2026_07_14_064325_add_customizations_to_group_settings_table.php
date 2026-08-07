<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('group_settings', function (Blueprint $table) {
            $table->json('customizations')->nullable()->after('selected_template');
            $table->boolean('is_published')->default(false)->after('customizations');
        });
    }

    public function down(): void
    {
        Schema::table('group_settings', function (Blueprint $table) {
            $table->dropColumn(['customizations', 'is_published']);
        });
    }
};
