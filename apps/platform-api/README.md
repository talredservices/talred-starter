# Talred Services Platform API

`apps/platform-api` owns the Talred Services Platform workspace bounded context.
It stores workspace identity, membership, and lifecycle state in the isolated
`talred_platform` database while using the `zoltasoft-starter` Identity project
for authentication and platform entry permissions.

The existing `apps/api` service remains the Talred Tasks API and keeps its own
Identity boundary. Platform API credentials stay server-side; the Starter Nuxt
BFF is the browser-facing transport.

## Local development

```bash
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate
php artisan serve
```

The `PlatformWorkspace` bounded context lives under
`app/Services/PlatformWorkspace` with `API`, `Application`, `Domain`, and
`Infrastructure` layers. Talred PHP discovers attribute-owned routes and CQRS
messages from the configured roots in `config/zolta.php`.

When using the separately deployed Identity Project, configure the API client credentials in environment variables; keep them server-side. See the [Identity Project integration guide](../../docs/api/07-cross-cutting/identity-project-integration.md).

## Checks

```bash
php artisan zolta:routes:cache
php artisan zolta:maps:cache
php artisan route:list --path=api
php artisan test
```

## Workspace API

The contract exposes authenticated workspace provisioning, list, detail, and
create operations:

    POST /api/platform/v1/workspaces/default
    GET  /api/platform/v1/workspaces
    POST /api/platform/v1/workspaces
    GET  /api/platform/v1/workspaces/{workspace}

The default-workspace command is idempotent per authenticated account. It
promotes an active owned workspace or creates one named `My workspace`. List
and detail responses include `is_default`. The Nuxt client consumes these
routes through `/api/platform/workspaces`; browser code must not call the
Laravel service directly.

The Identity application directory is a separate live, caller-authorized read:

    GET  /api/platform/v1/identity-applications

It returns only application discovery metadata for Identity Projects visible to
the signed-in Identity account; it does not include client credentials or
application authentication configuration.

## Containers

Build the Platform API image from this directory as the Docker build context:

```bash
docker build -f docker/laravel/Dockerfile -t talred-platform-api .
docker build -f docker/laravel/nginx.Dockerfile -t talred-platform-api-nginx .
```

The PHP-FPM image owns Laravel runtime state; the Nginx image serves the public document root and forwards PHP requests to `PHP_FPM_UPSTREAM`. The Nuxt image is independent and is built from `apps/web` with `docker/nuxt/Dockerfile`.

## License

This application is licensed under Apache-2.0 under the repository [LICENSE](../../LICENSE).
