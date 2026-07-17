<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        $users = DB::table('users')
            ->whereNull('email_verified_at')
            ->orWhereNull('remember_token')
            ->orWhere('remember_token', '')
            ->get(['id', 'created_at', 'email_verified_at', 'remember_token']);

        foreach ($users as $user) {
            DB::table('users')->where('id', $user->id)->update([
                'email_verified_at' => $user->email_verified_at ?: ($user->created_at ?: now()),
                'remember_token' => ($user->remember_token !== null && $user->remember_token !== '')
                    ? $user->remember_token
                    : Str::random(60),
            ]);
        }

        // Catch any remaining nulls
        DB::table('users')->whereNull('email_verified_at')->update([
            'email_verified_at' => now(),
        ]);
        DB::table('users')->whereNull('remember_token')->update([
            'remember_token' => Str::random(60),
        ]);

        Schema::table('users', function (Blueprint $table) {
            $table->timestamp('email_verified_at')->nullable(false)->change();
            $table->string('remember_token', 100)->nullable(false)->change();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->timestamp('email_verified_at')->nullable()->change();
            $table->string('remember_token', 100)->nullable()->change();
        });
    }
};
