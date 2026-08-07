<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Move the citext extension out of `public`.
 *
 * The baseline migration created it with a bare CREATE EXTENSION, and PostgreSQL puts
 * an extension in the first schema on the search path — which here is `public`. Supabase
 * flags that ("Extension in Public") because `public` holds the application's own tables:
 * extension objects there can collide with them, extension functions end up executable by
 * PUBLIC, and dumps and version upgrades get messier. Supabase keeps its own extensions
 * in the `extensions` schema; this puts citext alongside them.
 *
 * Purely organisational — no data changes. The five citext columns keep their type,
 * because a column stores the type's identity rather than its name, and unqualified
 * `citext` still resolves as long as DB_SEARCH_PATH includes `extensions`.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() !== 'pgsql' || !$this->extensionInstalled()) {
            return;
        }

        // Present on Supabase already; created here so a plain PostgreSQL install works too.
        DB::statement('CREATE SCHEMA IF NOT EXISTS extensions');

        if ($this->extensionSchema() !== 'extensions') {
            DB::statement('ALTER EXTENSION citext SET SCHEMA extensions');
        }
    }

    public function down(): void
    {
        if (DB::getDriverName() !== 'pgsql' || !$this->extensionInstalled()) {
            return;
        }

        if ($this->extensionSchema() !== 'public') {
            DB::statement('ALTER EXTENSION citext SET SCHEMA public');
        }
    }

    private function extensionInstalled(): bool
    {
        return DB::select("select 1 from pg_extension where extname = 'citext'") !== [];
    }

    private function extensionSchema(): ?string
    {
        $row = DB::select(
            "select n.nspname from pg_extension e
             join pg_namespace n on n.oid = e.extnamespace
             where e.extname = 'citext'"
        );

        return $row ? $row[0]->nspname : null;
    }
};
