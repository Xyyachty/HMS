<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * "changes" is taken.
 *
 * Eloquent\Model declares a protected $changes property (the attributes that moved
 * on the last save), so a column of that name is shadowed: $revision->changes reads
 * the model's internal array and never the stored diff, and the array cast never
 * runs. The edit history showed no field-by-field detail because of it.
 */
return new class extends Migration
{
    public function up(): void
    {
        // Raw SQL rather than renameColumn(): this is Laravel 10, where the Blueprint
        // rename goes through doctrine/dbal, which this project does not carry.
        if (Schema::hasTable('hotel_concept_revisions') && Schema::hasColumn('hotel_concept_revisions', 'changes')) {
            $this->rename('changes', 'field_changes');
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('hotel_concept_revisions') && Schema::hasColumn('hotel_concept_revisions', 'field_changes')) {
            $this->rename('field_changes', 'changes');
        }
    }

    private function rename(string $from, string $to): void
    {
        $sql = DB::getDriverName() === 'pgsql'
            ? 'ALTER TABLE "hotel_concept_revisions" RENAME COLUMN "' . $from . '" TO "' . $to . '"'
            : 'ALTER TABLE `hotel_concept_revisions` RENAME COLUMN `' . $from . '` TO `' . $to . '`';

        DB::statement($sql);
    }
};
