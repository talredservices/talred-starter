#!/usr/bin/env node
import assert from 'node:assert/strict'
import { spawnSync } from 'node:child_process'
import { readFileSync, readdirSync, realpathSync, statSync, writeFileSync } from 'node:fs'
import { dirname, join, resolve } from 'node:path'
import { fileURLToPath } from 'node:url'

export const excludedPackages = ['braces', 'deepmerge', 'glob', 'listhen', 'minimatch', 'node-forge']

export function checkAudit(report, policy, today = new Date().toISOString().slice(0, 10)) {
  assert.equal(policy.version, 1, 'Unsupported dependency exception policy')
  assert.ok(report.metadata?.vulnerabilities && report.advisories && !report.error, 'Incomplete registry audit response')
  const accepted = []
  for (const advisory of Object.values(report.advisories)) {
    const exceptions = policy.exceptions.filter(item => advisory.url === `https://github.com/advisories/${item.ghsa}`)
    assert.ok(exceptions.length, `Unreviewed dependency advisory: ${advisory.url}`)
    const packageExceptions = exceptions.filter(item => item.package === advisory.module_name)
    assert.ok(packageExceptions.length, 'Advisory package changed')
    assert.ok(advisory.findings?.length, 'Audit finding has no dependency paths')
    for (const finding of advisory.findings) {
      assert.ok(finding.paths?.length, 'Audit finding has no dependency paths')
      const exception = packageExceptions.find(item => {
        const suffixes = item.pathSuffixes ?? [`>${item.via}>${item.package}`]
        return finding.paths.every(path => suffixes.some(suffix => path.endsWith(suffix)))
      })
      assert.ok(exception, 'Vulnerable dependency is used outside reviewed tooling')
      const versions = exception.versions ?? [exception.version]
      assert.ok(versions.includes(finding.version), 'Exception does not cover this installed version')
      assert.ok(today <= exception.expires, `Dependency exception expired: ${exception.ghsa}`)
    }
    accepted.push(exceptions[0].ghsa)
  }
  const reported = Object.values(report.metadata.vulnerabilities).reduce((sum, count) => sum + count, 0)
  assert.equal(reported, Object.keys(report.advisories).length, 'Audit totals do not match the advisory report')
  return [...new Set(accepted)]
}

export function checkRuntime(serverRoot) {
  const evidence = JSON.parse(readFileSync(join(serverRoot, 'starter-runtime-dependencies.json'), 'utf8'))
  assert.equal(evidence.version, 1, 'Missing production module graph evidence')
  assert.ok(evidence.moduleCount > 0, 'Production module graph is empty')
  assert.deepEqual(evidence.excludedPackages, excludedPackages, 'Production exclusion policy differs from the audit policy')
  const visited = new Set()
  const packageNames = excludedPackages.join('|')
  const importPattern = new RegExp(`(?:from\\s*|import\\s*\\(|require\\s*\\()\\s*['"](?:${packageNames})(?:/|['"])`)
  const scan = (directory) => {
    const real = realpathSync(directory)
    assert.ok(real === serverRoot || real.startsWith(`${serverRoot}/`), 'Runtime dependency escapes the production artifact')
    if (visited.has(real)) return
    visited.add(real)
    for (const entry of readdirSync(directory, { withFileTypes: true })) {
      const path = join(directory, entry.name)
      if (entry.isDirectory() || (entry.isSymbolicLink() && statSync(path).isDirectory())) scan(path)
      else if (entry.name === 'package.json') {
        const pkg = JSON.parse(readFileSync(path, 'utf8'))
        assert.ok(!excludedPackages.includes(pkg.name), `Excluded package shipped in production: ${pkg.name}`)
        if (directory === serverRoot) {
          for (const name of Object.keys(pkg.dependencies ?? {})) {
            assert.ok(!excludedPackages.includes(name), `Production manifest depends on excluded package: ${name}`)
          }
        }
      }
      else if (/\.(?:mjs|cjs|js)$/.test(entry.name)) {
        const code = readFileSync(path, 'utf8')
        assert.ok(!importPattern.test(code), `Excluded tooling import in production: ${path}`)
      }
    }
  }
  scan(serverRoot)
}

if (process.argv[1] && resolve(process.argv[1]) === fileURLToPath(import.meta.url)) {
  try {
    const root = resolve(dirname(fileURLToPath(import.meta.url)), '..')
    const web = join(root, 'apps/web')
    checkRuntime(realpathSync(join(web, '.output/server')))
    const result = spawnSync('pnpm', ['audit', '--prod', '--json'], { cwd: web, encoding: 'utf8', maxBuffer: 16 * 1024 * 1024 })
    assert.ok(!result.error && [0, 1].includes(result.status), result.stderr || result.error?.message || 'Dependency audit command failed')
    const report = JSON.parse(result.stdout)
    writeFileSync(join(web, 'audit-report.json'), JSON.stringify(report, null, 2) + '\n')
    const policy = JSON.parse(readFileSync(join(root, 'docs/security/tooling-advisories.json'), 'utf8'))
    const accepted = checkAudit(report, policy)
    console.log(`Dependency audit passed: ${accepted.length} reviewed tooling advisories, excluded from production. See docs/security/dependency-policy.md.`)
  }
  catch (error) {
    console.error(`Dependency audit failed: ${error.message}`)
    process.exitCode = 1
  }
}
