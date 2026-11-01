# Dependency audit policy

CI runs `pnpm run audit` after the production build. The command fetches a fresh
`pnpm audit --prod --json` report and fails on registry errors, incomplete
responses, new advisories, changed dependency versions or paths, and expired
exceptions. It keeps the raw report in ignored `apps/web/audit-report.json`.
PHP applications continue to use Composer's strict audit without exceptions.

## Temporary tooling exceptions

The following advisories are limited to development and build tooling; the
policy does not fix the underlying packages. `deepmerge` remains at 4.3.1
because the configured npm registry does not publish the advisory's listed
4.3.2 fix. The current `glob` and `minimatch` tool dependencies use older major
versions than their published fixes. `braces` remains covered by the existing
reviewed exception.

| Package | Advisory | Starter use | Review deadline |
| --- | --- | --- | --- |
| `braces@3.0.3` | [GHSA-vfj7-8cjw-p6xm](https://github.com/advisories/GHSA-vfj7-8cjw-p6xm) | `micromatch` in Nuxt Content/i18n/Nitro build tooling processes source-owned glob patterns. | 2026-11-15 |
| `deepmerge@4.3.1` | [GHSA-gcqx-w48m-w2h4](https://github.com/advisories/GHSA-gcqx-w48m-w2h4) | Nitro's Rollup resolver uses this during the build. The configured npm registry currently reports 4.3.1 as the latest published version. It is excluded from the runtime artifact. | 2026-11-15 |
| `glob@10.5.0`, `glob@13.0.6` | [GHSA-3g3m-2fjr-6pw4](https://github.com/advisories/GHSA-3g3m-2fjr-6pw4) | Nitro's file tracer and archiver use these during build; the patched release is in the `glob@14` line. Both versions are excluded from the runtime artifact. | 2026-11-15 |
| `minimatch@5.1.9`, `minimatch@9.0.9`, `minimatch@10.2.6` | [GHSA-g3hv-75wg-m249](https://github.com/advisories/GHSA-g3hv-75wg-m249) | Content matching, ESLint, file tracing, and archive tooling use these during build; the patched release is in the `minimatch@11` line. All versions are excluded from the runtime artifact. | 2026-11-15 |

The machine-readable policy is `tooling-advisories.json` in this directory.
Only the exact advisory, package versions, and reviewed dependency path suffixes are
accepted. Do not pass untrusted glob patterns to build tooling or expose the
Nuxt development server publicly. `node-forge` is pinned to the patched 1.4.1
release and remains excluded from the application runtime.

## Enforced production boundary

Starter's Nitro Rollup plugin rejects `braces`, `deepmerge`, `glob`, `listhen`,
`minimatch`, and `node-forge` in
bundled modules or external chunk imports. It emits
`.output/server/starter-runtime-dependencies.json` as evidence of the checked
module graph. The audit command requires that evidence and scans the traced
runtime packages, executable imports, and the root runtime manifest for the
same packages. A pruned dependency's upstream manifest may still list an
unused CLI dependency (for example IPX lists `listhen`); that declaration
alone is not executable code in the artifact. Missing evidence
or inclusion of an excluded package fails CI even if its advisory is listed.
The release Dockerfile copies only `.output`, not the development dependency
tree. Run the application with that artifact for deployment; `pnpm dev` is a
development server.

Remove each exception as soon as an upstream fix is published and verified.
An expired exception requires a fresh exposure review; extending a date alone
does not establish safety. New application use of either library requires
removing the exception and resolving its vulnerability before release.
