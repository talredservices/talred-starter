import { describe, expect, it } from 'vitest'
import { isPlatformOrganizationRouteAllowed } from '../server/utils/platform-organization-routes'

const workspaceId = '94481d36-3107-4078-9421-b9a2de3dbcc4'
const projectId = '01a122d1-dc35-73cb-9395-ea3f9032e58f'

describe('Platform organization BFF route contract', () => {
  it('allows Workspace, resource-section, invitation, and Identity-link operations', () => {
    expect(isPlatformOrganizationRouteAllowed(`workspaces/${workspaceId}/overview`, 'GET')).toBe(true)
    expect(isPlatformOrganizationRouteAllowed(`workspaces/${workspaceId}`, 'GET')).toBe(true)
    expect(isPlatformOrganizationRouteAllowed(`workspaces/${workspaceId}/members/${projectId}`, 'DELETE')).toBe(true)
    expect(isPlatformOrganizationRouteAllowed(`workspaces/${workspaceId}/invitations`, 'POST')).toBe(true)
    expect(isPlatformOrganizationRouteAllowed('invitations/accept', 'POST')).toBe(true)
    expect(isPlatformOrganizationRouteAllowed(`workspaces/${workspaceId}/resources/identity-projects`, 'GET')).toBe(true)
    expect(isPlatformOrganizationRouteAllowed(`workspaces/${workspaceId}/resources/identity-projects`, 'POST')).toBe(true)
    expect(isPlatformOrganizationRouteAllowed(`workspaces/${workspaceId}/resources/identity-projects/available`, 'GET')).toBe(true)
    expect(isPlatformOrganizationRouteAllowed(`workspaces/${workspaceId}/resources/identity-projects/${projectId}`, 'DELETE')).toBe(true)
  })

  it('rejects unsupported methods, malformed identifiers, and arbitrary upstream paths', () => {
    expect(isPlatformOrganizationRouteAllowed('workspaces/not-a-uuid/overview', 'GET')).toBe(false)
    expect(isPlatformOrganizationRouteAllowed('teams', 'GET')).toBe(false)
    expect(isPlatformOrganizationRouteAllowed('../identity/projects', 'GET')).toBe(false)
    expect(isPlatformOrganizationRouteAllowed(`projects/${projectId}/identity-links`, 'POST')).toBe(false)
    expect(isPlatformOrganizationRouteAllowed(`workspaces/${workspaceId}/projects`, 'GET')).toBe(false)
  })
})
