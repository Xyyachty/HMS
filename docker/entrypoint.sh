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

# Only meaningful when MEDIA_DISK=public. On Render the media disk is Supabase
# Storage, so nothing is served from the local filesystem.
php artisan storage:link >/dev/null 2>&1 || true

echo "[entrypoint] handing over to: $*"
exec "$@"
