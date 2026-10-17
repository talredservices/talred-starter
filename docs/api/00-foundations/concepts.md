# Zolta Architecture Concepts

[← Foundations index](index.md) · [↑ Architecture home](../index.md)

This catalogue is not a gate or a separate implementation layer. Start from any topic in the [architecture index](../index.md); use the references below to jump between principles and their implementation chapters.

This document is a point-by-point catalogue of the architectural concepts used when building services with the Zolta Framework. It separates general software principles from Zolta mechanisms and shows how the layers communicate.

The catalogue is synchronized with [Architecture index](../index.md): whenever a detailed pattern, responsibility, or lifecycle rule changes, update the corresponding concept point here, and reconcile any concept change back into the detailed guide.

Each concept should be taught with a small implementation example when the boundary or decision is easy to misunderstand. When Zolta provides more than one supported mechanism, document the preferred path and at least one meaningful alternative (for example, HTTP hydration versus direct DTO construction, or a synchronous listener versus an outbox worker). Alternatives are valid only when they preserve the same inward dependencies, typed contracts, business ownership, and failure semantics; this is flexibility, not permission to mix layers.

## Concept implementation map

The points below are the concepts’ implementation sources. Follow the linked document for the complete pattern, examples, alternatives, and verification guidance; this catalogue remains the concise architectural index.

- **Foundations and dependency direction:** [dependency direction](../01-foundations/dependency-direction.md)
- **Request validation and transport boundaries:** [request validation](../02-boundaries/request-validation.md)
- **DTOs and boundary mapping:** [DTO flow](../02-boundaries/dto-flow.md)
- **Aggregates, entities, rules, policies, specifications, events, and repositories:** [DDD model](../03-domain/ddd-model.md)
- **Value objects and Forge construction:** [value objects](../03-domain/value-objects.md)
- **Use-case orchestration and application ports:** [application services](../04-application/application-services.md)
- **Commands, queries, dispatch, validation, and transactions:** [CQRS](../04-application/cqrs.md)
- **Result, Option, payloads, and event release:** [handler outcomes](../04-application/handler-outcomes.md)
- **Routes, attributes, middleware, authorization, and discovery:** [routes](../05-presentation/routes.md)
- **Output DTOs, resources, envelopes, and response bridges:** [responses](../05-presentation/responses.md)
- **Persistence records and anti-corruption mapping:** [models and mappers](../06-infrastructure/models-and-mappers.md)
- **Aggregate repositories, projections, query options, and pagination:** [repository adapters](../06-infrastructure/repositories.md)
- **Zolta package adapters and service-local framework adapters:** [adapters](../06-infrastructure/adapters.md)
- **Composition roots, bindings, discovery, registration, and configuration:** [providers](../06-infrastructure/providers.md) · [configuration](../06-infrastructure/configuration.md)
- **Domain-event wrappers, listeners, queues, retries, and outbox choices:** [events and jobs](../06-infrastructure/events-and-jobs.md)
- **External ports, SDK/HTTP adapters, retries, and delivery choices:** [integrations](../06-infrastructure/integrations.md)
- **Infrastructure test boundaries:** [infrastructure verification](../06-infrastructure/verification.md)
- **Vertical slices and end-to-end feature flow:** [vertical slices](../07-cross-cutting/vertical-slices.md)
- **Domain-event delivery and Event Sourcing distinction:** [cross-cutting events](../07-cross-cutting/events.md)
- **Cross-layer architectural guarantees:** [cross-cutting verification](../07-cross-cutting/verification.md)


## Architectural foundation

See [dependency direction](../01-foundations/dependency-direction.md) and [vertical slices](../07-cross-cutting/vertical-slices.md).
- Product boundaries are expressed as bounded contexts.
- Each bounded context owns its ubiquitous language, use cases, domain model, ports, adapters, and API surface.
- Dependencies point inward toward business policy.
- Frameworks, databases, transports, queues, and external services are replaceable details.
- A feature is organized as a vertical slice through the layers rather than as a collection of unrelated technical folders.
- The architecture protects business meaning from delivery and infrastructure concerns.

## Domain-Driven Design concepts

See [DDD model](../03-domain/ddd-model.md), [value objects](../03-domain/value-objects.md), and [domain events](../07-cross-cutting/events.md).
- Ubiquitous language keeps code, conversations, and API contracts aligned with the business vocabulary.
- Bounded contexts define where a model and its language are valid.
- Aggregate roots protect transactional consistency boundaries.
- Entities have identity and lifecycle; their state changes through intentful behavior.
- Value objects model immutable values with normalization, equality, and self-validation.
- Factories create valid aggregates and provide explicit persistence reconstitution paths.
- Domain services hold domain behavior that does not naturally belong to one entity or value object.
- Domain policies express decisions using supplied facts without performing I/O.
- Specifications express reusable predicates and composable eligibility rules.
- Invariants protect conditions that must always hold.
- Domain exceptions express meaningful business failures.
- Repositories are domain-language contracts for loading and saving aggregate roots.
- Aggregates reference other aggregates by identity unless immediate consistency is explicitly required.
- Domain events are immutable, past-tense facts raised after meaningful state changes.
- Event Sourcing is a separate choice that requires an event store, replay, and stream rehydration; domain events alone are not Event Sourcing.

## Domain-layer implementation patterns

See [DDD model](../03-domain/ddd-model.md) and [value objects](../03-domain/value-objects.md).
- Aggregate roots should control their own construction; use a private constructor plus named `create(...)` and `restore(...)`/`reconstitute(...)` paths.
- A creation path establishes defaults and may record a creation event; a reconstitution path restores trusted persisted state and must not emit a new creation event.
- Aggregate roots extend the Zolta entity/event lifecycle: domain behavior records events internally and the application boundary releases them after successful persistence.
- `recordThat(...)` is an internal aggregate operation; callers never inject or publish events directly into an aggregate.
- `releaseEvents()` returns and clears pending events, so each event is handed to the application pipeline exactly once.
- Aggregate state is private; callers use intentful methods such as `suspend()`, `reactivate()`, `scheduleDeletion()`, or `changeEmail()` rather than public property mutation.
- Domain methods should be idempotent where the business meaning permits it; a repeated transition can return a deliberate no-op result instead of creating duplicate state or events.
- State machines are represented with domain enums or value objects, while the aggregate owns legal transitions and transition side effects.
- Entities may exist inside an aggregate when their identity and lifecycle are only meaningful within that aggregate boundary.
- Nested entities should be created or restored through factories and compared using domain identity or a domain equality method, not ORM identity semantics.
- Sensitive concepts such as credentials, tokens, money, identifiers, and dates should use semantic value objects or dedicated types when they carry invariants or serialization rules.
- Factories translate creation/reconstitution input into domain types and keep construction policy out of repositories and controllers.
- Factory input should be explicit and typed at the Application boundary; array-based factories are compatibility conveniences and should not become an unvalidated domain API.
- Domain rules validate one value; specifications express reusable predicates; invariants validate relationships across a completed object; policies decide based on supplied facts; domain services coordinate behavior that belongs to no single object.
- Specifications and invariants are deterministic and side-effect free. A uniqueness or availability check that needs a database or external service is an Application capability, not a pure Domain specification.
- A policy may return an eligibility decision for an expected business condition, while an impossible or unsafe state should raise a semantic domain exception; do not flatten every failure into `false`.
- Domain transformations normalize representations before rules run; they must not perform persistence, network, configuration, or framework calls.
- Domain exceptions should describe business meaning (`AccountLocked`, `InvalidConfiguration`, `InsufficientBalance`) rather than expose database, HTTP, or framework errors.
- Repository interfaces use domain language and return aggregates, nullable aggregates, iterables, or typed pagination; they must not expose ORM query builders or persistence models.
- Domain read accessors may expose immutable value objects and deliberate projections, but a read accessor must not become a public mutation backdoor.
- Time-dependent rules should receive a clock or resolved time when deterministic testing and replay matter; constructing wall-clock time deep inside behavior makes tests and reconstitution less reliable.
- Passwords, access tokens, and other secrets require distinct creation and reconstitution semantics; a hashed persisted secret must never be treated as a newly supplied plaintext value.
- Domain code may use PHP standard-library types and Zolta domain primitives, but never framework helpers, facades, ORM models, or external SDKs.

## Layer tree concepts

See the [architecture index](../index.md) and each layer index: [boundaries](../02-boundaries/index.md), [domain](../03-domain/index.md), [application](../04-application/index.md), [presentation](../05-presentation/index.md), and [infrastructure](../06-infrastructure/index.md).
- A bounded context keeps API, Application, Domain, and Infrastructure under one business-owned root.
- API contains controllers, requests, resources, middleware, and presentation exceptions.
- Application contains input/output DTOs, commands, queries, handlers, application services, ports, and payloads.
- Domain contains aggregates, entities, value objects, enums, factories, repositories, rules, specifications, invariants, policies, domain services, events, and semantic exceptions.
- Infrastructure contains persistence models, mappers, repositories, providers, jobs, listeners, framework event wrappers, and external adapters.
- Directories are optional building-block markers, not mandatory boilerplate; create them when the bounded context has that responsibility.
- Tree organization follows business language and adapter responsibility, not framework package names or database tables.
- A vertical slice references neighboring layer directories through contracts and typed data, while preserving inward dependency direction.

## Clean Architecture concepts

See [identity, authentication, and authorization](../07-cross-cutting/identity-and-authorization.md) for principal resolution, route abilities, trusted identity context, policy layering, and failure semantics.

See [dependency direction](../01-foundations/dependency-direction.md), [adapters](../06-infrastructure/adapters.md), and [vertical slices](../07-cross-cutting/vertical-slices.md).
- The Domain contains the most stable business policy.
- The Application layer coordinates use cases and depends on Domain abstractions.
- The API layer adapts HTTP and other presentation transports to Application.
- Infrastructure implements ports owned by Domain or Application.
- The dependency rule is `API → Application → Domain`, with Infrastructure pointing inward through implementations.
- Inner layers do not import outer-layer frameworks, models, controllers, resources, or adapters.
- Dependency inversion keeps interfaces close to the policy that needs them.
- Ports describe capabilities, not technical products or entire workflows.
- Adapters translate between external representations and inner-layer types.
- Composition roots bind interfaces to concrete adapters at startup.
- Framework code is confined to boundary adapters and composition/configuration code.
- Cross-cutting concerns are attached through explicit adapters, middleware, or framework integration points.

## CQRS concepts

See [CQRS](../04-application/cqrs.md), [application services](../04-application/application-services.md), and [handler outcomes](../04-application/handler-outcomes.md).
- Commands express intent to change state.
- Queries retrieve state and do not mutate it.
- CQRS separates operation responsibilities, not necessarily bounded contexts or directories.
- A bounded context may contain both command and query trees.
- Each command or query represents one use-case intent.
- Messages are immutable and typed.
- A message has one mapped handler with one responsibility.
- Command handlers load aggregates, invoke domain behavior, persist changes, and return `Result`.
- Query handlers read through a repository or projection contract and return `Option` when absence matters.
- Empty collections are successful typed results, not absent results.
- Command and query mappings are declared with Zolta handler attributes.
- Zolta’s CQRS proxy hydrates messages, applies configured validation, invokes the mapped handler, and captures the outcome.
- `ApplicationService::runAndCapture()` is the application boundary for executing a message and preserving its envelope.
- `ApplicationService::transactional()` makes an intentional immediate-consistency boundary explicit.

## Validation concepts

See [request validation](../02-boundaries/request-validation.md), [DTO flow](../02-boundaries/dto-flow.md), [CQRS](../04-application/cqrs.md), and [value objects](../03-domain/value-objects.md).
- Transport validation protects the API boundary from malformed input.
- Request authorization decides whether a principal may enter the transport boundary.
- Trusted request data is server-derived and overrides client-supplied authority-bearing values.
- DTO mapping validation protects constructor completeness and type shape.
- CQRS/message validation applies rules that must hold for every command/query entry point.
- Value-object validation protects a value regardless of whether input came from HTTP, CLI, queue, or tests.
- Aggregate invariants and policies protect business correctness using current domain state and resolved facts.
- Infrastructure constraints protect persistence and query execution.
- Validation must be placed at the narrowest layer that owns the rule.
- No single outer validation layer can replace domain invariants.

## DTO and data-boundary concepts

See [DTO flow](../02-boundaries/dto-flow.md), [application services](../04-application/application-services.md), and [responses](../05-presentation/responses.md).
- Input DTOs are framework-neutral application inputs.
- Output DTOs are framework-neutral application results.
- DTOs prevent HTTP requests, ORM models, and transport serialization from crossing into inner layers.
- DTOs are use-case-specific and immutable.
- `FromRequest` aliases map transport names to DTO constructor parameters.
- Application services convert validated primitives into domain value objects where required.
- Application services map payload/domain/projection data into output DTOs.
- DTOs expose only data intended for the next layer; secrets and persistence internals stay private.
- Queue, CLI, and event consumers can construct the same Application DTOs without pretending to be HTTP requests.
- Compatibility arrays are allowed only as deliberate, isolated legacy contracts.

## Zolta flexibility principle

See the alternatives in [request validation](../02-boundaries/request-validation.md), [CQRS](../04-application/cqrs.md), [responses](../05-presentation/responses.md), [adapters](../06-infrastructure/adapters.md), and [events and jobs](../06-infrastructure/events-and-jobs.md).
- Zolta is convention-oriented, not pattern-imprisoning: it provides a preferred path while allowing deliberate alternatives.
- Attribute-driven controllers are the default for ordinary endpoints, but a manual controller/adapter may be used for exceptional transport behavior.
- Class-level and method-level route declarations are both supported; choose based on endpoint cohesion.
- Request-to-DTO mapping can use declarative `#[Request]` hydration, an explicit mapper, or direct construction for non-HTTP entry points.
- Typed output DTOs and resources are the preferred public contract, while compatibility arrays and direct response payloads remain available for isolated legacy or transport-specific cases.
- Commands and queries may share one bounded context; a query may use an aggregate repository or an Application-owned projection contract according to the read need.
- A repository may use Zolta `BaseRepository` facilities or a custom adapter, as long as the inward contract and query-safety rules remain intact.
- Value objects may use named factories, constructors, or Forge `resolve()`/attribute pipelines; every path must preserve the same invariants.
- `ApplicationService::runAndCapture()` is the normal message boundary; direct `dispatch`, `ask`, `run`, or `make` calls are available when an explicit orchestration requires them.
- Transactions are opt-in workflow boundaries, not an automatic wrapper around every command or query.
- Domain events may be consumed synchronously, queued, or published through an outbox according to the required delivery guarantee.
- Laravel is one adapter; the Zolta contracts and bridges allow other presentation, persistence, queue, or integration adapters.
- Flexibility does not mean bypassing architecture: every alternative must preserve dependency direction, typed boundaries, domain ownership, explicit failure semantics, and testability.
- Choose the simplest supported option that satisfies the use case, document deviations from the preferred path, and avoid introducing a second pattern without a concrete reason.

## Zolta HTTP concepts

See [routes](../05-presentation/routes.md), [request validation](../02-boundaries/request-validation.md), and [responses](../05-presentation/responses.md).
- Route attributes declare URI, verbs, middleware, authentication, authorization metadata, and route names.
- Request attributes bind a transport request to a request class and input DTO.
- Service attributes bind a route to one invokable application service.
- Response attributes bind a route to a presentation resource and optional transformation method.
- Documentation attributes keep OpenAPI metadata near the endpoint contract.
- Attribute discovery builds route and handler metadata from source declarations.
- The route invoker resolves metadata, validates configuration, authorizes route gates, creates DTOs, invokes the service, transforms resources, and wraps the response.
- Controllers are declarative endpoint metadata holders whenever the attribute pipeline is sufficient.
- Manual controller actions are an alternative for exceptional transport behavior, not the default use-case location.

## Zolta request and validation pipeline

See [request validation](../02-boundaries/request-validation.md) and [DTO flow](../02-boundaries/dto-flow.md).
- Route metadata selects the request and DTO classes.
- The request adapter performs authorization.
- Route and query parameters are normalized.
- Transport validation rules run.
- Validated data is collected.
- Query options are normalized and allow-listed when configured.
- `trustedData()` is merged last so server context cannot be overridden by the client.
- The result is mapped to the input DTO.
- The application service receives only the DTO, not the framework request.

## Zolta Forge concepts

See [value objects](../03-domain/value-objects.md) and [DDD model](../03-domain/ddd-model.md).
- `ValueObject` supplies immutable-style construction, access, equality, serialization, and recursive normalization.
- `AbstractUuid` provides semantic UUID identity specializations.
- `resolve()` invokes the Forge construction pipeline.
- Transforms normalize values before validation.
- Rules validate individual properties.
- Specifications apply reusable predicates.
- Invariants validate relationships across completed value-object state.
- Policies evaluate value-object decisions using supplied facts/options.
- `VOConstructionContext` supplies controlled runtime options without coupling the value object to a caller.
- Construction must be deterministic and free of persistence or network I/O.

## Handler outcome concepts

See [handler outcomes](../04-application/handler-outcomes.md), [CQRS](../04-application/cqrs.md), and [events and jobs](../06-infrastructure/events-and-jobs.md).
- Command handlers return explicit `Result::success()` or `Result::failure()`.
- Query handlers return `Option::some()`, `Option::none()`, or `Option::error()` where absence/error semantics require it.
- `MessagePayloadInterface` gives a handler result a named, structured shape.
- Successful command results may carry released domain events.
- Events are released once, after persistence, and coordinated by the application/transaction boundary.
- Raw aggregates, ORM models, HTTP responses, and arbitrary scalars do not belong in handler outcomes.
- Named payloads are not public API resources; they are the handler-to-application contract.

## Application-service concepts

See [application services](../04-application/application-services.md), [CQRS](../04-application/cqrs.md), and [DTO flow](../02-boundaries/dto-flow.md).
- An application service is one invokable, typed use-case entry point.
- It dispatches one primary command or query.
- It coordinates ports and transaction boundaries without owning domain rules.
- It extracts `Result`/`Option` through `getOrFail()` according to the application error policy.
- It maps payloads to output DTOs.
- It returns a DTO to the presentation pipeline, not JSON or a framework response.
- It can be called from HTTP, CLI, jobs, or event consumers.
- A coordinator spanning several messages must expose an explicit workflow and consistency requirement.

## Resource and response concepts

See [responses](../05-presentation/responses.md) and [routes](../05-presentation/routes.md).
- A resource is a presentation-layer transformer.
- A resource selects and names the public fields returned by an endpoint.
- Zolta `Resource` accepts an output DTO and exposes controlled helpers such as `get()`, `has()`, `all()`, `set()`, and `router()`.
- Resources do not query repositories, dispatch messages, enforce domain rules, or perform I/O.
- `#[Response(Resource::class)]` connects a route to its resource.
- `ResponseFactory` normalizes the transformed result into `ResponsePayload`.
- A configured `ResponseBridge` emits JSON, HTML, Inertia, or another transport representation.
- The standard response envelope distinguishes `success`, `message`, `data`, and `errors`.
- Singular, collection, empty, and acknowledgement shapes are explicit public contracts.
- Direct `ResponsePayload`/`HttpResponse` returns are reserved for exceptional transport needs such as downloads, redirects, streams, or custom headers.

## Infrastructure layer structure and concepts

See [infrastructure configuration](../06-infrastructure/configuration.md) for discovery roots, generated maps, route/OpenAPI settings, identity/security configuration, environment defaults, and configuration verification.

See the [infrastructure index](../06-infrastructure/index.md) and its focused topics: [mappers](../06-infrastructure/models-and-mappers.md), [repositories](../06-infrastructure/repositories.md), [providers](../06-infrastructure/providers.md), [events and jobs](../06-infrastructure/events-and-jobs.md), [integrations](../06-infrastructure/integrations.md), and [verification](../06-infrastructure/verification.md).
A bounded context may organize its Infrastructure tree like this:

```text
Infrastructure/
  Models/Eloquent/          persistence representations
  Mappers/                  persistence ↔ domain translators
  Repositories/             concrete repository/read adapters
  Persistence/
    Migrations/
    Factories/
    Seeders/
    Providers/              persistence registration
  Providers/                composition-root bindings
  Events/                   framework event wrappers
  Listeners/                framework event consumers
  Jobs/                     retryable queued work
  Mail/                     mailables and delivery adapters
  Notifications/            framework notification adapters
  Services/                 external/auth/storage/framework adapters
  Webhooks/                 outbound integration publishers
```

- The tree is organized by adapter responsibility; omit directories that a bounded context does not need.
- `Models/Eloquent` contains persistence models only. ORM relations, casts, scopes, and persistence concerns stay there; business invariants stay in Domain.
- `Mappers` form the anti-corruption boundary. They convert records to domain value objects and aggregate `restore`/`reconstitute` calls, and convert aggregates back to storage primitives.
- `Repositories` implement Domain repository contracts and Application read/capability ports. They translate typed options into Zolta repository queries, enforce server-side scope and allow-lists, and map results back to aggregates, projections, iterables, or pagination.
- Repository adapters may use Zolta `BaseRepository`, query options, filters, includes, sorting, pagination, caching, and streaming facilities, but those technical choices must not leak through an inner-layer contract.
- `Persistence` owns migrations, database factories, seeders, and persistence-specific providers. Migrations express storage evolution, not domain workflow.
- `Providers` are composition roots. Bind every inward interface to a concrete adapter explicitly and register listeners, migrations, authentication, mail, and other integrations in a deliberate order.
- Framework event wrappers translate pure domain events into framework-dispatchable events. Use Zolta domain-event mapping attributes where supported; never add Laravel event or queue traits to Domain events.
- `Listeners` consume integration events and invoke Application-owned capabilities or use cases. They do not reimplement domain rules and should be idempotent.
- `Jobs` isolate retryable, time-bounded side effects. They should carry stable identifiers rather than large ORM graphs, check idempotency before acting, define timeout/backoff/attempt policy, and record terminal failure when required.
- `Mail` and `Notifications` adapt framework delivery to Application ports. Templates and channel details stay outside Domain and Application.
- `Services` wrap external identity, storage, HTTP, rate limiting, secrets, and framework APIs behind focused adapters. Avoid a single Infrastructure service that owns an entire bounded-context workflow.
- `Webhooks` and outbound integrations validate destinations, use bounded timeouts, authenticate payloads, prevent unsafe redirects/SSRF where relevant, and persist delivery state when retries or auditability matter.
- Infrastructure adapters may depend on every inner layer they implement, but inner layers never depend on Infrastructure classes or namespaces.
- Infrastructure failures should be translated into semantic capability failures at the Application boundary; raw ORM, HTTP-client, or framework exceptions should not become Domain concepts.
- Persistence and side effects need an explicit consistency policy. Use `afterCommit`, an outbox, or an equivalent durable mechanism when a consumer must not observe uncommitted state or publication must survive process failure.
- Infrastructure tests focus on mapper round-trips, repository constraints, query allow-lists, provider bindings, integration serialization, retry/idempotency behavior, and external-client contracts.

## Adapter concepts

See [adapters](../06-infrastructure/adapters.md) and [dependency direction](../01-foundations/dependency-direction.md).
- Zolta Core packages define framework-agnostic contracts; framework adapters implement those contracts outside Core.
- A package adapter implements `FrameworkAdapterInterface` with `supports()`, `priority()`, and `bindings()`.
- Composer `extra["zolta-framework-adapter"]` metadata lets `FrameworkBootstrap` discover adapter classes without hard-coding a framework into Core.
- `FrameworkRegistry` filters unsupported adapters, orders candidates by priority, and resolves the first binding for a requested abstraction.
- A package may also expose framework service providers for bootstrapping; provider registration and framework-adapter registry discovery solve different problems.
- A service-local adapter is an Infrastructure adapter used when the required Zolta abstraction or integration is not yet available.
- Local adapters are grouped under `Infrastructure/Adapters/<InnerLayer>/...`, where the inner-layer name identifies the contract being implemented.
- `Adapters/Domain` implements Domain repository or port contracts; `Adapters/Application` implements Application capability ports; `Adapters/API` implements presentation/framework bridges.
- Adapter placement does not permit the named inner layer to import the adapter or its framework dependencies.
- An adapter translates external/framework types, errors, configuration, and lifecycle into neutral contract behavior.
- Bind local local adapters explicitly in a bounded-context Infrastructure provider; use a package adapter and Composer discovery when the integration is reusable across services.
- Adapter priority must be intentional when several adapters support one runtime; test the resolved binding rather than relying on registration order.
- Local adapters are appropriate for narrow, service-local gaps and should be candidates for upstreaming when multiple services need them.
- A local adapter must not hide a business workflow, repair missing domain logic, or become a backdoor for Laravel imports in Domain/Application.
- Local adapter tests verify support detection, binding resolution, contract behavior, failure translation, and absence of framework-type leakage.

## Infrastructure and integration concepts

See [repositories](../06-infrastructure/repositories.md), [adapters](../06-infrastructure/adapters.md), [events and jobs](../06-infrastructure/events-and-jobs.md), [integrations](../06-infrastructure/integrations.md), and [providers](../06-infrastructure/providers.md).
- Infrastructure implements inward-facing repository and capability contracts.
- Persistence mappers convert records to aggregate reconstitution calls and back to storage primitives.
- ORM models remain inside Infrastructure.
- External clients are wrapped by focused ports and adapters.
- Queue, mail, storage, OAuth, and framework-event concerns stay outside Domain and Application.
- Providers bind interfaces to implementations at the composition root.
- Domain events are mapped to framework integration events only at the Infrastructure boundary.
- Event consumers are idempotent, retry-safe, and explicit about delivery guarantees.
- Transactional outbox or an equivalent durable mechanism is required when post-commit publication must survive process failure.

## Layer communication summary

See [vertical slices](../07-cross-cutting/vertical-slices.md), [dependency direction](../01-foundations/dependency-direction.md), and [responses](../05-presentation/responses.md).
- API communicates with Application through input DTOs and output DTOs.
- Application communicates with Domain through commands, queries, aggregates, value objects, policies, and repository/port interfaces.
- Domain communicates outward only by returning values, raising domain exceptions, and recording domain events.
- Infrastructure communicates with inner layers by implementing their contracts.
- Application services orchestrate; handlers execute one message; aggregates enforce invariants; resources format output.
- No layer skips over its neighbor to reach a technical detail owned by another layer.
- Every crossing converts data into the type owned by the receiving layer.
- The public response shape is decided at the resource boundary, not inside Domain or Application.
- Known domain failures are translated at the API boundary. With the installed attribute router, use an API-owned service adapter when a plain domain exception would otherwise be rendered as 500; keep HTTP types out of Application and Domain. See [responses](../05-presentation/responses.md#domain-failures-at-the-http-boundary).

## Client navigation concepts

See [client architecture](../../client/02-client/client.md#dashboard-navigation-and-transitions).
- Nuxt Vue page transitions and browser view transitions are separate route policies; `pageTransition: false` does not disable `viewTransition`.
- Shared dashboard shells that redirect after mount disable browser view transitions through layout-scoped global middleware to prevent overlapping visual updates from blocking already-rendered data.

## Architectural quality principles

See [cross-cutting verification](../07-cross-cutting/verification.md) and [infrastructure verification](../06-infrastructure/verification.md).
- Prefer explicit contracts over implicit conventions.
- Prefer one responsibility per class and one intent per message.
- Prefer immutable inputs, value objects, and payloads at boundaries.
- Prefer named factories and aggregate methods over public mutation.
- Prefer typed read contracts for screen-specific data.
- Prefer eventual consistency and idempotent consumers across independent contexts.
- Prefer evidence from installed Zolta source over assumptions about framework behavior.
- Treat security context as trusted server data, never as client input.
- Test business invariants, orchestration, mappings, adapters, public responses, and dependency boundaries.
- Document version-specific behavior and do not present unsupported options as guarantees.
