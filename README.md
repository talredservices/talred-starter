# Talred Starter

Talred Starter is an open-source reference for building understandable PHP services. It demonstrates Clean Architecture, domain-driven design, and CQRS with `talred/php` and Laravel, connected to Nuxt clients through clear BFF/API contracts and the independently owned Talred Identity service.

The public brand is Talred. PHP technical namespaces and existing Identity application keys retain their established compatibility names; the Nuxt integrations use the published `@talred/*` packages.

The repository includes a deliberately empty Starter account dashboard, Fresh SaaS, and Tasks examples. Follow a request from Nuxt through its BFF to the API and domain layers, with authentication and each application's business data kept in their owning services. The code and documentation aim to be clear for developers and AI agents to navigate.

## First beta

`0.1.0-beta.1` is the first release candidate for evaluation and adaptation.
See [the changelog](CHANGELOG.md) for scope and limitations and
[the release checklist](docs/releasing.md) for publication gates. Temporary
build-tool dependency advisories are documented in
[the dependency policy](docs/security/dependency-policy.md).
Local native/Docker results and their limits are recorded in
[the beta verification report](docs/beta-verification.md).

## Composition

- `apps/web` — the Nuxt 4 client and BFF, with the empty Starter dashboard, Fresh SaaS, and Tasks examples.
- `apps/api` — the Laravel 13 Tasks API, demonstrating `talred/php`, Clean Architecture, and CQRS.
- `identity-release.json` — the external Talred Identity image contract.
- `runtime/identity` — local configuration for the isolated Identity instance; no implementation source.

Identity remains authoritative for Identity Projects, Clients, Applications, authentication, and authorization. The Starter Home intentionally has no workspace or Identity directory data; the standalone Tasks example continues to use its own Nuxt BFF and Laravel API.

## Quick start

### Standalone Docker stack

With Docker Engine, Docker Compose 2.17+, and internet access, start the stack from a fresh clone:

```bash
git clone https://github.com/talredservices/talred-starter.git
cd talred-starter
./docker/up.sh
./docker/credentials.sh
```

Open the Starter at `http://localhost:3300` and the Identity Console at `http://localhost:3201/admin/sign-in`. The setup generates credentials in the ignored root `.env`; `credentials.sh` displays only the local Identity Console owner credentials. Stop with `./docker/down.sh`; named database and storage volumes are preserved.

The stack pulls the content-addressed public Identity Server and Nginx images. It builds a Starter-specific Console from the matching public Identity source commit with Starter, SaaS, and Tasks auth page layers compiled in. Its MySQL, Redis, Identity storage, and application databases are isolated from other local stacks. No Identity source checkout or sibling workspace repository is needed on the host.

The startup script pulls those public images with an isolated temporary Docker
config, so a saved GHCR login cannot interfere with anonymous pulls and is left
unchanged.

If a default port is already in use, change the port values in the ignored root `.env` created by `./docker/up.sh`, then rerun the setup.

For the shared workspace runtime and verification commands, follow the [developer setup guide](docs/developer-setup.md). It uses the canonical `identity` and `local-dev-ops` sibling projects.

### Native PHP and Node servers

Clone the public `identity` repository alongside `talred-starter`, prepare the
Identity and Tasks Laravel apps with their Nuxt clients, and run `./scripts/init-stack --host`.
The [native setup guide](docs/developer-setup.md#run-a-fresh-public-clone-without-docker)
lists the requirements and every command. Docker and `local-dev-ops` are not
required for this path.

For the optional shared workspace image overlay, see
[workspace runtime](docs/workspace-runtime.md).

## Licensing

The root starter and its web/API applications are licensed under Apache-2.0. External Identity releases retain their own repository license. Copyright and licensing ownership for original repository content remains with Redouane Taleb. Third-party dependencies retain their own licenses, and their terms are not changed by this repository.

Talred is the public brand for this starter. Brand and trademark ownership remain with Redouane Taleb; see [NOTICE](NOTICE) for repository attribution.

## Identity source ownership

`identity` owns the generic Console, Server, authentication framework, dependencies, CI, and Identity image publication. Starter owns its web/API, Identity project provisioning, hosted-auth presentation configuration, and optional page-set source in `integrations/identity-hosted-auth/`. Starter does not publish competing Identity images. Its Docker setup builds a local Console from the pinned public Identity source and compiles the Starter-owned page layers into it; the public Server and Nginx images remain shared. The Console and API page-set allowlists enable `default,starter,saas,projects`.

### Initialize local Identity

#### Docker Compose

The Docker Compose workflow initializes its isolated Identity database and
provisions Starter, Fresh SaaS, and Tasks projects and hosted applications with
the `starter-identity-init` service. It does not create application user
accounts; use each app's registration policy or invite people through Identity.
For a public clone, run `./docker/credentials.sh` from the repository root to
display the generated local owner credentials. In the shared workspace runtime, use
`local-dev-ops/local/init/runtime/show-starter-identity-credentials.sh` from
the workspace root.

#### Host-run services

For a fresh public clone without Docker, clone the independent public Identity
repository alongside Starter, start its Laravel API and Nuxt Console from
source, prepare the two Starter Laravel apps, then run
`./scripts/init-stack --host`. The setup instructions include the required
Identity auth-layer path, native bootstrap, default ports, and per-terminal
server commands in [developer setup](docs/developer-setup.md). The initializer
uses Identity's installed Artisan application directly; it does not need
Docker or the `local-dev-ops` repository in this mode.

During initialization, enter the local Identity owner password when prompted.
For an already bootstrapped Identity installation, this is the existing
Console owner password; the terminal hides the input as you type. The
initializer provisions Starter's Identity clients and hosted applications and
writes their generated credentials to ignored local environment files. Never
put the owner password or generated credentials in source control or chat.

Do not point this local-only initializer at a shared or production Identity
installation. It writes generated credentials to ignored, mode-restricted
`.env` files and creates no application or agent user. Fresh SaaS and Tasks
allow public registration; Starter remains invite-only.

## Codex skills

Talred-specific Codex skills and their workspace installer are documented in [Codex skill management](docs/codex-skill-management.md).
