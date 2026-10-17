#!/usr/bin/env node
import assert from 'node:assert/strict'
import { spawnSync } from 'node:child_process'
import { readFileSync, readdirSync, realpathSync, statSync, writeFileSync } from 'node:fs'
import { dirname, join, resolve } from 'node:path'
import { fileURLToPath } from 'node:url'

export const excludedPackages = ['braces', 'listhen', 'node-forge']

export function checkAudit(report, policy, today = new Date().toISOString().slice(0, 10)) {
  assert.equal(policy.version, 1, 'Unsupported dependency exception policy')
  assert.ok(report.metadata?.vulnerabilities && report.advisories && !report.error, 'Incomplete registry audit response')
  const accepted = []
  for (const advisory of Object.values(report.advisories)) {
    const exception = policy.exceptions.find(item => advisory.url === `https://github.com/advisories/${item.ghsa}`)
    assert.ok(exception, `Unreviewed dependency advisory: ${advisory.url}`)
    assert.ok(today <= exception.expires, `Dependency exception expired: ${exception.ghsa}`)
    assert.equal(advisory.module_name, exception.package, 'Advisory package changed')
    assert.ok(advisory.findings?.length, 'Audit finding has no dependency paths')
    for (const finding of advisory.findings) {
      assert.equal(finding.version, exception.version, 'Exception does not cover this installed version')
      assert.ok(finding.paths?.length, 'Audit finding has no dependency paths')
      assert.ok(finding.paths.every(path => path.endsWith(`>${exception.via}>${exception.package}`)), 'Vulnerable dependency is used outside reviewed tooling')
    }
    accepted.push(exception.ghsa)
  }
  const reported = Object.values(report.metadata.vulnerabilities).reduce((sum, count) => sum + count, 0)
  assert.equal(reported, Object.keys(report.advisories).length, 'Audit totals do not match the advisory report')
  return accepted
}

export function checkRuntime(serverRoot) {
  const evidence = JSON.parse(readFileSync(join(serverRoot, 'starter-runtime-dependencies.json'), 'utf8'))
  assert.equal(evidence.version, 1, 'Missing production module graph evidence')
  assert.ok(evidence.moduleCount > 0, 'Production module graph is empty')
  assert.deepEqual(evidence.excludedPackages, excludedPackages, 'Production exclusion policy differs from the audit policy')
  const visited = new Set()
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
        assert.ok(!/(?:from\s*|import\s*\(|require\s*\()\s*['"](?:braces|listhen|node-forge)(?:\/|['"])/.test(code), `Excluded tooling import in production: ${path}`)
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
