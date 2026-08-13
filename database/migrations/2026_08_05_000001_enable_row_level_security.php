<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Close the Supabase REST API off from the application tables.
 *
 * Supabase publishes every table in the `public` schema through PostgREST at
 * https://<ref>.supabase.co/rest/v1/, authorised by the project's anon key — a key
 * that is public by design. Out of the box the `anon` and `authenticated` roles are
 * granted full privileges on tables in `public`, and with RLS disabled that means
 * anybody holding the anon key can read and write every row, password hashes included.
 *
 * This app never uses that API. It talks to PostgreSQL directly as `postgres` over the
 * session pooler, so the entire surface can simply be shut.
 *
 * Two independent layers, either of which alone would be sufficient:
 *
 *   1. RLS enabled with no policies — the roles can reach the tables but no row ever
 *      satisfies a policy, so every query returns nothing. This is what Supabase's
 *      Security Advisor asks for.
 *   2. Privileges revoked outright, including for tables added later. Defence in depth:
 *      if a policy is ever added carelessly, the grants are still absent.
 *
 * The application is unaffected: `postgres` owns these tables, and a table owner is not
 * subject to its own RLS unless FORCE ROW LEVEL SECURITY is set, which it is not.
 *
 * To undo — only needed if you later adopt the Supabase client libraries — see down().
 */
return new class extends Migration
{
    /** Roles PostgREST authenticates as. `service_role` intentionally keeps its access. */
    private const API_ROLES = ['anon', 'authenticated'];

    public function up(): void
    {
        if (DB::getDriverName() !== 'pgsql') {
            return;
        }

        foreach ($this->tables() as $table) {
            DB::statement(sprintf('ALTER TABLE %s ENABLE ROW LEVEL SECURITY', $this->quote($table)));
        }

        foreach (self::API_ROLES as $role) {
            DB::statement(sprintf('REVOKE ALL ON ALL TABLES IN SCHEMA public FROM %s', $this->quote($role)));
            DB::statement(sprintf('REVOKE ALL ON ALL SEQUENCES IN SCHEMA public FROM %s', $this->quote($role)));
            // Anything created from now on is closed by default too.
            DB::statement(sprintf(
                'ALTER DEFAULT PRIVILEGES IN SCHEMA public REVOKE ALL ON TABLES FROM %s',
                $this->quote($role)
            ));
            DB::statement(sprintf(
                'ALTER DEFAULT PRIVILEGES IN SCHEMA public REVOKE ALL ON SEQUENCES FROM %s',
                $this->quote($role)
            ));
        }
    }

    public function down(): void
    {
        if (DB::getDriverName() !== 'pgsql') {
            return;
        }

        foreach (self::API_ROLES as $role) {
            DB::statement(sprintf(
                'ALTER DEFAULT PRIVILEGES IN SCHEMA public GRANT ALL ON TABLES TO %s',
                $this->quote($role)
            ));
            DB::statement(sprintf(
                'ALTER DEFAULT PRIVILEGES IN SCHEMA public GRANT ALL ON SEQUENCES TO %s',
                $this->quote($role)
            ));
            DB::statement(sprintf('GRANT ALL ON ALL TABLES IN SCHEMA public TO %s', $this->quote($role)));
            DB::statement(sprintf('GRANT ALL ON ALL SEQUENCES IN SCHEMA public TO %s', $this->quote($role)));
        }

        foreach ($this->tables() as $table) {
            DB::statement(sprintf('ALTER TABLE %s DISABLE ROW LEVEL SECURITY', $this->quote($table)));
        }
    }

    /** Every base table in public, including `migrations`. */
    private function tables(): array
    {
        return array_map(
            fn ($row) => $row->tablename,
            DB::select("select tablename from pg_tables where schemaname = 'public' order by tablename")
        );
    }

    private function quote(string $identifier): string
    {
        return '"' . str_replace('"', '""', $identifier) . '"';
    }
};
