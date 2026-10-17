import type { H3Event } from 'h3'
import type { TalredApiEnvelope, TalredApiFetchClient } from '@talred/api-client'
import type { PlatformIdentityApplicationDirectory, PlatformIdentityProjectDirectory, PlatformWorkspaceCollection, PlatformWorkspaceResponse } from '../../shared/types/platform'
import { createTalredApiClient } from '#server/utils/create-talred-api-client'
import { mapPlatformIdentityApplicationDirectory, mapPlatformIdentityProjectDirectory, mapPlatformWorkspaceCollection, mapPlatformWorkspaceResponse } from '#server/utils/platform.mapper'

type UpstreamBody = Record<string, unknown>

export function platformApi(event: H3Event): Promise<TalredApiFetchClient> {
  return createTalredApiClient(event, 'platform')
}

export function listPlatformWorkspaces(client: TalredApiFetchClient) {
  return client<TalredApiEnvelope<PlatformWorkspaceCollection>>('/api/platform/v1/workspaces')
    .then(mapPlatformWorkspaceCollection)
}

export function listPlatformIdentityProjects(client: TalredApiFetchClient) {
  return client<TalredApiEnvelope<PlatformIdentityProjectDirectory>>('/api/platform/v1/identity-projects')
    .then(mapPlatformIdentityProjectDirectory)
}

export function listPlatformIdentityApplications(client: TalredApiFetchClient) {
  return client<TalredApiEnvelope<PlatformIdentityApplicationDirectory>>('/api/platform/v1/identity-applications')
    .then(mapPlatformIdentityApplicationDirectory)
}

export function ensureDefaultPlatformWorkspace(client: TalredApiFetchClient) {
  return client<TalredApiEnvelope<PlatformWorkspaceResponse>>('/api/platform/v1/workspaces/default', { method: 'POST' })
    .then(mapPlatformWorkspaceResponse)
}

export function getPlatformWorkspace(client: TalredApiFetchClient, workspaceId: string) {
  return client<TalredApiEnvelope<PlatformWorkspaceResponse>>(`/api/platform/v1/workspaces/${workspaceId}`)
    .then(mapPlatformWorkspaceResponse)
}

export function createPlatformWorkspace(client: TalredApiFetchClient, body: UpstreamBody) {
  return client<TalredApiEnvelope<PlatformWorkspaceResponse>>('/api/platform/v1/workspaces', { method: 'POST', body })
    .then(mapPlatformWorkspaceResponse)
}
