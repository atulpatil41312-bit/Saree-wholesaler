# WholeMarket on DigitalOcean

This directory contains the repository-side pieces for a secure GitHub Actions deployment to a DigitalOcean Linux server. It does not configure a live server, DNS, firewall, SSH, TLS, or database. Those changes require an operator with access to the DigitalOcean account and an approved maintenance window.

## Deployment model

1. GitHub Actions checks out the exact commit pushed to `main`.
2. PHP dependencies, Vite assets, and the existing PHPUnit suite are verified.
3. The workflow packages an immutable archive containing tracked application code, `vendor`, and `public/build`. It never packages `.env`, uploads, logs, or runtime cache.
4. The archive is copied over SSH using a dedicated deployment account, a pinned `known_hosts` entry, and a GitHub `production` environment.
5. `deploy-release.sh` extracts the archive into a new release directory, links the server-owned `.env` and persistent storage, warms Laravel caches, switches the `current` symlink atomically, and verifies the HTTPS `/up` endpoint.
6. A failed health check restores the previous `current` symlink. The previous releases remain available for manual rollback.

The workflow serializes production deployments with GitHub Actions concurrency and the server script also uses `flock`. The deployment account must not be root. Do not give it a shell command that can be used to modify unrelated servers or read unrelated secrets.

## GitHub configuration

Create a GitHub environment named `production` and, if desired, require a reviewer before the deploy job can run. Add these environment variables:

| Variable | Example shape | Purpose |
| --- | --- | --- |
| `DEPLOY_HOST` | `deploy.example.com` | DigitalOcean server hostname or fixed IP |
| `DEPLOY_PORT` | `22` | SSH port |
| `DEPLOY_USER` | `wholemarket-deploy` | Dedicated non-root account |
| `DEPLOY_APP_ROOT` | `/srv/wholemarket` | App root on the server |
| `DEPLOY_INBOX` | `/srv/wholemarket/inbox` | Directory writable by the deployment account |
| `DEPLOY_HEALTH_URL` | `https://example.com/up` | HTTPS health URL |
| `DEPLOY_SCRIPT` | `/opt/wholemarket/bin/deploy-release` | Installed path for this script |

Add these environment secrets. Their values must be entered only in GitHub Settings; never paste them into chat or commit them:

- `DEPLOY_SSH_PRIVATE_KEY`: a dedicated key whose public key is restricted to the deployment account.
- `DEPLOY_KNOWN_HOSTS`: the server's verified SSH host-key line(s), collected out-of-band and reviewed before saving.

The workflow does not accept a password, disables no host-key checks, and does not print these values. Keep the `production` environment protected and restrict who can push to `main`.

Run-on-push deployments do not run migrations. To apply a migration, start **Actions → Deploy WholeMarket to DigitalOcean → Run workflow**, choose `main`, and set `run_migrations` to `true`. The server refuses that operation unless a backup command is configured.

## One-time server setup (operator action)

The following is a checklist, not an instruction for this agent to run against production:

1. Create a dedicated non-root `wholemarket-deploy` user with an SSH public key. Keep a separate admin recovery path. Disable password SSH only after key login is verified in a second session.
2. Install PHP 8.3 with the extensions used by the Docker image (`bcmath`, `gd`, `mbstring`, `pdo_pgsql`, `zip`), Composer 2, `curl`, `tar`, and `flock`.
3. Create `/srv/wholemarket/{inbox,releases,shared/storage/app/public,shared/storage/framework/cache,shared/storage/framework/sessions,shared/storage/framework/views,shared/storage/logs}`. Put the production `.env` at `/srv/wholemarket/shared/.env` with mode `0640`; it must never be in the repository.
4. Copy this repository's `deploy-release.sh` to `/opt/wholemarket/bin/deploy-release`, review it, and make it executable. Set ownership so only the deployment account and the web-process group can read the release tree. Do not run the script as root.
5. Configure Apache or Nginx to serve `/srv/wholemarket/current/public`, allow only the required public ports, and keep PostgreSQL/internal services bound to private interfaces. The web process needs read access to releases and write access only to the shared storage directories.
6. Create `/srv/wholemarket/shared/deploy.env` with server-owned operational settings. Do not put secrets in GitHub workflow files:

   ```sh
   WHOLEMARKET_ARCHIVE_ROOT=/srv/wholemarket/inbox
   WHOLEMARKET_HEALTH_URL=https://example.com/up
   WHOLEMARKET_KEEP_RELEASES=3
   # Required only for a migration-enabled run. The command must create a dated,
   # restorable backup outside the release tree before it returns successfully.
   WHOLEMARKET_DB_BACKUP_COMMAND='your-reviewed-backup-command'
   ```

   Replace the placeholder backup command with a reviewed command suitable for the actual database provider. It must not echo credentials. Test a restore in an isolated environment before relying on it.
7. Configure TLS with a trusted certificate authority, redirect HTTP to HTTPS, enable secure cookies, and verify renewal before calling the deployment URL production-ready.
8. Configure backups for the database, `/srv/wholemarket/shared/storage`, and the server-owned `.env` (the last one encrypted and access-controlled). Test restoration and monitor disk space, certificate renewal, failed deployments, and backup freshness.

## Rollback

If the health check fails, the script automatically restores the previous `current` symlink. For a later manual rollback, run the same script with an existing release archive only after reviewing the release and database state, or atomically point `current` at a retained release as the server operator. Do not run destructive reverse migrations against a live database; restore a tested database backup only when the migration plan requires it.

## Domain and HTTPS verification

Before changing DNS, document the proposed `A`/`AAAA` record(s) pointing the domain to the DigitalOcean server and verify there is no conflicting record. After TLS setup, verify:

```sh
curl --fail --silent --show-error --location --max-time 20 http://example.com/up -o /dev/null
curl --fail --silent --show-error --max-time 20 https://example.com/up -o /dev/null
```

The HTTP request must redirect to HTTPS and the HTTPS request must return success. Also verify a browser receives secure cookies and that `/dashboard`, authentication, uploads, and shop storefronts are not cached publicly.
