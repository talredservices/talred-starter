# Developer setup

Talred Starter has two isolated local workflows. Use the standalone Docker
stack from a public clone, or the shared workspace Compose stack to develop
alongside the canonical `identity` source and `local-dev-ops` project.

## Run a public clone with Docker

Prerequisites: Docker Engine, Docker Compose 2.17 or later, BuildKit support,
and internet access to pull the published Identity images and fetch the pinned
public Identity source for the Console build.

From the repository root, run:

```bash
./docker/up.sh
./docker/credentials.sh
```

The setup creates a mode-restricted, ignored root `.env`, generates local
database and session secrets, pulls the content-addressed public Identity
Server and Nginx images, and builds the Tasks API, Nuxt app, and Starter-specific
Identity Console. The Console build fetches the matching public Identity
commit and compiles the bundled Starter, Fresh SaaS, and Tasks auth page layers
into that image. No Identity source checkout or sibling workspace repository
is needed on the host.

The stack migrates its isolated databases, bootstraps an Identity Console
owner, and provisions the Starter, Fresh SaaS, and Tasks projects through the
Identity management API. It does not seed application users. Fresh SaaS and
Tasks allow registration with their configured Member role. Starter is
invite-only; invite its users through the Identity Console. Development
invitation tokens are enabled only in this loopback-only local stack.

Open the Starter at `http://localhost:3300`, the Identity Console at
`http://localhost:3201/admin/sign-in`, the Identity API at
`http://localhost:8201`, the Tasks API at `http://localhost:8300`. `./docker/credentials.sh` displays
the generated Identity owner credentials locally. Keep the root `.env` private;
it contains passwords, database credentials, client secrets, and session keys.

Stop the stack with `./docker/down.sh`. It preserves the named database and
storage volumes and the generated `.env`. If a port is already in use, change
the relevant port in `.env` and rerun `./docker/up.sh`.

## Run a fresh public clone without Docker

The Starter consumes the public Identity source in native mode. Clone both
repositories side by side; the Identity repository remains independent and
owns its API, Console, and dependencies.

Requirements: Git, PHP 8.3 with the extensions required by Laravel/SQLite,
Composer 2, Node.js 24.11 or later in the Node 24 LTS line, and pnpm 11.21. This walkthrough uses Linux, macOS,
or WSL with Bash.

```bash
mkdir talred-local
cd talred-local
git clone https://github.com/talredservices/identity.git
git clone https://github.com/talredservices/talred-starter.git
# Use the same public Identity revision as this Starter beta.
git -C identity checkout --detach 0fb11ef1b570b88dd6f63cdc8d04d0e305f6a89d
```

From the directory that contains `talred-local`, prepare Identity's Laravel API
and start it in one terminal:

```bash
cd talred-local/identity/apps/identity-server
composer install
cp .env.example .env
php artisan key:generate
touch database/database.sqlite
php artisan migrate
php artisan serve --host=127.0.0.1 --port=8100
```

From the directory that contains `talred-local`, prepare the Tasks API in
another terminal. It uses its own SQLite database and application key:

```bash
cd talred-local/talred-starter/apps/api
composer install
cp .env.example .env
php artisan key:generate
touch database/database.sqlite
php artisan migrate

```

Install the Nuxt dependencies and initialize the local Identity projects. The
initializer prompts for a local Identity installation owner and password
when creating a new installation. If the database already has an owner but its
password is not saved in local configuration, it prompts for that existing
owner password without echoing it. It bootstraps the Console client in the
Identity source process, provisions the Starter, Fresh SaaS, and Tasks
projects, and writes generated credentials to ignored mode-600 environment
files. It also configures Identity to compile the Starter-owned hosted-auth
pages from the sibling Starter checkout.


From the directory that contains `talred-local`, run:

```bash
cd talred-local/identity
pnpm install --frozen-lockfile
cd ../talred-starter/apps/web
pnpm install --frozen-lockfile
cd ../..
./scripts/init-stack --host
```

The command expects Identity at `../identity`. If you keep it elsewhere, set
`ZOLTA_IDENTITY_SOURCE_ROOT` to its absolute path. Host mode uses ports 8100
(Identity API), 3100 (Identity Console), 8300 (Tasks API), and 3300 (Starter web).
Override the URLs with `ZOLTA_IDENTITY_API_URL`, `ZOLTA_IDENTITY_CONSOLE_URL`,
`ZOLTA_STARTER_API_URL`, or `ZOLTA_STARTER_SITE_URL` before initialization if needed.

Restart Identity's API process after initialization so it loads the generated
hosted-application token and page-set allowlist. Run the four remaining
servers in separate terminals, each starting from `talred-local`:

```bash
# Identity Console, from the identity repository root
cd talred-local/identity && pnpm exec nuxt dev --host 127.0.0.1 --port 3100

# Tasks API
cd talred-local/talred-starter/apps/api && php artisan serve --host=127.0.0.1 --port=8300

# Starter web and BFF
cd talred-local/talred-starter/apps/web && pnpm exec nuxt dev --host 127.0.0.1 --port 3300
```

Open `http://localhost:3300`. Fresh SaaS and Tasks allow registration; Starter
is invite-only, so invite a Starter account from the Identity Console. The
initializer refuses non-local Identity URLs and creates no application or
agent account. It does not start, stop, or modify any Docker containers.

## Run inside the TalredServices workspace

The shared workspace runtime combines this repository with canonical
`identity` source and the `local-dev-ops` Compose stack. Keep those
repositories as siblings in the workspace so the shared Compose build
contexts resolve.

## Start the examples

From `local-dev-ops/local`, create the ignored environment file on first use,
then start only the Starter services and their declared dependencies:

```bash
test -f .env || cp .env.example .env
docker compose up -d --build --wait --wait-timeout 180 \
  starter-identity-console starter-api zolta-starter-web
```

Compose initializes the isolated Identity database and provisions the Starter,
Fresh SaaS, and Tasks Identity projects, clients, roles, and hosted applications
through `starter-identity-init` before the Identity API starts. This uses the
configured Identity runtime secrets and Starter-owned hosted-auth configuration.
For the generated local owner credentials, use
`local-dev-ops/local/init/runtime/show-starter-identity-credentials.sh` from the
workspace root.

`./scripts/init-stack --host` is for the separate host-run Identity workflow. Do not
run it after starting the Compose stack; it writes host `.env.starter` files and
can contain credentials from another local Identity instance.

Open the Nuxt app at `http://localhost:3300`. The Starter Identity Console is
at `http://localhost:3201`; the Tasks API is at `http://localhost:8300`. The web
container mounts `apps/web` for Nuxt hot reload. Rebuild the Tasks API
after changing its source:

```bash
cd local-dev-ops/local
docker compose up -d --build --force-recreate --no-deps starter-api
```

The full local stack setup, environment ownership, and operational safeguards
are documented in [`local-dev-ops/local/README.md`](../../local-dev-ops/local/README.md).

## How the examples fit together

| Example | Source | Request path |
| --- | --- | --- |
| Tasks | `apps/web/layers/projects`, `apps/api` | Nuxt page → Projects BFF → Laravel Tasks API |
| Fresh SaaS | `apps/web/layers/saas` and its separate Identity application | Nuxt experience → its BFF and Identity application |
| Identity Console and authentication | Canonical `identity` sibling project | Identity Console/BFF → Identity API |

Identity owns Identity Projects, Clients, Applications, authentication, and
each project's details. Identity lifecycle operations remain in Identity
Console.

When adding a feature, keep its business rules in the API that owns them. Add
browser-facing server routes in the Nuxt BFF, keep credentials server-side,
and update the shared contract and tests at each boundary. See
[`full-stack-contracts.md`](full-stack-contracts.md),
[`api/index.md`](api/index.md), and [`client/index.md`](client/index.md).

## Verify changes

The web unit suite can run while the local stack is up:

```bash
cd apps/web
pnpm install --frozen-lockfile
pnpm test
pnpm lint:js
```

Typecheck, build, and full CI compile the application and can use significant
memory. In the shared TalredServices workspace, run them through the wrapper
below so it stops and restores the same running Compose services. For a
standalone public clone, run `pnpm lint:types`, `pnpm build`, and
`./scripts/ci pre-push` directly from their respective app/repository roots.

```bash
cd apps/web
../../../local-dev-ops/scripts/with-stack-paused -- pnpm lint:types
../../../local-dev-ops/scripts/with-stack-paused -- pnpm build
```

The complete Starter CI command validates the web app and Tasks API,
Identity release metadata, and tracked-secret policy:

```bash
cd talred-starter
../local-dev-ops/scripts/with-stack-paused -- ./scripts/ci fast
```

The Laravel test suites use in-memory SQLite. Host PHP needs the versions and
extensions required by the CI workflow; the CI script reports missing SQLite
or GD extensions before running those suites.

The web dependency check is `pnpm run audit` after `pnpm build`; it enforces
[the dependency policy](security/dependency-policy.md) and production artifact
exclusion. Raw `pnpm audit --prod` still reports the documented tooling findings.

## Prepare a beta release

Use the [beta release checklist](releasing.md) for the clean-clone, CI, Identity
image pin, and GitHub Container Registry steps. A local successful build is not
evidence that a release image was published or that its package is public.
