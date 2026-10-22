# CI failure prevention

## Reproducible web installation

Use Node 24 and the pnpm version declared in `apps/web/package.json`. A native
`better-sqlite3` addon installed under a different Node ABI can fail the content
build even when installation succeeds. A clean clone or Docker build must
install its own dependencies. Keep pnpm patch hashes synchronized with their
patch files and verify `pnpm install --frozen-lockfile`; do not disable frozen
installation to hide a mismatch.

## Dependency and artifact checks

Run the build before `pnpm run audit`. The audit needs the production module
graph evidence and fails without it. The two temporary tooling findings are
governed by [the dependency policy](security/dependency-policy.md); they may not
be converted into blanket audit suppression. Security workflow failures must
propagate instead of being hidden after uploading a report. Registry outages
are incomplete verification, not successful security checks.

On 2026-10-22, the Starter dependency audit found Nuxt GHSA-vjh5-4x84-xrqp in
the Nuxt 4.6.0 lockfile. Nuxt 4.6.2 fixes case-insensitive matching for
server-side route rules. Keep `nuxt` and the resolved lockfile at 4.6.2 or
newer; do not approve this runtime advisory as a tooling exception.

Run the Tasks API Composer audit against its lockfile. The beta audit found advisories affecting Laravel,
CommonMark, Flysystem, HttpFoundation, and YAML. Both locks were updated to
patched compatible releases. Keep optional development tooling updates out of
mandatory Composer hooks: `boost:update` was unavailable in this installation
and made an otherwise successful dependency update exit with an error.

## Public source and image context

Generated Laravel cache, compiled views, SQLite databases, environment files,
credentials, and dependency directories must not enter Git or Docker build
contexts. Root ignore patterns must cover nested applications. Check a
candidate created from the working tree, including newly added source files,
before release; scanning only HEAD does not cover untracked candidate files.

## Setup verification

Use independent temporary directories, database files, Compose project names,
and ports when checking a fresh setup. Existing local `.env` files and
initialized databases can hide missing setup steps. Never reset an existing
developer's volumes or installation to simulate a fresh clone.

Laravel's native development server reloads after `.env` changes. The
initializer waits for `/up` and retries only its authentication request on
connection or gateway failures for a bounded period. Provisioning mutations
and authentication/validation rejections are never retried automatically.

Identity management responses omit existing client secrets. Reuse a saved
secret only when its saved client ID matches the provisioned client. Rotate
only when that matching local secret is unavailable; rotating every client
on each setup run invalidates running consumers and triggers needless reloads.
Verify repeat initialization preserves generated environment content.

The production Console uses Secure cookies. Browsers treat `localhost` as a
trustworthy origin, whereas common HTTP test clients reject Secure cookies on
plain HTTP. Match browser localhost behavior explicitly in a disposable test
harness; do not weaken production cookie settings to make such a test pass.
