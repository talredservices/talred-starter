# Dependency audit policy

CI runs `pnpm run audit` after the production build. The command fetches a fresh
`pnpm audit --prod --json` report and fails on registry errors, incomplete
responses, new advisories, changed dependency versions or paths, and expired
exceptions. It keeps the raw report in ignored `apps/web/audit-report.json`.
PHP applications continue to use Composer's strict audit without exceptions.

## Temporary tooling exceptions

As reviewed on 2026-10-16, the following upstream packages have advisories and
no published patched release. These remain risks in development/build tooling;
they are not fixed by this policy.

| Package | Advisory | Starter use | Review deadline |
| --- | --- | --- | --- |
| `node-forge@1.4.0` | [GHSA-86w9-cpqp-85rv](https://github.com/advisories/GHSA-86w9-cpqp-85rv) | Transitive `listhen` dependency for local HTTPS certificate generation. Starter does not use Forge signature verification. | 2026-11-15 |
| `braces@3.0.3` | [GHSA-vfj7-8cjw-p6xm](https://github.com/advisories/GHSA-vfj7-8cjw-p6xm) | `micromatch` in Nuxt Content/i18n/Nitro build tooling processes source-owned glob patterns. | 2026-11-15 |

The machine-readable policy is `tooling-advisories.json` in this directory.
Only the exact advisory, package version, and reviewed dependency path are
accepted. Do not pass untrusted glob patterns to build tooling, expose the
Nuxt development server publicly, or use its certificate generator as an
application cryptographic API.

## Enforced production boundary

Starter's Nitro Rollup plugin rejects `braces`, `listhen`, and `node-forge` in
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
