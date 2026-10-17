# Laravel Cloud Setup

This application is ready to run as a Laravel Cloud API with two long-lived environments:

- `development`: internal testing and feature verification
- `production`: public API used by the Nuxt application

## Recommended Environment Strategy

Use one Laravel Cloud application with two permanent environments:

1. `development`
   Branch: `develop`
   Purpose: validate backend changes before release
2. `production`
   Branch: `main`
   Purpose: live API

When a feature is done:

1. Merge into `develop` and verify the `development` environment
2. Merge or promote the same commit to `main`
3. Let Laravel Cloud deploy `production`

If you want more review safety later, enable Laravel Cloud preview environments for pull requests.

## Build and Deploy Commands

Recommended Laravel Cloud build command:

```bash
composer install --no-dev --prefer-dist --optimize-autoloader && composer run cloud:build
```

Recommended Laravel Cloud deploy command:

```bash
composer run cloud:deploy
```

These map to:

- `composer run cloud:build` -> `php artisan optimize`
- `composer run cloud:deploy` -> `php artisan migrate --force`

## Environment Variables

Use the templates in:

- `.env.cloud.development.example`
- `.env.cloud.production.example`

Laravel Cloud will inject database and resource credentials for attached resources. Your custom variables still need to be configured per environment.

Minimum variables to review per environment:

- `APP_URL`
- `APP_ENV`
- `APP_DEBUG`

## Resource Guidance

- Database: attach a managed MySQL or Postgres database
- Cache: current app defaults are database-backed and Cloud-safe to start
- Queue: current app defaults are database-backed and Cloud-safe to start
- Files: do not rely on local persistent filesystem for uploaded assets

If the API begins storing user-uploaded files, switch `FILESYSTEM_DISK` to `s3` and attach Laravel Cloud Object Storage or another S3-compatible bucket.

## Development Environment Recommendations

- Enable push-to-deploy on the `develop` branch
- Use smaller compute and scale-to-zero if acceptable
- Enable HTTP basic authentication in Laravel Cloud to prevent public access
- Keep a separate Cloud domain such as `api-dev.example.com`

## Production Environment Recommendations

- Use a custom domain such as `api.example.com`
- Set `APP_DEBUG=false`
- Keep push-to-deploy on `main`, or switch to deploy hooks if you want CI gates before production releases
- Use an always-on compute profile if you need lower cold-start risk

## Important Laravel Cloud Notes

- Do not use `storage:link` as a deployment step on Laravel Cloud; deployment filesystem changes do not persist
- Treat the filesystem as ephemeral
- Re-deploy after environment variable changes
- Queue workers are restarted automatically by Laravel Cloud after deployments
