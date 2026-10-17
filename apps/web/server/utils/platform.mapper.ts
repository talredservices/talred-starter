import type { TalredApiEnvelope } from '@talred/api-client'
import type { PlatformIdentityApplication, PlatformIdentityApplicationDirectory, PlatformIdentityProjectDirectory, PlatformWorkspace, PlatformWorkspaceCollection, PlatformWorkspaceResponse } from '../../shared/types/platform'

type ResponsePayload<T extends object> = TalredApiEnvelope<T> | T | { response?: T, data?: T }

function responseData<T extends object>(payload: ResponsePayload<T>): T {
  if ('data' in payload && payload.data !== undefined) return payload.data
  if ('response' in payload && payload.response !== undefined) return payload.response
  return payload as T
}

function mapWorkspace(workspace: PlatformWorkspace): PlatformWorkspace {
  return { ...workspace, member_count: Number(workspace.member_count ?? 0) }
}

export function mapPlatformWorkspaceCollection(envelope: ResponsePayload<PlatformWorkspaceCollection>): PlatformWorkspaceCollection {
  return { workspaces: responseData(envelope).workspaces.map(mapWorkspace) }
}

export function mapPlatformWorkspaceResponse(envelope: ResponsePayload<PlatformWorkspaceResponse>): PlatformWorkspaceResponse {
  return { workspace: mapWorkspace(responseData(envelope).workspace) }
}

export function mapPlatformIdentityProjectDirectory(envelope: ResponsePayload<PlatformIdentityProjectDirectory>): PlatformIdentityProjectDirectory {
  const data = responseData(envelope)
  const availability = ['available', 'access_needed', 'unavailable'].includes(data.availability)
    ? data.availability
    : 'unavailable'

  return {
    availability,
    projects: Array.isArray(data.projects)
      ? data.projects.filter(project => project && typeof project.id === 'string' && project.id.length > 0)
      : []
  }
}

export function mapPlatformIdentityApplicationDirectory(envelope: ResponsePayload<PlatformIdentityApplicationDirectory>): PlatformIdentityApplicationDirectory {
  const data = responseData(envelope)
  const availability = ['available', 'access_needed', 'unavailable'].includes(data.availability)
    ? data.availability
    : 'unavailable'

  return {
    availability,
    applications: Array.isArray(data.applications)
      ? data.applications.filter((application): application is PlatformIdentityApplication => (
          application
          && typeof application.id === 'string'
          && application.id.length > 0
          && typeof application.project?.id === 'string'
          && application.project.id.length > 0
        )).map(application => ({
          id: application.id,
          name: typeof application.name === 'string' ? application.name : 'Application',
          key: typeof application.key === 'string' ? application.key : '',
          status: typeof application.status === 'string' ? application.status : 'unknown',
          application_url: typeof application.application_url === 'string' ? application.application_url : null,
          project: {
            id: application.project.id,
            name: typeof application.project.name === 'string' ? application.project.name : 'Identity Project',
            slug: typeof application.project.slug === 'string' ? application.project.slug : '',
            status: typeof application.project.status === 'string' ? application.project.status : 'unknown',
            mode: typeof application.project.mode === 'string' ? application.project.mode : 'unknown'
          }
        }))
      : []
  }
}
