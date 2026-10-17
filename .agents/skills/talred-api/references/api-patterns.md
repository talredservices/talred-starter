# API implementation patterns

Use these patterns with the Starter's focused API documentation and the installed Zolta package source. They summarize the staged Laravel/Zolta guidance; the docs remain the authoritative detail.

## Slice structure

```text
<Context>/
  API/Controllers/ Requests/ Resources/
  Application/DTOs/ Commands/ Queries/ Services/ Contracts/ Payloads/
  Domain/Aggregates/ Entities/ ValueObjects/ Enums/ Repositories/ Rules/ Events/
  Infrastructure/Models/ Mappers/ Repositories/ Adapters/ Providers/ Events/ Jobs/
```

Create directories only when the responsibility exists. Keep use cases and domain language together inside a bounded context.

## HTTP and application flow

- Prefer installed Zolta attributes for route, request, service, response, and API documentation declarations. Keep controller methods declarative.
- Put transport rules, route/query normalization, authorization, and server-derived `trustedData()` in a `BaseRequest` subclass. Merge trusted actor, tenant, or ownership data after client input.
- Map the request into an immutable framework-neutral DTO. Do not pass an HTTP request or unvalidated input array to the application layer.
- Use one invokable application service for a use case and dispatch one typed command or query through the installed `runAndCapture()` pattern where supported.
- Commands change state; query handlers remain side-effect free. Use `#[HandlesCommand(...)]` and `#[HandlesQuery(...)]` only after confirming the installed attribute signatures.
- Return explicit `Result` outcomes for commands and typed `Option`/collections for queries. Use a transaction only for a deliberate multi-step atomic boundary.

## Searchable reads

Treat the URL contract and persistence allow-list as separate gates:

1. The request declares accepted `filter`, `include`, `sort`, `page`, and `per_page` shapes and strictness.
2. The DTO and query carry the validated options.
3. The handler builds typed options with the installed query-options factory.
4. The repository independently allow-lists fields, relations, operators, and relation sorting.
5. Verify that every public filter/include/sort is intentionally supported or rejected by the repository.

Do not rely on request validation alone to restrict persistence queries. Scope reads by tenant and ownership at the repository boundary too.

## Domain and persistence

- Aggregates own invariants and intentful transitions. Use explicit create and restore/reconstitute paths; restore persisted state without replaying creation behavior.
- Use value objects for semantic immutable values. Keep rules deterministic and free from database or network checks.
- Put repository contracts inward and concrete Eloquent repositories, ORM models, and mappers in Infrastructure. Never leak Eloquent models inward.
- Keep aggregate repositories separate from screen-specific read projections.
- Enable repository read caching only when stale reads are acceptable; exclude auth/token/request-scoped identity paths and define invalidation for writes.

## Domain events and infrastructure wiring

Record a domain event only for an actual business side effect. Release events after successful persistence. If Laravel listeners are required, use one mapped wrapper/listener path, keep the listener thin, register it exactly once, and regenerate the event map after discovery changes. Do not dispatch both the domain event and wrapper manually or keep events with no consumer.

Before adding or removing event behavior, trace the complete path from `recordThat()` through persistence, event release, mapping, wrapper, listener, and consumer use case. Remove orphaned wrappers/listeners and duplicate registrations only after confirming there is no active consumer. Consumers must be idempotent and retry-safe; use after-commit behavior or an outbox when the delivery guarantee requires it.

Bind inward contracts through focused Infrastructure providers. Use a fresh binding by default; use a singleton only when shared stateless lifetime is intentional. Keep framework traits, queues, SDKs, HTTP clients, serialization, retries, and provider errors at the Infrastructure edge.

## Verification and documentation

Check the touched slice's tests, route output, generated route/CQRS maps, provider bindings, and API docs as applicable. When adding a concept or supported alternative, update the focused API topic and concept catalogue together. For cross-stack changes, also follow the full-stack contract and client verification guide.
