# Changelog

## 0.1.0-beta.1 — release candidate

First public beta of Talred Starter: a reference for clean architecture, domain
driven design, CQRS, Laravel APIs, and Nuxt clients/BFFs built on Talred PHP.

- Includes a workspace/organization example, a project and task board, and a
  separate SaaS consumer with Identity-hosted authentication.
- Uses the independent public Talred Identity project, with pinned Identity
  images for Docker and Starter-owned branded authentication layers.
- Provides standalone Docker setup and a native PHP/Node setup guide, plus an
  idempotent initializer for projects, API/BFF clients, roles, permissions,
  registration policies, and hosted applications.
- Keeps local credentials and runtime data outside Git and does not provision
  an application user or automation account.
- Preserves generated credentials across repeat setup and configures the
  Platform API's Identity onboarding trust before starting consumers.
- Returns explicit not-found, conflict, and validation responses for project,
  task, and workspace failures instead of accidental server errors.
- Updates both API dependency locks to patched Laravel, CommonMark,
  Flysystem, and Symfony releases and removes an unavailable optional
  `boost:update` command from the Composer update hook.

This beta is for evaluation and adaptation. APIs, examples, and setup contracts
may change before 1.0. The local Docker stack is not a production deployment:
it uses loopback ports, development token exposure, and local mail logging.
Starter registration is invitation-only; Tasks and SaaS permit registration.

Two upstream development/build dependencies have temporary, expiring security
exceptions. CI verifies that neither is shipped in the production application;
see the [dependency policy](docs/security/dependency-policy.md). Run production
artifacts rather than exposing development servers.

Publication date, reviewed source commit, image digests, and final fresh-clone
verification evidence are filled in when the reviewed `main` commit is tagged.
