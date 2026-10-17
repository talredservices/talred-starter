import assert from 'node:assert/strict'
import { mkdtempSync, mkdirSync, writeFileSync, rmSync } from 'node:fs'
import { tmpdir } from 'node:os'
import { join } from 'node:path'
import { test } from 'node:test'
import { checkAudit, checkRuntime, excludedPackages } from '../audit-web-dependencies.mjs'

const exception = { ghsa: 'GHSA-reviewed', package: 'braces', version: '3.0.3', via: 'micromatch', expires: '2026-11-15' }
const policy = { version: 1, exceptions: [exception] }
const report = () => ({
  metadata: { vulnerabilities: { high: 1 } },
  advisories: { 1: { module_name: 'braces', url: 'https://github.com/advisories/GHSA-reviewed', findings: [{ version: '3.0.3', paths: ['.>tooling>micromatch>braces'] }] } }
})

test('accepts only the reviewed tooling version and dependency path before expiry', () => {
  assert.deepEqual(checkAudit(report(), policy, '2026-10-16'), ['GHSA-reviewed'])
  const changed = report()
  changed.advisories[1].findings[0].paths = ['.>application>braces']
  assert.throws(() => checkAudit(changed, policy, '2026-10-16'), /outside reviewed tooling/)
  assert.throws(() => checkAudit(report(), policy, '2026-11-16'), /expired/)
})

test('rejects new advisories, changed versions, and incomplete registry responses', () => {
  const unknown = report()
  unknown.advisories[1].url = 'https://github.com/advisories/GHSA-new'
  assert.throws(() => checkAudit(unknown, policy, '2026-10-16'), /Unreviewed/)
  const changed = report()
  changed.advisories[1].findings[0].version = '3.0.2'
  assert.throws(() => checkAudit(changed, policy, '2026-10-16'), /installed version/)
  assert.throws(() => checkAudit({ error: 'offline' }, policy), /Incomplete/)
})

test('fails when a vulnerable package is traced into the production artifact', () => {
  const root = mkdtempSync(join(tmpdir(), 'starter-runtime-audit-'))
  try {
    writeFileSync(join(root, 'starter-runtime-dependencies.json'), JSON.stringify({ version: 1, moduleCount: 1, excludedPackages }))
    writeFileSync(join(root, 'package.json'), JSON.stringify({ dependencies: {} }))
    checkRuntime(root)
    mkdirSync(join(root, 'node_modules/ipx'), { recursive: true })
    writeFileSync(join(root, 'node_modules/ipx/package.json'), JSON.stringify({ name: 'ipx', dependencies: { listhen: '1.10.1' } }))
    checkRuntime(root) // A pruned upstream manifest can still list its unused CLI dependency.
    writeFileSync(join(root, 'node_modules/ipx/index.mjs'), "import forge from 'node-forge';")
    assert.throws(() => checkRuntime(root), /tooling import/)
    rmSync(join(root, 'node_modules/ipx/index.mjs'))
    mkdirSync(join(root, 'node_modules/braces'), { recursive: true })
    writeFileSync(join(root, 'node_modules/braces/package.json'), JSON.stringify({ name: 'braces' }))
    assert.throws(() => checkRuntime(root), /shipped in production/)
  }
  finally { rmSync(root, { recursive: true, force: true }) }
})
