# Talred Starter API source map

Resolve these paths from the repository root. In the larger workspace, prefix them with `talred-starter/`.

- Lifecycle and concepts: `docs/api/index.md`, `docs/api/00-foundations/concepts.md`
- Dependency direction and DTO boundary: `docs/api/01-foundations/dependency-direction.md`, `docs/api/02-boundaries/dto-flow.md`
- Validation and trusted identity: `docs/api/02-boundaries/request-validation.md`, `docs/api/07-cross-cutting/identity-project-integration.md`
- Domain model and value objects: `docs/api/03-domain/ddd-model.md`, `docs/api/03-domain/value-objects.md`
- Application services, CQRS, and outcomes: `docs/api/04-application/application-services.md`, `docs/api/04-application/cqrs.md`, `docs/api/04-application/handler-outcomes.md`
- Routes and responses: `docs/api/05-presentation/routes.md`, `docs/api/05-presentation/responses.md`
- Persistence and infrastructure: `docs/api/06-infrastructure/index.md`, then its focused repositories, adapters, providers, configuration, events/jobs, and verification topics
- Vertical slices and verification: `docs/api/07-cross-cutting/vertical-slices.md`, `docs/api/07-cross-cutting/verification.md`
- Cross-stack contract: `docs/full-stack-contracts.md`

The installed package source decides version-specific behavior. The Starter docs define intended architecture, while the service remains responsible for dependency direction, business ownership, and its tests.
