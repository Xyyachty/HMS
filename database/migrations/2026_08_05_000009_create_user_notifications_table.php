<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * In-app notification feed shared by the dean, faculty and student portals.
 *
 * Deliberately NOT Laravel's polymorphic `notifications` table: every row here
 * targets one users.id, is written by App\Support\Notifier and read straight
 * back as JSON by the bell dropdown — no queued channels, no serialized class
 * names to keep in sync when a notification's shape changes.
 *
 * One row per recipient. Fan-out happens at write time so the unread count is a
 * plain indexed COUNT instead of a per-request permission walk.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('user_notifications')) {
            Schema::create('user_notifications', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
                // Who caused it. Nullable: system events (a class opening) have no actor.
                $table->foreignId('actor_id')->nullable()->constrained('users')->nullOnDelete();
                $table->string('type', 50);
                $table->string('title');
                $table->string('body', 500)->nullable();
                // Deep link into the portal. Stored as a path so it survives a domain change.
                $table->string('url')->nullable();
                $table->timestamp('read_at')->nullable();
                $table->timestamps();

                // The dropdown query: newest first for one user.
                $table->index(['user_id', 'created_at']);
                // The badge query: unread count for one user.
                $table->index(['user_id', 'read_at']);
            });
        }

        if (DB::getDriverName() === 'pgsql') {
            // Matches the security posture of every other table: reachable only
            // through the app's own connection, never through PostgREST roles.
            DB::statement('ALTER TABLE "user_notifications" ENABLE ROW LEVEL SECURITY');
            foreach (['anon', 'authenticated'] as $role) {
                DB::statement(sprintf('REVOKE ALL ON "user_notifications" FROM "%s"', $role));
            }
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('user_notifications');
    }
};
