---
name: talred-api
description: Build, refactor, review, or debug Laravel APIs in the Talred Starter architecture, including Zolta HTTP/CQRS, DDD boundaries, request and DTO flow, handlers, repositories, events, and provider wiring. Use for API service work in this stack; use generic Laravel guidance for framework questions outside this architecture.
---

# Talred API

Use the Talred Starter API contract as the architecture source of truth. The skill provides the decision path; the Starter docs explain the supported patterns in detail.

## Start from the repository and package source

1. Resolve the target using the workspace instructions. The docs live at `talred-starter/docs/api/` in the workspace or `docs/api/` when working inside the Starter clone.
2. Read `docs/api/index.md`, then follow the topic's related links and the concept catalogue. For full-stack work, also read `docs/full-stack-contracts.md` and the relevant client guide.
3. Inspect the nearest service slice and the installed Zolta package source. Verify version-specific attributes, signatures, discovery behavior, and runtime semantics; do not infer support from a name or an old example.
4. Keep examples product-neutral. Record the selected pattern and any justified deviation in the change summary or architecture docs.

## Preserve the layer direction

`API → Application → Domain`; Infrastructure implements contracts owned inward.

- **API** owns routes, transport validation, authorization, resources, and HTTP response behavior.
- **Application** owns typed input/output DTOs, use cases, commands, queries, handlers, ports, and deliberate workflow transactions.
- **Domain** owns aggregates, entities, value objects, policies, rules, repository contracts, and domain events.
- **Infrastructure** owns Eloquent, persistence models and mappers, concrete repositories, providers, queues, integrations, and framework adapters.

Keep Domain and Application free of Laravel, Eloquent, HTTP, queue, SDK, and Infrastructure dependencies. Pass validated typed data inward; derive actor, tenant, and ownership values on the server.

## Build one vertical slice at a time

For an endpoint, declare the route with the installed Zolta HTTP attributes, validate and normalize transport input in a `BaseRequest`, map to an immutable DTO, and invoke an application service. Keep controllers declarative; use a manual controller only for exceptional transport behavior such as streams, downloads, or custom headers.

For writes, use one invokable application service and dispatch a typed command through the installed Zolta application service/bus. The handler loads the aggregate through an inward contract, applies domain behavior, persists it, and returns an explicit result. Use transactions only for an intentional atomic boundary.

For reads, use typed queries and read contracts or projections. Represent meaningful absence explicitly; an empty collection is a successful result. Return output DTOs/resources rather than ORM models, builders, framework responses, or unvalidated arrays.

Create only directories needed by the slice. Use business language for names and group code by service/bounded context instead of database table or framework package.

## Enforce query and persistence boundaries

Treat the HTTP query contract and repository allow-list as separate checks. Requests define accepted filters, includes, sorts, pagination, and strictness. Query handlers turn validated options into typed query options. Repositories independently restrict allowed fields, relations, operators, and relation sorting; request validation alone is not a persistence safety boundary.

Repository contracts belong inward; concrete Eloquent repositories and mappers belong in Infrastructure. Map persistence records explicitly to domain objects and restore aggregates without replaying creation behavior. Enforce tenant and ownership scope at the adapter even when the request also validates it. Enable read caching only where stale data is acceptable and define invalidation deliberately.

## Handle domain events and provider wiring deliberately

Aggregates record domain events only for real business effects. Release them after successful persistence. When Laravel listener behavior is needed, use one mapped wrapper/listener path, register each listener once, and regenerate the appropriate Zolta event map. Domain events are not framework events and do not imply event sourcing.

Bind each inward contract explicitly from a focused Infrastructure provider. Use `bind` by default; use a singleton only when shared stateless lifetime is intentional. Keep Laravel traits, SDKs, serialization, retries, and provider errors at the Infrastructure edge.

## Verify the complete affected slice

Use only checks present in the target service. For API changes, verify the focused tests and applicable route/CQRS map generation, inspect route output, and check API docs and contracts. For changes that also alter client contracts, follow the Starter full-stack verification guide and review both API and client docs. Report checks that could not run and why.

See [the API documentation map](references/starter-docs.md) for focused Starter topics and [API implementation patterns](references/api-patterns.md) for a concise slice checklist.
