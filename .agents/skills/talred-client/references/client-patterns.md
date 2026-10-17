# Client and BFF implementation patterns

Use these patterns with the Starter's focused client docs and the installed Nuxt/consumer package versions. They preserve the staged layered-client guidance while keeping the Starter docs authoritative.

## Feature layer boundaries

- Root `app/` owns cross-feature shell, layouts, middleware, session helpers, and shared UI foundations.
- Root `server/` owns cross-layer transport infrastructure such as upstream clients, cache drivers, auth helpers, and shared error handling.
- `layers/<feature>/app` owns feature pages, components, composables, and local utilities.
- `layers/<feature>/server` owns feature BFF routes, services, schemas, mappers, DTOs, and resources.
- `layers/<feature>/shared` owns feature types/utilities used by both client and server sides.

Promote a file to the root only when multiple layers already need it, it defines whole-app shell/session behavior, or it is transport infrastructure used across features.

## Client state and BFF routes

For richer workflows, follow the local `jobs`-style separation when present: state/defaults, queries, actions, BFF client transport, readonly store facade, then model/page composition. For simpler CRUD/resource screens, match the simpler local composable style instead of importing the richer split.

A feature BFF route should validate route/query/body input, enforce auth/permissions, delegate orchestration, use shared upstream clients, normalize upstream responses, and return a client-facing resource. Do not accumulate domain orchestration, caching strategy, or mapping logic in the route handler. Browser clients call the BFF only.

## Identity, errors, and caching

- Keep authorization intent, callback/state validation, CSRF, session exchange, and confidential client credentials in the server-mediated flow.
- Use typed upstream clients and shared transport/error translation. Keep third-party payloads and API envelopes behind resources/mappers.
- Scope cache keys by all authorization-relevant identity (user, tenant, hosted application) and use feature service/util layers for policy and invalidation.
- Avoid caching authentication, token, or request-scoped identity responses. Bypass or invalidate caches for writes requiring read-after-write freshness.
- Preserve error semantics across upstream, BFF, and browser boundaries without exposing internal details or secrets.

## Naming, state, and user-facing behavior

Match the touched layer's file naming, exports, and decomposition. Use feature-local names such as `*.service.ts`, `*.mapper.ts`, `*.schema.ts`, and `*.resource.ts` where they are already established. Keep visible copy localized and cover loading, empty, validation, error, unavailable, and recovery states appropriate to the feature. Use the installed Nuxt UI/icon bundle conventions rather than assuming auto-registration.

## Verification

Verify the route contract, auth/CSRF behavior, upstream error mapping, cache scope, and user-visible state. Use the repository's package scripts for lint, type/build, and focused tests. Follow managed runtime ownership for browser verification and check both API and client docs when a cross-stack contract changes.
