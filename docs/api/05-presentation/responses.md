# Application output and presentation response

[← 05-presentation index](index.md) · [↑ Architecture home](../index.md) · [Concept catalogue](../00-foundations/concepts.md)

Related: [DTO flow](../02-boundaries/dto-flow.md) · [Routes](routes.md) · [Handler outcomes](../04-application/handler-outcomes.md)

---

## Application output and presentation response

The outward boundary is deliberately a second mapping step. A handler payload is an application message result; an output DTO is the stable result of a use case; a resource is the transport representation; and the Zolta response bridge produces the framework response.

```text
Result / Option from handler
  → ApplicationService::runAndCapture() + getOrFail()
  → application output ResponseDTO
  → #[Response(Resource::class)]
  → Resource::toArray()
  → Zolta ResponseFactory / ResponsePayload
  → configured ResponseBridge
  → JSON, HTML, or another presentation response
```

### Application services extract and map the envelope

An application service owns the hand-off from CQRS to a use-case result. It should:

1. dispatch one command or query through `ApplicationService`;
2. preserve the `Result`/`Option` outcome until the service boundary;
3. call `getOrFail()` once so failures and absence follow the configured exception policy;
4. extract the named payload fields;
5. map domain or projection data into a typed output `ResponseDTO`;
6. return that DTO to the Zolta route pipeline.

```php
#[AsApplicationService]
final readonly class GetRecordService
{
    public function __construct(private ApplicationService $applicationService) {}

    public function __invoke(GetRecordDTO $dto): RecordResponseDTO
    {
        ['record' => $record] = $this->applicationService
            ->runAndCapture(GetRecordQuery::class, [
                'recordId' => new RecordId($dto->recordId),
                'actorId' => new ActorId($dto->actorId),
            ])
            ->getOrFail();

        return RecordResponseDTO::fromDomain($record);
    }
}
```

The service is the right place for primitive-to-domain conversion and payload-to-DTO mapping because it knows the use-case contract. It must not serialize JSON, choose HTTP status codes, instantiate resources, expose ORM models, or return a framework response. For a projection, use `fromProjection()` rather than pretending the read model is an aggregate.

`runAndCapture()` may return a `Result` for commands or an `Option` for queries. Do not bypass it by constructing handlers directly, calling framework buses, or depending on an arbitrary raw handler return. If a service coordinates multiple messages, make the workflow and transaction boundary explicit; still return one final DTO for the use case.

### Output DTOs: the Application-to-API contract

Output DTOs extend `Zolta\\Support\\Application\\DTO\\Output\\ResponseDTO` (or implement its contract). They are final, immutable, use-case-specific, and framework-agnostic. They should expose only data intentionally published by the use case.

```php
final class RecordResponseDTO extends ResponseDTO
{
    public function __construct(
        public readonly string $id,
        public readonly string $title,
        public readonly string $status,
    ) {}

    public static function fromDomain(Record $record): self
    {
        return new self(
            id: $record->id()->toString(),
            title: $record->title()->toString(),
            status: $record->status()->toString(),
        );
    }
}
```

A DTO may provide `fromDomain()`, `fromProjection()`, or a named factory for a collection/pagination result. Its `toArray()` behavior comes from the Zolta DTO support and is useful for resources and non-HTTP callers. Keep mapping explicit: rename fields, flatten value objects, format dates, and omit secrets deliberately. Do not expose Eloquent models, repositories, requests, domain event objects, credentials, internal IDs, or mutable aggregates directly from a public DTO.

Use one output DTO per public use-case shape. A detail response, collection response, dashboard summary, mutation acknowledgement, and error contract have different consumers and should not share a catch-all DTO. For an intentionally empty command result, return a small acknowledgement DTO or an explicit empty DTO shape rather than making clients infer meaning from `null`.

#### DTO alternatives

| Situation | Appropriate choice |
| --- | --- |
| Normal HTTP use case | Typed `ResponseDTO`, then a resource. |
| Queue, CLI, or internal caller | The same `ResponseDTO`, or the application payload when no presentation contract is needed. |
| Collection/read model | Dedicated collection DTO containing items and pagination/meta fields. |
| Compatibility endpoint | A deliberate array contract, documented and isolated; migrate toward a DTO. |
| Binary/download/stream response | A dedicated outer adapter/response port; do not force bytes through a JSON DTO. |

### Resources: the presentation transformation

A resource is an API-layer formatter. It receives the output DTO (or a compatibility array), selects and names the public fields, and returns the exact data shape that belongs inside the response envelope. It is allowed to know Zolta HTTP resource primitives and presentation concerns; it must not query repositories, invoke commands, enforce domain rules, or perform I/O.

The standard resource extends `Zolta\\Http\\Response\\Resources\\Resource` and implements `toArray()`:

```php
final class RecordResource extends Resource
{
    public function toArray(): array
    {
        return [
            'record' => [
                'id' => $this->get('id'),
                'title' => $this->get('title'),
                'status' => $this->get('status'),
            ],
        ];
    }
}
```

The base resource accepts a `ResponseDTO` (an array is supported only for compatibility) and provides protected `get()`, `has()`, `set()`, `all()`, and `router()` helpers. Prefer `get()`/`all()` with an explicit returned array so the public contract is visible. Use `has()` for optional fields, and use `router()` only when a URL must be generated from the configured router. Do not mutate the DTO with `set()` unless a narrowly justified presentation normalization requires it.

For collections, return a stable shape with items and metadata. Do not let an empty collection become `null` or disappear:

```php
final class RecordCollectionResource extends Resource
{
    public function toArray(): array
    {
        return [
            'records' => $this->get('records') ?? [],
            'meta' => $this->get('meta') ?? [],
        ];
    }
}
```

A resource should be deterministic: the same DTO produces the same representation. Conditional fields should be based on explicit DTO state or authorized presentation context, not hidden database queries. Nested resources are useful for deliberate composition, but keep the output contract shallow enough for clients to understand and version.

#### Resource choices and alternatives

- Use a dedicated Zolta `Resource` for a stable public API shape, field renaming, nested presentation, collection metadata, or links.
- Use a framework `JsonResource`/equivalent only when the configured adapter explicitly supports it and the team accepts the framework coupling in API.
- Omit `#[Response]` only for an intentionally raw/compatibility endpoint; Zolta then returns the service result through its generic normalization path. This is weaker as a public contract.
- Return a `ResponsePayload`/`HttpResponse` directly only for exceptional transport cases such as a file, redirect, custom header, or streaming response. Normal use cases should not build responses themselves.

### Controller declaration and final response

The preferred controller is declarative and empty. It declares route, request/DTO, service, response resource, and documentation attributes; Zolta resolves and invokes the application service:

```php
#[Route(path: 'v1/records/{record}', methods: ['GET'], middleware: ['api'], name: 'records.show')]
#[Request(GetRecordRequest::class, GetRecordDTO::class)]
#[Service(GetRecordService::class, 'Record retrieved.')]
#[Response(RecordResource::class)]
#[Doc(summary: 'Get a record', tags: ['Records'])]
final class GetRecordController extends Controller {}
```

The controller has no manual action because `RouteInvoker` reads the metadata, creates the request DTO, invokes the service, applies the declared resource, and passes the result to `ResponseFactory`. The `#[Response]` attribute accepts a resource class and an optional method name (default `toArray`). Keep the resource declaration on the same route/class/method as the service contract it formats.

The installed Laravel path normalizes the successful result as `ResponsePayload(success, message, data, errors, debug)` and delegates to the configured `ResponseBridge`. The standard JSON bridge emits:

```json
{
  "success": true,
  "message": "Record retrieved.",
  "data": { "record": { "id": "…", "title": "…", "status": "…" } },
  "errors": []
}
```

`message` is route metadata for a human-readable operation message, not domain state. `success` describes transport outcome. `data` is the resource's public shape. `errors` is reserved for failure responses, and debug information must never be exposed outside the configured development mode.

### Domain failures at the HTTP boundary

Installed behavior verified in `talred/http` v2.1.4: the Laravel attribute
`RouteInvoker` catches failures before the application's global Laravel
exception renderer. Its `ExceptionResponseFactory` preserves Symfony HTTP
exceptions and Zolta renderable exceptions, but treats an ordinary domain
exception as an unexpected 500. A global `withExceptions()` callback alone
therefore does not map failures inside these attribute routes.

Keep domain exceptions framework-independent. Where this installed router
lacks a configurable domain mapper, an API-owned invokable service adapter can
wrap the application use case and translate known failures into 404 (absent or
not owned), 409 (state/uniqueness conflict), or 422 (invalid business input).
Point `#[Service]` at that adapter and keep `#[Request]` and `#[Response]`
declarative. The adapter returns the same output DTO; it does not orchestrate
business work or replace the Application service. A single adapter may cover
a small, explicit set of related DTO/use-case pairs when they share one error
contract. Unknown failures remain server errors rather than exposing their
messages. Verify the real route and persisted state in a feature test, then
verify that the BFF preserves the intended client error.

### Data-shape rules

- Choose one stable root shape per endpoint: a singular resource (`record`), a collection (`records` plus `meta`), or an explicit acknowledgement.
- Keep naming consistent between DTO properties, resource keys, and documented schemas; rename only at the resource boundary when the public contract requires it.
- Represent an empty collection as an empty array with metadata, not `null` or an absent key.
- Do not leak persistence column names, ORM serialization, internal exception messages, or domain event envelopes.
- Keep transport envelopes consistent across endpoints; put endpoint-specific content under `data`.
- Version breaking shape changes through the public API contract, not by silently changing a resource.
- Generate and verify OpenAPI schemas from the route/resource declarations where the adapter supports it.

### Response verification checklist

- The service returns a typed `ResponseDTO` and does not know HTTP/resources.
- The DTO exposes only intentional application output and has explicit domain/projection mapping.
- The resource depends only on the DTO and presentation helpers; it performs no I/O or business decisions.
- `#[Response]` points to the correct resource and method.
- The controller remains declarative and has one clear operation owner.
- Empty, singular, collection, and error shapes are tested as public contracts.
- Status, message, `success`, `data`, and `errors` are consistent with the configured response bridge.
- JSON/view/download alternatives are chosen explicitly rather than mixed inside application services.

---

---

[Architecture index](../index.md) · [Concept catalogue](../00-foundations/concepts.md)
