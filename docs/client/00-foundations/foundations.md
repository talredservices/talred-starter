# Foundations and starter tooling

[← Foundations and starter tooling index](index.md) · [↑ Nuxt architecture index](../index.md) · [Architecture home](../../api/index.md)

## Purpose and boundary

The Nuxt application is a layered composition of independent feature layers. The browser talks to Nuxt `/api/...` BFF routes. BFF routes talk to Laravel or external services. Vue pages never call Laravel or third-party APIs directly.

```text
Vue page
  → page/model/store composables
  → query/action/client composables
  → Nuxt BFF route
  → BFF validation/auth/service/cache/mapper
  → Laravel API or external provider
  → client-facing resource shape
```

The BFF is an application boundary, not a transparent proxy: it validates input, applies session/auth context, orchestrates upstream calls, normalizes envelopes, owns caching, and returns a stable client contract.

## Repository and layer tree

```text
app/                              shared client foundations
  layouts/                        app-wide shells
  components/                     shared UI shell
  composables/                    session/authenticated transport
  middleware/                     cross-layer navigation guards
  plugins/                        global client lifecycle
server/                           shared BFF infrastructure
  api/                            only truly cross-feature endpoints
  http/                           Laravel/external clients and errors
  cache/                          reusable cache drivers
  authorization/                  shared session/permission helpers
  validation/                     shared schema helpers
layers/<feature>/
  app/
    pages/                        thin route views
    components/                   feature presentation
    composables/                  state, query, action, client, store, model/page
  server/
    api/<feature>/                BFF entry points
    utils/                        services, clients, mappers, schemas, resources
  shared/                         types used by both app and server
```

Keep feature code in its layer. Put code in root `app/` or `server/` only when multiple layers actually need it or it is a global shell/session/transport foundation. A layer may use a simpler tree when the feature is small; do not create empty architectural ceremony.

## Feature layer configuration

Add the layer to the root `extends` list in `nuxt.config.ts`. Use a layer-local `nuxt.config.ts` for feature aliases and feature-local runtime configuration. Keep cross-layer runtime configuration in the root config and never expose server secrets through `runtimeConfig.public`.

## Starter shared foundations and tools

A Nuxt starter should provide reusable foundations even when it contains no product feature layers:

- `app/composables/useAuthenticatedFetch.ts` centralizes authenticated BFF transport, CSRF/logout headers, locale-aware redirects, and session-aware navigation.
- `app/composables/useAuthenticationState.ts` exposes session initialization, login redirect, and logout behavior without coupling pages to the identity provider.
- `app/composables/useApiFormValidation.ts` extracts stable API validation errors and converts field messages to Nuxt UI form errors.
- `app/middleware/` contains only cross-layer navigation/session guards; feature guards remain in their layer.
- `app/components/` contains shell and reusable primitives such as panels, action dialogs, resource lists, empty states, headers, menus, and settings rows.
- `server/http/` owns upstream API clients and error translation (`TalredApiError` to safe H3 errors).
- `server/validation/` owns Zod request validation and field-error conversion.
- `server/authorization/` owns reusable BFF authorization helpers with explicit `all`/`any` permission matching.
- `server/cache/` exposes a driver boundary (memory for local use, Redis when configured); feature services own keys and invalidation.
- `server/utils/` contains shared rate limiting, stable serialization, auth-session requirements, and client factories.
- `shared/types/` and `shared/utils/` contain browser/server-safe contracts only; no secrets, ORM types, or server-only imports.

These are starter capabilities, not product features. A new feature should consume them instead of duplicating authentication, validation, error, cache, or transport code.

## Nuxt configuration and package baseline

The root `nuxt.config.ts` is the composition point. It extends feature layers and package layers, registers modules, defines public versus private runtime configuration, configures i18n/route rules, and sets compatibility/devtools options. A layer’s `nuxt.config.ts` owns only its aliases and feature-local runtime needs.

A typical baseline includes Nuxt, Vue, TypeScript, `@nuxt/eslint`, Nuxt UI/icon support, VueUse, Zod, Vitest, `@talred/api-client`, and (when selected) `@talred/identity-consumer-nuxt`, CSRF/session modules, i18n, image/content, and cache/client utilities. Pin compatible versions and inspect package peer requirements before adding modules.

Keep secrets in private `runtimeConfig` or environment variables. Only browser-safe values belong under `runtimeConfig.public`. Avoid putting feature-specific modules into the root config until more than one layer needs them.

## Quality tooling

Use the starter’s scripts as the baseline contract:

```bash
pnpm run dev
pnpm run build
pnpm run generate
pnpm run preview
pnpm run lint
pnpm run lint:js
pnpm run lint:types
pnpm run format:check
pnpm run test
```

`lint:js` runs ESLint with zero warnings over app/layers/packages/server/shared code; `lint:types` runs strict TypeScript project references through Nuxt-generated configs; `test` runs Vitest tests under `tests/**/*.test.ts` and excludes end-to-end tests. Keep generated `.nuxt` output out of source edits, run `nuxt prepare` via `postinstall`, and add e2e coverage separately when browser behavior matters.
