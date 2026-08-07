<?php
/*
 * Checks the Supabase connection before any migration or data import runs.
 *
 *   php database/checks/supabase_connection.php
 *
 * Verifies, in order: the env keys are filled in, the host resolves and accepts a
 * connection, TLS is actually on, the server version, whether citext resolves under
 * the configured search_path, and whether the schema is still empty.
 */

require __DIR__ . '/../../vendor/autoload.php';
$app = require __DIR__ . '/../../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;

$config = config('database.connections.pgsql');
$fail = 0;

echo "Configuration\n";
printf("  host       : %s\n", $config['host']);
printf("  port       : %s\n", $config['port']);
printf("  database   : %s\n", $config['database']);
printf("  username   : %s\n", $config['username']);
printf("  sslmode    : %s\n", $config['sslmode']);
printf("  search_path: %s\n", $config['search_path']);

$placeholders = [];
if (str_contains((string) $config['username'], 'YOUR_PROJECT_REF')) {
    $placeholders[] = 'DB_PG_USERNAME';
}
if (str_contains((string) $config['password'], 'YOUR_DB_PASSWORD') || $config['password'] === '') {
    $placeholders[] = 'DB_PG_PASSWORD';
}
if ($placeholders) {
    echo "\nStill a placeholder: " . implode(', ', $placeholders) . "\n";
    echo "Fill these in .env from the Supabase dashboard (Connect -> Session pooler), then re-run.\n";
    exit(1);
}

echo "\nConnection\n";
try {
    $started = microtime(true);
    $version = DB::connection('pgsql')->select('select version() as v')[0]->v;
    $ms = (microtime(true) - $started) * 1000;
    printf("  connected  : yes (%.0f ms)\n", $ms);
    printf("  server     : %s\n", explode(' on ', $version)[0]);
} catch (\Throwable $e) {
    echo "  connected  : NO\n";
    echo "  error      : " . trim(explode("\n", $e->getMessage())[0]) . "\n\n";
    echo "Common causes:\n";
    echo "  - Using db.<ref>.supabase.co instead of the pooler host (IPv6-only, times out)\n";
    echo "  - Wrong password, or the username is missing the .<project-ref> suffix\n";
    echo "  - Project paused after 7 days idle — open the dashboard and restore it\n";
    exit(1);
}

// sslmode=require makes libpq refuse to connect unless the server negotiates TLS,
// so reaching this line at all is the proof. pg_stat_ssl is NOT usable here: through
// the pooler it describes Supavisor's own backend link, not this client connection,
// and reports false even while the client link is encrypted.
printf(
    "  encrypted  : %s\n",
    $config['sslmode'] === 'require' || str_starts_with((string) $config['sslmode'], 'verify')
        ? 'yes — connection succeeded under sslmode=' . $config['sslmode']
        : 'NOT ENFORCED — sslmode is "' . $config['sslmode'] . '", set DB_SSLMODE=require'
);
if ($config['sslmode'] !== 'require' && !str_starts_with((string) $config['sslmode'], 'verify')) {
    $fail++;
}

// Steady-state round-trip, measured after the connection is warm. This is the number
// the template-save budget depends on.
$samples = [];
for ($i = 0; $i < 5; $i++) {
    $t = microtime(true);
    DB::connection('pgsql')->select('select 1');
    $samples[] = (microtime(true) - $t) * 1000;
}
sort($samples);
printf("  round-trip : %.0f ms median (%.0f-%.0f ms)\n", $samples[2], $samples[0], $samples[4]);

echo "\ncitext\n";
try {
    DB::connection('pgsql')->statement('CREATE EXTENSION IF NOT EXISTS citext');
    $row = DB::connection('pgsql')->select("select 'a'::citext = 'A'::citext as ok")[0];
    $ok = $row->ok === true || $row->ok === 't';
    printf("  resolves   : %s\n", $ok ? 'yes — case-insensitive comparison works' : 'NO');
    if (!$ok) { $fail++; }
} catch (\Throwable $e) {
    echo "  resolves   : NO\n";
    echo "  error      : " . trim(explode("\n", $e->getMessage())[0]) . "\n";
    echo "  fix        : set DB_SEARCH_PATH=public,extensions in .env\n";
    $fail++;
}

echo "\nSchema\n";
try {
    $tables = DB::connection('pgsql')->select(
        "select table_name from information_schema.tables where table_schema = 'public' order by table_name"
    );
    printf("  tables     : %d\n", count($tables));
    if ($tables) {
        echo "  " . implode(', ', array_map(fn ($t) => $t->table_name, array_slice($tables, 0, 8)))
            . (count($tables) > 8 ? ', …' : '') . "\n";
    }
} catch (\Throwable $e) {
    echo "  tables     : could not list (" . trim($e->getMessage()) . ")\n";
    $fail++;
}

echo "\n" . ($fail === 0 ? "READY — safe to run: php artisan migrate --database=pgsql\n" : "$fail problem(s) above\n");
exit($fail === 0 ? 0 : 1);
