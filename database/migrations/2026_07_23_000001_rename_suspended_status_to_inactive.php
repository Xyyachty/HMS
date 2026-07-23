<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('users') && Schema::hasColumn('users', 'status')) {
            DB::table('users')->where('status', 'suspended')->update(['status' => 'inactive']);
        }

        if (Schema::hasTable('faculties') && Schema::hasColumn('faculties', 'status')) {
            DB::table('faculties')->where('status', 'suspended')->update(['status' => 'inactive']);
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('users') && Schema::hasColumn('users', 'status')) {
            DB::table('users')->where('status', 'inactive')->update(['status' => 'suspended']);
        }

        if (Schema::hasTable('faculties') && Schema::hasColumn('faculties', 'status')) {
            DB::table('faculties')->where('status', 'inactive')->update(['status' => 'suspended']);
        }
    }
};
