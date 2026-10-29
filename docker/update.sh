#!/usr/bin/env bash
#
# Updates HMS on the school server (hms.spccdo.edu.ph).
#
#   sudo bash /opt/hms/docker/update.sh          pull + rebuild only when there is something new
#   sudo bash /opt/hms/docker/update.sh --force  rebuild even when there is nothing new
#
# The running site is left alone until the new image has built, so a failed
# build never takes it down. If the new container does not answer within the
# wait below, the previous image is started again.

set -euo pipefail

# Everything lives in main(), called on the last line, so bash has read the
# whole script before `git pull` can replace this file underneath it.
main() {
    local dir=/opt/hms
    local env_file="$dir/.env.production"
    local branch=feat/supabase-migration
    local run_args=(-d --name hms --restart unless-stopped --network host
        --env-file "$env_file" -v hms-storage:/var/www/html/storage/app)

    if [ "$EUID" -ne 0 ]; then
        echo "Run with sudo: sudo bash $dir/docker/update.sh"
        exit 1
    fi
    cd "$dir"

    local port
    port=$(grep -E '^PORT=' "$env_file" | cut -d= -f2 || true)
    port=${port:-8083}

    echo "== Checking for new commits"
    git fetch origin "$branch"
    local new
    new=$(git log --oneline "HEAD..origin/$branch")
    if [ -z "$new" ] && [ "${1:-}" != "--force" ]; then
        echo "Nothing new. Already on $(git log -1 --oneline)."
        exit 0
    fi
    echo "${new:-(no new commits, --force rebuild)}"

    echo "== Pulling"
    if ! git pull --ff-only origin "$branch"; then
        echo
        echo "git pull failed. Someone probably edited files on this server."
        echo "See them with: git -C $dir status"
        echo "Throw them away with: git -C $dir reset --hard origin/$branch"
        exit 1
    fi

    echo "== Building image (site stays up meanwhile)"
    if docker image inspect hms >/dev/null 2>&1; then
        docker tag hms hms:prev
    fi
    if ! docker build -t hms "$dir"; then
        echo
        echo "Build failed. The old version is still running, nothing was changed."
        echo "If the error says 'not valid yet' or mentions signatures, check the clock: date"
        exit 1
    fi

    echo "== Restarting container"
    docker rm -f hms >/dev/null 2>&1 || true
    docker run "${run_args[@]}" hms >/dev/null

    # The entrypoint runs migrations and seeders before Apache starts, so the
    # first answer can take a while.
    echo "== Waiting for http://127.0.0.1:$port"
    local code=000
    for _ in $(seq 1 45); do
        code=$(curl -s -o /dev/null -w '%{http_code}' "http://127.0.0.1:$port/" || true)
        case "$code" in
            2??|3??)
                echo "Up (HTTP $code). Now on $(git log -1 --oneline)."
                exit 0
                ;;
        esac
        sleep 2
    done

    echo
    echo "New version did not come up (last HTTP $code). Last log lines:"
    docker logs --tail 40 hms 2>&1 || true

    if docker image inspect hms:prev >/dev/null 2>&1; then
        echo
        echo "== Rolling back to the previous image"
        docker rm -f hms >/dev/null 2>&1 || true
        docker run "${run_args[@]}" hms:prev >/dev/null
        echo "Previous version started again. Fix the problem, then rerun with --force."
    fi
    exit 1
}

main "$@"
