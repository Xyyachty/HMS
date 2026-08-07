<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private array $tables = [
        'template_layouts',
        'template_elements',
        'template_images',
        'template_content_items',
    ];

    public function up(): void
    {
        foreach ($this->tables as $table) {
            if (!Schema::hasTable($table) || !Schema::hasColumn($table, 'version_id')) {
                continue;
            }

            DB::table($table)->whereNull('version_id')->update(['version_id' => 0]);
            DB::statement("ALTER TABLE `{$table}` MODIFY `version_id` BIGINT UNSIGNED NOT NULL DEFAULT 0");
        }
    }

    public function down(): void
    {
        foreach ($this->tables as $table) {
            if (!Schema::hasTable($table) || !Schema::hasColumn($table, 'version_id')) {
                continue;
            }

            DB::statement("ALTER TABLE `{$table}` MODIFY `version_id` BIGINT UNSIGNED NULL DEFAULT NULL");
            DB::table($table)->where('version_id', 0)->update(['version_id' => null]);
        }
    }
};
