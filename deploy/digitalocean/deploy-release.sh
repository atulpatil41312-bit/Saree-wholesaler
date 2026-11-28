#!/usr/bin/env bash

set -Eeuo pipefail
umask 027

usage() {
    cat >&2 <<'USAGE'
Usage: deploy-release.sh --archive PATH --release SHA --app-root PATH --health-url URL --run-migrations true|false
USAGE
}

die() {
    printf 'Deployment failed: %s\n' "$1" >&2
    exit 1
}

archive=''
release_id=''
app_root=''
health_url=''
run_migrations='false'

while (($# > 0)); do
    case "$1" in
        --archive)
            [[ $# -ge 2 ]] || { usage; exit 2; }
            archive="$2"
            shift 2
            ;;
        --release)
            [[ $# -ge 2 ]] || { usage; exit 2; }
            release_id="$2"
            shift 2
            ;;
        --app-root)
            [[ $# -ge 2 ]] || { usage; exit 2; }
            app_root="$2"
            shift 2
            ;;
        --health-url)
            [[ $# -ge 2 ]] || { usage; exit 2; }
            health_url="$2"
            shift 2
            ;;
        --run-migrations)
            [[ $# -ge 2 ]] || { usage; exit 2; }
            run_migrations="$2"
            shift 2
            ;;
        --help)
            usage
            exit 0
            ;;
        *)
            usage
            exit 2
            ;;
    esac
done

[[ "$release_id" =~ ^[0-9a-f]{40}$ ]] || die 'release must be a full Git SHA.'
[[ "$run_migrations" == 'true' || "$run_migrations" == 'false' ]] || die 'run-migrations must be true or false.'
[[ "$app_root" = /* && "$app_root" != */ ]] || die 'app-root must be an absolute path.'
[[ "$health_url" =~ ^https:// ]] || die 'health-url must use HTTPS.'
[[ -f "$archive" ]] || die 'release archive was not found on the server.'

releases_dir="$app_root/releases"
shared_dir="$app_root/shared"
current_link="$app_root/current"
deploy_config="$shared_dir/deploy.env"
release_dir="$releases_dir/$release_id"
temporary_dir="$releases_dir/.${release_id}.tmp.$$"

[[ ! -L "$releases_dir" && ! -L "$shared_dir" ]] || die 'releases and shared must be real directories, not symlinks.'
if [[ -e "$current_link" && ! -L "$current_link" ]]; then
    die 'current must be a symlink or absent.'
fi

mkdir -p "$app_root" "$releases_dir" "$shared_dir" "$shared_dir/storage/app/public" \
    "$shared_dir/storage/framework/cache" "$shared_dir/storage/framework/sessions" \
    "$shared_dir/storage/framework/views" "$shared_dir/storage/logs"

exec 9>"$app_root/.deploy.lock"
flock -n 9 || die 'another deployment is already running.'

[[ -f "$shared_dir/.env" ]] || die "server environment file is missing: $shared_dir/.env"
[[ ! -e "$release_dir" ]] || die "release already exists: $release_id"

if [[ -f "$deploy_config" ]]; then
    # This file is owned and maintained on the server. It is never copied from GitHub.
    set -a
    # shellcheck disable=SC1090
    . "$deploy_config"
    set +a
fi

if [[ -n "${WHOLEMARKET_HEALTH_URL:-}" && "$health_url" != "$WHOLEMARKET_HEALTH_URL" ]]; then
    die 'health URL does not match the server deployment configuration.'
fi

if [[ -n "${WHOLEMARKET_ARCHIVE_ROOT:-}" ]]; then
    archive_root="$(realpath -m -- "$WHOLEMARKET_ARCHIVE_ROOT")"
    archive_realpath="$(realpath -- "$archive")"
    [[ "$archive_realpath" == "$archive_root"/* ]] || die 'archive is outside the configured inbox.'
fi

while IFS= read -r path; do
    case "$path" in
        /*|../*|*/../*|..)
            die 'release archive contains an unsafe path.'
            ;;
    esac
done < <(tar -tzf "$archive")

mkdir "$temporary_dir"
tar -xzf "$archive" -C "$temporary_dir"
[[ ! -e "$temporary_dir/.env" ]] || die 'release archive must not contain .env.'

# The web process must be able to read the immutable release tree. The parent
# releases directory is expected to use the web-process group as its setgid
# group so these permissions survive extraction.
find "$temporary_dir" -type d -exec chmod g+rx {} +
find "$temporary_dir" -type f -exec chmod g+r {} +

mkdir -p "$temporary_dir/bootstrap/cache"
ln -s "$shared_dir/.env" "$temporary_dir/.env"
ln -s "$shared_dir/storage" "$temporary_dir/storage"

if [[ -e "$temporary_dir/public/storage" || -L "$temporary_dir/public/storage" ]]; then
    rm -rf -- "$temporary_dir/public/storage"
fi
ln -s "$shared_dir/storage/app/public" "$temporary_dir/public/storage"

if [[ "$run_migrations" == 'true' ]]; then
    [[ -n "${WHOLEMARKET_DB_BACKUP_COMMAND:-}" ]] || die 'migrations require WHOLEMARKET_DB_BACKUP_COMMAND in shared/deploy.env.'
    bash -lc "$WHOLEMARKET_DB_BACKUP_COMMAND"
    php "$temporary_dir/artisan" migrate --force --no-interaction
fi

mv -- "$temporary_dir" "$release_dir"

# Cache after the temporary directory has been renamed. Laravel stores
# absolute paths (for example compiled views and log files) in its cached
# configuration; caching before the rename leaves those paths pointing at the
# removed .tmp directory.
if ! (
    cd "$release_dir"
    php artisan optimize:clear --no-interaction
    php artisan config:cache --no-interaction
    php artisan route:cache --no-interaction
    php artisan view:cache --no-interaction
); then
    rm -rf -- "$release_dir"
    die 'Laravel cache warm-up failed.'
fi

find "$release_dir" -type d -exec chmod g+rx {} +
find "$release_dir" -type f -exec chmod g+r {} +

previous_target=''
if [[ -L "$current_link" ]]; then
    previous_target="$(readlink -f -- "$current_link")"
fi

next_link="$app_root/.current-$release_id"
ln -s "$release_dir" "$next_link"
mv -Tf -- "$next_link" "$current_link"

refresh_token="$(openssl rand -hex 32)"
refresh_capability="$release_dir/.opcache-refresh"
printf '%s' "$refresh_token" > "$refresh_capability"
chmod 0644 "$refresh_capability"
trap 'rm -f -- "$refresh_capability"' EXIT
health_host="${health_url#https://}"
health_host="${health_host%%/*}"

rollback() {
    printf 'Health check failed; restoring the previous release.\n' >&2
    rollback_link="$app_root/.rollback-$release_id"
    if [[ -n "$previous_target" ]]; then
        ln -s "$previous_target" "$rollback_link"
        mv -Tf -- "$rollback_link" "$current_link"
    else
        rm -f -- "$current_link"
    fi
    rm -rf -- "$release_dir"
}

if ! curl --fail --silent --show-error --max-time 20 \
    --resolve "$health_host:443:127.0.0.1" \
    -H "X-WholeMarket-Refresh: $refresh_token" \
    "https://$health_host/opcache-refresh.php" >/dev/null; then
    rollback
    exit 1
fi

if ! curl --fail --silent --show-error --max-time 20 \
    --resolve "$health_host:443:127.0.0.1" \
    "$health_url" >/dev/null; then
    rollback
    exit 1
fi

keep_releases="${WHOLEMARKET_KEEP_RELEASES:-3}"
[[ "$keep_releases" =~ ^[0-9]+$ ]] && ((keep_releases >= 2)) || die 'WHOLEMARKET_KEEP_RELEASES must be at least 2.'

mapfile -t old_releases < <(
    find "$releases_dir" -mindepth 1 -maxdepth 1 -type d -printf '%f\n' \
        | grep -E '^[0-9a-f]{40}$' \
        | sort -r
)

if ((${#old_releases[@]} > keep_releases)); then
    for old_release in "${old_releases[@]:keep_releases}"; do
        old_path="$releases_dir/$old_release"
        [[ "$old_path" != "$release_dir" ]] || continue
        [[ "$old_path" != "$previous_target" ]] || continue
        rm -rf -- "$old_path"
    done
fi

printf 'Activated release %s.\n' "$release_id"
