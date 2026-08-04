#!/usr/bin/env bash
set -euo pipefail

# Runs on every container start, before Apache.
#
# Config caching happens here rather than in the Dockerfile because it bakes the
# environment variables in, and Render only supplies those at runtime. Caching at
# build time would freeze empty values into the image.

php artisan config:cache
php artisan route:cache
php artisan view:cache

# Migrations run automatically so a deploy cannot leave the schema behind the code.
# Every migration is guarded (hasTable / driver checks), so this is a no-op when the
# database is already current. Set RUN_MIGRATIONS=false to take manual control.
if [ "${RUN_MIGRATIONS:-true}" = "true" ]; then
    php artisan migrate --force
fi

# Only meaningful when MEDIA_DISK=public; harmless otherwise. On Render the media
# disk is Supabase Storage, so nothing is served from the local filesystem.
php artisan storage:link 2>/dev/null || true

exec "$@"
