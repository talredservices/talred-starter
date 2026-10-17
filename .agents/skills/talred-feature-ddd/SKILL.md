---
name: talred-feature-ddd
description: "Mandatory first-stage transformation for new TalredServices ecosystem business features: convert a feature prompt into a pragmatic DDD business model, ownership map, invariants, contracts, and acceptance criteria before implementation. Use across TalredServices, Identity, DevOps, and generated applications; do not force domain modeling onto purely presentational changes."
---

# Talred Feature DDD

This is Step 1 of the TalredServices Agile feature workflow. Run it after shaping the backlog item and before changing implementation code for any feature with business meaning, state, authorization, persistence, an API, an integration, or a workflow.

## Required transformation

Translate the request into a concise, reviewable domain transformation. Establish:

- the business outcome and bounded context;
- primary actors, stakeholders, and tenant/project scope;
- commands/use cases and their preconditions;
- aggregates, entities, value objects, and domain services only where the behavior requires them;
- invariants, policies, authorization decisions, and failure semantics;
- domain events and read models/projections when they are needed;
- repository/port needs and external integration ownership;
- the boundary map: domain/API/persistence, BFF/browser transport, client presentation, runtime/DevOps, and Identity;
- acceptance criteria that can later be verified at each affected seam.

Do not invent entities merely to make a diagram. If the request is presentation-only, record `classification: presentation-only`, explain why no business state or policy changes, and proceed under the owning UI skill.

## Gate

Before implementation, create or present a domain transformation document using the contract in `references/domain-transformation-schema.md`. Then run:

```bash
Use the engine's `zolta_engine_domain_transform_validate` capability with the
target root and transformation path.
```

The validator must report `valid: true` and no unresolved blocking assumptions. A valid document is a design gate, not permission to skip authorization, contract, testing, or live verification.

If required information is genuinely unavailable, list it as an explicit assumption or decision needed. Do not hide uncertainty in implementation details. Keep the document small enough to review and update it when the feature scope changes.

## Handoff to implementation

After the gate passes, hand the document to the owning implementation skill. For
cross-stack work, use the engine's dynamic workspace routing together with
`$talred-api` and/or `$talred-client`; for API-only work use
`$talred-api`; for client/BFF work use `$talred-client`. Preserve the
domain language at boundaries and map transport models rather than leaking
persistence models into browser code.
