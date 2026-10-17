# Talred Starter API

`apps/api` is the Laravel API for the Talred Tasks example. It owns the authenticated Projects and Tasks bounded context and demonstrates independently deployable services built with Talred HTTP, CQRS, Forge, and Clean Architecture.

## Local development

```bash
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate
php artisan serve
```

Add each bounded context under `app/Services/<BoundedContext>` with `API`, `Application`, `Domain`, and `Infrastructure` layers. Talred PHP discovers attribute-owned routes and CQRS messages from the configured roots in `config/zolta.php`.

When using the separately deployed Identity Project, configure the API client credentials in environment variables; keep them server-side. See the [Identity Project integration guide](../../docs/api/07-cross-cutting/identity-project-integration.md).

## Checks

```bash
php artisan zolta:routes:cache
php artisan zolta:maps:cache
php artisan route:list --path=api
php artisan test
```

## Containers

Build API images from `apps/api` as the Docker build context:

```bash
docker build -f docker/laravel/Dockerfile -t talred-starter-api .
docker build -f docker/laravel/nginx.Dockerfile -t talred-starter-api-nginx .
```

The PHP-FPM image owns Laravel runtime state; the Nginx image serves the public document root and forwards PHP requests to `PHP_FPM_UPSTREAM`. The Nuxt image is independent and is built from `apps/web` with `docker/nuxt/Dockerfile`.

## License

This application is licensed under Apache-2.0 under the repository [LICENSE](../../LICENSE).


## Projects API example

The Starter API includes a small authenticated Projects/Tasks bounded context. It demonstrates owner-scoped Laravel persistence and the client-facing routes consumed by `apps/web/layers/projects`. Apply it locally with:

    php artisan migrate --force
    php artisan route:list --path=api

The Nuxt client must consume this API through its BFF; browser code must not call the Laravel service directly.

Projects can be permanently deleted with `DELETE /api/projects/{projectId}` by their authenticated owner. The existing foreign-key cascade removes the project's task records with it.
