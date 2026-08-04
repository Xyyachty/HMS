<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Replace SQL NULL (and "None") on optional text fields with empty string.
     * NULL is a poor demo example and often displays as "None".
     */
    public function up(): void
    {
        $nullish = ['None', 'none', 'NONE', 'null', 'NULL', 'Null'];

        DB::table('users')->where(function ($q) use ($nullish) {
            $q->whereNull('middle_name')->orWhereIn('middle_name', $nullish);
        })->update(['middle_name' => '']);

        DB::table('users')->where(function ($q) use ($nullish) {
            $q->whereNull('phone_number')->orWhereIn('phone_number', $nullish);
        })->update(['phone_number' => '']);

        DB::table('users')->whereNull('first_name')->update(['first_name' => '']);
        DB::table('users')->whereNull('last_name')->update(['last_name' => '']);

        if (DB::getSchemaBuilder()->hasColumn('faculties', 'phone_number')) {
            DB::table('faculties')->where(function ($q) use ($nullish) {
                $q->whereNull('phone_number')->orWhereIn('phone_number', $nullish);
            })->update(['phone_number' => '']);
        }

        if (DB::getSchemaBuilder()->hasColumn('tasks', 'description')) {
            DB::table('tasks')->whereNull('description')->update(['description' => '']);
        }

        DB::table('users')
            ->whereNull('email_verified_at')
            ->update(['email_verified_at' => now()]);
    }

    public function down(): void
    {
        // Irreversible data cleanup.
    }
};
