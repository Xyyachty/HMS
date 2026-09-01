#!/usr/bin/env bash
#
# Runs on every container start, before Apache.
#
# Deliberately NOT `set -e`. An earlier version aborted on the first failing
# command, which meant a slow database or a bad credential turned into "Deploy
# failed" with no app running and nothing to look at. The app should still come
# up so the logs can be read and the platform health check can respond.

echo "[entrypoint] starting"

# ---------------------------------------------------------------------------
# Apache port
#
# Render assigns the port through $PORT and it is not always 80. The port is
# written into the config here rather than templated as ${PORT} at build time:
# Apache's expansion of environment variables inside its own config is not
# reliable across images, and when it silently fails Apache listens on the wrong
# port, the health check never passes, and the deploy fails with a build that
# looked perfectly fine.
# ---------------------------------------------------------------------------
: "${PORT:=80}"
echo "[entrypoint] binding Apache to port ${PORT}"
sed -ri "s/^Listen [0-9]+\$/Listen ${PORT}/" /etc/apache2/ports.conf
sed -ri "s/<VirtualHost \*:[0-9]+>/<VirtualHost *:${PORT}>/" /etc/apache2/sites-available/*.conf

# ---------------------------------------------------------------------------
# Framework caches
#
# Built here, not in the Dockerfile: config caching bakes in environment
# variables, and Render only supplies those at runtime. Caching at build time
# would freeze empty values into the image.
# ---------------------------------------------------------------------------
for cmd in "config:cache" "route:cache" "view:cache"; do
    if php artisan "$cmd" >/dev/null 2>&1; then
        echo "[entrypoint] $cmd ok"
    else
        # Non-fatal: the app runs fine uncached, just slower. Better a working
        # site with a warning than a dead container.
        echo "[entrypoint] WARNING: $cmd failed, continuing uncached"
        php artisan "$cmd" 2>&1 | tail -5
    fi
done

# ---------------------------------------------------------------------------
# Migrations
#
# Keeps the schema in step with the code on deploy. Every migration is guarded,
# so this is a no-op when the database is already current. Failure is reported
# loudly but does not stop the container — a transient network problem reaching
# Supabase should not take the whole site down.
#
# Set RUN_MIGRATIONS=false to skip entirely.
# ---------------------------------------------------------------------------
if [ "${RUN_MIGRATIONS:-true}" = "true" ]; then
    echo "[entrypoint] running migrations"
    if php artisan migrate --force --no-interaction 2>&1 | tail -20; then
        echo "[entrypoint] migrations ok"
    else
        echo "[entrypoint] WARNING: migrations failed - check DB_PG_* variables"
    fi
else
    echo "[entrypoint] migrations skipped (RUN_MIGRATIONS=false)"
fi

# ---------------------------------------------------------------------------
# Seeders
#
# Creates the dean and faculty accounts the app cannot be administered without.
# Every seeder is updateOrCreate, so this is a no-op on every start after the
# first — but it does rewrite the seeded passwords back to their defaults, so a
# password changed by hand will not survive the next deploy.
#
# Non-fatal for the same reason as the migrations above: a database that is
# briefly unreachable should leave the site up, not dead.
#
# Set RUN_SEEDERS=false to skip entirely.
# ---------------------------------------------------------------------------
if [ "${RUN_SEEDERS:-true}" = "true" ]; then
    echo "[entrypoint] running seeders"
    if php artisan db:seed --force --no-interaction 2>&1 | tail -20; then
        echo "[entrypoint] seeders ok"
    else
        echo "[entrypoint] WARNING: seeders failed - check DB_PG_* variables"
    fi
else
    echo "[entrypoint] seeders skipped (RUN_SEEDERS=false)"
fi

# Only meaningful when MEDIA_DISK=public. On Render the media disk is Supabase
# Storage, so nothing is served from the local filesystem.
php artisan storage:link >/dev/null 2>&1 || true

# ---------------------------------------------------------------------------
# Diagnostics
#
# Printed because the platform only reports "health check timed out", which says
# nothing about why. These three answer the three things that actually go wrong:
# is Apache's config valid, can PHP reach the database, and does the app render.
# ---------------------------------------------------------------------------
echo "[entrypoint] apache configtest:"
apache2ctl configtest 2>&1 | sed 's/^/[entrypoint]   /'

echo "[entrypoint] database check:"
php -r '
require "/var/www/html/vendor/autoload.php";
$app = require "/var/www/html/bootstrap/app.php";
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
try {
    $n = Illuminate\Support\Facades\DB::connection()->table("users")->count();
    echo "  connected, users=$n\n";
} catch (\Throwable $e) {
    echo "  FAILED: " . trim(explode("\n", $e->getMessage())[0]) . "\n";
}' 2>&1 | sed 's/^/[entrypoint] /'

# Render the landing page in-process and report the exception behind any 500.
# Laravel's handler swallows exceptions and renders a generic "Server Error" page
# when APP_DEBUG is off, so the cause only reaches the log as one line buried in a
# 40-frame stack trace. Substituting a handler that prints the class and message
# puts the actual fault on its own line, and does it without exposing anything
# publicly the way APP_DEBUG=true would.
echo "[entrypoint] rendering / in-process:"
php -r '
require "/var/www/html/vendor/autoload.php";
$app = require "/var/www/html/bootstrap/app.php";

$app->singleton(Illuminate\Contracts\Debug\ExceptionHandler::class, function () {
    return new class implements Illuminate\Contracts\Debug\ExceptionHandler {
        public function report(Throwable $e): void {
            echo "  " . get_class($e) . ": " . $e->getMessage() . "\n";
            echo "  thrown at " . $e->getFile() . ":" . $e->getLine() . "\n";
            if ($p = $e->getPrevious()) {
                echo "  caused by " . get_class($p) . ": " . $p->getMessage() . "\n";
            }
        }
        public function shouldReport(Throwable $e): bool { return true; }
        public function render($request, Throwable $e) {
            return new Illuminate\Http\Response("", 500);
        }
        public function renderForConsole($output, Throwable $e): void {}
    };
});

try {
    $kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
    $response = $kernel->handle(Illuminate\Http\Request::create("/", "GET"));
    echo "  status " . $response->getStatusCode() . "\n";
} catch (\Throwable $e) {
    echo "  FATAL " . get_class($e) . ": " . $e->getMessage() . "\n";
}' 2>&1 | sed 's/^/[entrypoint] /'

echo "[entrypoint] handing over to: $*"

# Once Apache is up, fetch the landing page from inside the container. A non-200
# here is the app failing, not the platform, and the body carries the reason.
(
    sleep 8
    code=$(curl -s -o /tmp/probe.html -w '%{http_code}' "http://127.0.0.1:${PORT}/" || echo "000")
    echo "[entrypoint] self-probe GET / -> HTTP ${code}"
    if [ "$code" != "200" ]; then
        echo "[entrypoint] response body (first 40 lines):"
        head -40 /tmp/probe.html 2>/dev/null | sed 's/^/[entrypoint]   /'
    fi
) &

exec "$@"
