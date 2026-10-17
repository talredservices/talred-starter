---
name: talred-client
description: Build, refactor, review, or debug the Talred Starter Nuxt client and BFF architecture, including feature layers, server routes, upstream clients, identity/session boundaries, resources, caching, localization, and client state. Use for client/BFF work in this stack; use generic Nuxt guidance for framework questions outside this architecture.
---

# Talred Client

Use the Talred Starter client contract as the architecture source of truth. The skill provides the decision path; the Starter docs explain the supported patterns in detail.

## Start from the repository and installed packages

1. Resolve the target using workspace instructions. The docs live at `talred-starter/docs/client/` in the workspace or `docs/client/` when working inside the Starter clone.
2. Read `docs/client/index.md` and the focused topic. For work that changes the API contract, also inspect the API docs and `docs/full-stack-contracts.md`.
3. Inspect the nearest feature layer and actual Nuxt, Nuxt UI, Identity consumer, and API client versions. Follow source and runtime behavior where it differs from an example.

## Preserve feature ownership and the BFF boundary

- Put feature pages, components, composables, local utilities, server routes, DTOs, and resources in that feature's Nuxt layer.
- Put code in the root app or server only when it is a true cross-layer foundation, such as shared shell/session behavior or common transport infrastructure.
- Browser code calls the Nuxt BFF. BFF handlers validate input, enforce authorization where needed, orchestrate upstream calls, normalize responses, and own caching.
- Keep pages thin. Compose state, queries, actions, and presentation through the local layer's established pattern instead of moving orchestration into templates.
- Follow the layer's existing naming and decomposition; don't impose a second pattern when the local feature uses a simpler convention.

## Implement BFF routes as boundary adapters

Validate query, body, and route parameters at the route boundary. Delegate multi-step work to feature services. Use shared root utilities for genuinely shared clients, auth, errors, validation, and cache infrastructure. Map upstream envelopes and third-party payloads to client-facing resources before returning them.

For upstream calls, use typed clients and shared transport/error handling. Keep credentials server-side. Preserve Identity authorization intent, callback/state validation, session exchange, and CSRF protections; do not expose confidential BFF credentials or call protected APIs directly from the browser.

Cache only responses whose freshness and authorization scope permit it. Include relevant user, tenant, or application identity in cache keys and invalidate or bypass cache on write paths that require immediate read-after-write behavior.

## Keep contracts and user-facing states complete

Use the configured locales for new user-visible copy. Keep shared types and resources aligned with upstream contracts while exposing only the data needed by the client. Cover loading, empty, validation, unauthorized, unavailable, recovery, and success states appropriate to the flow. Keep authorization and security semantics in their owning boundaries rather than changing them to simplify presentation.

## Verify the whole route

Use scripts present in the target package. For client changes, run the relevant lint, type/build, and focused tests; verify route behavior and the browser-facing state. For Identity or cross-stack changes, follow the managed runtime profile and full-stack contract; inspect ownership and service health before changing a running stack. Report unavailable checks accurately.

See [the client documentation map](references/starter-docs.md) for focused Starter topics and [client implementation patterns](references/client-patterns.md) for layer and BFF checklists.
