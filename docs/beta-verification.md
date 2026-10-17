# First beta verification

Candidate: `0.1.0-beta.1`, reviewed locally on 2026-10-16. This is preparation
evidence, not an announcement of a published release.

## What was verified

The candidate was copied into independent temporary directories without local
environment files, installed dependencies, databases, or generated output.
Native setup installed dependencies from the public registries and used public
Identity source pinned to `0fb11ef1b570b88dd6f63cdc8d04d0e305f6a89d`.
Docker pulled the pinned Identity Server/Nginx images anonymously and compiled
the Console from that public source with Starter's bundled auth layers.
Native and Docker used separate ports, databases, and runtime configuration.

Both modes passed these ten HTTP workflow groups:

1. Public pages and unauthenticated API rejection.
2. Tasks registration, hosted authorization, callback, and signed BFF session.
3. A newly registered user's empty Tasks state.
4. Project/task creation, filtering, updates, validation, CSRF rejection, and
   the completed-task transition invariant.
5. Cross-user project isolation.
6. Task/project deletion and the resulting empty state.
7. Logout rejection and subsequent existing-account login.
8. SaaS registration and dashboard callback.
9. Starter invitation-only onboarding, login, default workspace, additional
   workspace creation/rename, duplicate-slug conflict, and Identity directories
   without credential fields.
10. Workspace invitation and revocation.

Repeat native initialization preserved environment content and timestamps.
Repeat Docker setup preserved user/project/client counts and generated
credentials; environment files were developer-owned with restricted access.
The Docker registration page was also opened through the browser's Tasks Demo
link and rendered the branded registration form successfully. Other account
mutations were exercised with disposable HTTP fixtures, not a complete visual
browser walkthrough.

## Repository guard

`./scripts/ci pre-push` was run with Node 24 through the shared workspace's
`with-stack-paused` wrapper. It covers frozen installation, lint/typecheck,
web tests (6), audit-policy tests (3), production build, artifact dependency
audit, strict Composer validation/install/audit, Tasks API tests (7), Platform
API tests (20), secret/candidate scans, initializer help/syntax, Compose
configuration, and the Identity release contract.

Both Composer audits reported no security advisories after the dependency
updates. The web audit accepts two explicitly reviewed, expiring tooling
advisories and verifies that their packages are excluded from the production
artifact. This is a remaining development/build-tool risk; see
[the dependency policy](security/dependency-policy.md).

The release scanner also rejected disposable fixtures containing a local env
file, runtime log, and high-confidence secret pattern. This is a targeted
source check, not a guarantee that every possible secret format is detected.

## Boundaries and remaining publication gates

- The candidate is now recorded in five grouped local commits through
  `f5c7454`. The full repository guard passed on that committed tree with Node
  24 through the shared workspace wrapper. An earlier attempt with Node 25 failed
  because its `better-sqlite3` native addon was built for another ABI.
- At the time of this verification, the branch push had not completed because
  the workspace lacked a usable GitHub CLI binary and SSH agent access. The
  later publication update below supersedes that state.
- No production environment was changed. Temporary verification servers and
  containers were stopped; verification database/storage volumes were retained.
- The local stack logs mail and exposes development invitation/reset tokens;
  it is intended for evaluation. Production hosting, delivery, backup, and
  security configuration remain the adopting application's responsibility.
- The first Docker build encountered registry/font timeouts. A retry succeeded;
  fresh builds require access to the public registries, GitHub, and font service.

## Publication update

As of 2026-10-17T00:30:09Z, the grouped candidate was pushed to
`codex/guard-ci-before-push-starter` at `bf2728e` and PR #9 was opened against
`main`. The local push hook passed, including its secret/release scans, web
lint, typecheck, tests, build and audit, API tests/audits, initializer checks,
and Compose contract checks. GitHub Actions started for PR #9: secret scan,
local runtime, Platform API, API, and Identity release contract checks passed;
web and dependency-audit checks were still running. The PR is unmerged, and no
beta tag, Starter image publication, or GitHub release exists yet. Continue
with the gates in [the release checklist](releasing.md).
