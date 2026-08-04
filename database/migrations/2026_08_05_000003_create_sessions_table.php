<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Session storage in the database.
 *
 * File-based sessions do not survive on Render: the filesystem is ephemeral, so every
 * redeploy or restart would log everyone out, and they are not shared if the service
 * ever runs more than one instance.
 *
 * Costs one extra query per request. Deliberately not the cache driver — the cache is
 * also file-based here, and adding Redis for a class-sized app is not worth it.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('sessions')) {
            Schema::create('sessions', function (Blueprint $table) {
                $table->string('id')->primary();
                // No foreign key: a session may outlive the user row, and Laravel's
                // own schema leaves this unconstrained.
                $table->foreignId('user_id')->nullable()->index();
                $table->string('ip_address', 45)->nullable();
                $table->text('user_agent')->nullable();
                $table->longText('payload');
                $table->integer('last_activity')->index();
            });
        }

        // Sessions hold auth state, so this table matters more than most. The earlier
        // security migration only covered tables that existed when it ran.
        if (DB::getDriverName() === 'pgsql') {
            DB::statement('ALTER TABLE "sessions" ENABLE ROW LEVEL SECURITY');
            foreach (['anon', 'authenticated'] as $role) {
                DB::statement(sprintf('REVOKE ALL ON "sessions" FROM "%s"', $role));
            }
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('sessions');
    }
};
