<?php

namespace App\Database;

use Illuminate\Database\PostgresConnection as BasePostgresConnection;

/**
 * Postgres connection that binds PHP booleans as boolean literals.
 *
 * Laravel's Connection::prepareBindings() turns `true`/`false` into the integers
 * 1/0, and bindValues() then binds an integer as PDO::PARAM_INT. That is fine on
 * a real server-side prepare: PDO sends the value as an untyped literal and
 * Postgres reads '1'/'0' in a boolean column's context, both of which are valid
 * boolean input.
 *
 * It is not fine with PDO::ATTR_EMULATE_PREPARES, which config/database.php turns
 * on because Supabase's transaction pooler cannot carry named prepared statements
 * across connections. Emulation substitutes a PARAM_INT binding inline as a bare
 * integer, so the statement reaches Postgres as `insert ... values (..., 0, ...)`
 * and is rejected:
 *
 *   SQLSTATE[42804]: column "is_published" is of type boolean but expression is
 *   of type integer
 *
 * Postgres will not implicitly cast integer to boolean. Every insert or update
 * writing a boolean broke the moment prepares were emulated — assigning tasks
 * died in ensureTemplate() on `is_published => false`.
 *
 * Binding 'true'/'false' as strings works under both modes: quoted, they arrive
 * as unknown-typed literals and resolve to boolean from the column's context.
 */
class PostgresConnection extends BasePostgresConnection
{
    /**
     * @param  array  $bindings
     * @return array
     */
    public function prepareBindings(array $bindings)
    {
        // Booleans are converted here, before the parent turns them into integers.
        foreach ($bindings as $key => $value) {
            if (is_bool($value)) {
                $bindings[$key] = $value ? 'true' : 'false';
            }
        }

        return parent::prepareBindings($bindings);
    }
}
