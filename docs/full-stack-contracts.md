# Full-stack contracts and verification

[← API architecture](api/index.md) · [Client architecture](client/index.md) · [Architecture home](api/index.md)

This guide connects the Laravel/Zolta API and Nuxt/BFF documentation. Use it when a feature crosses both applications.

## Contract flow

```text
API request/DTO → Application outcome/resource → Laravel JSON envelope
  → @talred/api-client → BFF service/mapper → client shared type
  → state/store/model composable → page/component
```

The API owns domain meaning and its public response contract. The BFF owns upstream authentication, error translation, normalization, and cache policy. The client owns presentation state and interaction. No layer should make another layer understand its private representation.

## Change-impact review

For every cross-stack change, inspect and update the affected points:

- API route, request validation, DTO, command/query, handler outcome, resource, and status code;
- API permission/authentication requirements and trusted identity context;
- API response envelope and empty/absent/failure shape;
- API client type and error mapping;
- BFF schema, auth/session requirement, upstream client, mapper/resource, cache key, and invalidation;
- client shared type, client transport, state/query/action/store/model composables, page, and component;
- contract, unit, integration, and browser tests;
- [API concepts](api/00-foundations/concepts.md) and the relevant [client topic](client/index.md).

A change is incomplete when one representation changes without tracing its consumers.

## Workspace resource contract

Workspaces own typed references to externally managed resources. The current
resource type is `identity_project`; the Platform API stores only its Identity
Project id and link provenance. `GET /api/platform/v1/workspaces/{workspaceId}/overview`
returns `resources.identity_projects` with live, caller-authorized summaries.
Available projects are listed at
`GET /api/platform/v1/workspaces/{workspaceId}/resources/identity-projects/available`;
link and unlink use `POST` and `DELETE` under
`/workspaces/{workspaceId}/resources/identity-projects`. Workspace owner/admin
roles control only these Platform links and never add Identity permissions.
The Identity Console remains responsible for Identity Project configuration
and lifecycle.

The global Starter Home also reads
`GET /api/platform/v1/identity-projects`, a read-only directory of Identity
Project summaries visible to the current user's Identity bearer. It does not
depend on Workspace links and does not persist a local Identity projection.
The Home renders this directory independently from the existing membership-
scoped Workspace list; Identity failures do not suppress Workspace results.
`/dashboard/workspaces` remains the dedicated Workspace directory and creation
flow.

The Home application directory reads the Identity-hosted application URLs and
resolves the Starter's bundled demo destinations consistently with Identity
Console: Fresh SaaS opens `/saas`, Tasks opens `/projects`, and the Starter app
opens the site root. The Identity `application_url` remains the base origin;
these demo route prefixes are presentation destinations, not callback URLs.

## Contract-testing pattern

Test each seam with fixtures that represent the actual envelope:

```ts
it('maps the API collection into the client shape', async () => {
  const api = {
    success: true,
    data: { items: [{ id: 't-1', title: 'Write docs' }], meta: { total: 1 } }
  }
  expect(mapTaskCollection(api).items[0]).toEqual({ id: 't-1', title: 'Write docs' })
})
```

Add API feature tests for request/response and authorization, BFF tests for upstream/error/mapping behavior, and client tests for composable state transitions. Use a small shared fixture or generated schema when practical; do not import PHP or server-only types into the browser bundle.

## Shared security checklist

The Tasks example returns 404 for absent or other-owner projects/tasks, 409
for reopening a completed task or reusing an owned project key, and 422 for
invalid business input. Its API transport adapter maps the domain exceptions;
the Nuxt BFF preserves these statuses instead of turning them into upstream
gateway errors. Rejected writes must leave persisted state unchanged.

- Credentials and secrets remain server-only; never expose API client secrets or identity credentials in public runtime config.
- Browser mutations use the authenticated fetch helper and CSRF header.
- API trusted identity data overrides client actor/tenant/project fields.
- BFF routes enforce session/permission requirements before upstream calls.
- Cache keys scope user/session-sensitive data and writes invalidate affected reads.
- Tokens, cookies, raw introspection payloads, and secrets are not logged.
- API, BFF, and client handle session expiry and 401/403/503 outcomes explicitly.
- Validate redirect URLs, webhook destinations, uploaded files, and external input at the owning boundary.

## Reference full-stack slice

Use this navigation when implementing a feature:

1. Define the API contract in [request validation](api/02-boundaries/request-validation.md), [DTO flow](api/02-boundaries/dto-flow.md), and [responses](api/05-presentation/responses.md).
2. Implement Application/CQRS and Domain behavior using [application services](api/04-application/application-services.md), [CQRS](api/04-application/cqrs.md), and [DDD](api/03-domain/ddd-model.md).
3. Implement persistence and bindings through [repositories](api/06-infrastructure/repositories.md), [mappers](api/06-infrastructure/models-and-mappers.md), and [providers](api/06-infrastructure/providers.md).
4. Add the BFF route and mapper using [client BFF guidance](client/01-bff/bff.md).
5. Add client transport/state/page composition using [client architecture](client/02-client/client.md) and the [jobs reference](client/03-reference/jobs-layer.md).
6. Run the API and client CI commands and verify the public contract end to end.

## CI baseline

```bash
# API
cd apps/api
composer install
php artisan test
composer run lint  # when defined by the service
php artisan route:list --path=api
php artisan zolta:maps:cache
php artisan zolta:routes:cache

# Nuxt client
cd apps/web
pnpm install
pnpm run lint
pnpm run test
pnpm run build
pnpm run generate
```

Use the scripts actually defined by the target application; do not assume a command exists. A deployment build should regenerate/verify generated maps and route artifacts as part of its release checks.
