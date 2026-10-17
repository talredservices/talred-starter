import type {
  PlatformIdentityProject,
  PlatformIdentityProjectDirectory,
  PlatformIdentityApplicationDirectory,
  PlatformWorkspace,
  PlatformWorkspaceCollection,
  PlatformWorkspaceInvitation,
  PlatformWorkspaceMember,
  PlatformWorkspaceOverview,
  PlatformWorkspaceResponse
} from '../../shared/types/platform'

export type {
  PlatformIdentityProject,
  PlatformIdentityProjectDirectory,
  PlatformIdentityApplicationDirectory,
  PlatformWorkspace,
  PlatformWorkspaceCollection,
  PlatformWorkspaceInvitation,
  PlatformWorkspaceMember,
  PlatformWorkspaceOverview,
  PlatformWorkspaceResponse
}

type OrganizationPayload<T> = T | { data?: T, response?: T }

function organizationData<T>(payload: OrganizationPayload<T>): T {
  let value: unknown = payload
  for (let depth = 0; depth < 3 && value && typeof value === 'object'; depth++) {
    const record = value as Record<string, unknown>
    if ('data' in record && record.data !== undefined) value = record.data
    else if ('response' in record && record.response !== undefined) value = record.response
    else break
  }
  return value as T
}

export function usePlatformClient() {
  const { csrf, headerName } = useCsrf()
  const session = useIdentitySession()
  const localePath = useLocalePath()
  const authenticatedFetch = useTalredIdentityFetch('starter', {
    defaultRedirect: localePath('/dashboard'),
    logoutHeaders: () => ({ [headerName]: csrf })
  })
  const writeHeaders = { [headerName]: csrf }
  const workspaceCache = useState<Record<string, { workspaces: PlatformWorkspace[], fetchedAt: number }>>('platform-workspace-cache', () => ({}))
  const cacheKey = computed(() => session.user.value?.id || 'anonymous')
  const cacheLifetimeMs = 15_000

  const updateCache = (workspaces: PlatformWorkspace[]) => {
    workspaceCache.value = {
      ...workspaceCache.value,
      [cacheKey.value]: { workspaces, fetchedAt: Date.now() }
    }
  }

  const list = async (options: { force?: boolean } = {}) => {
    const cached = workspaceCache.value[cacheKey.value]
    if (!options.force && cached && Date.now() - cached.fetchedAt < cacheLifetimeMs) {
      return { workspaces: cached.workspaces }
    }

    const collection = await authenticatedFetch<PlatformWorkspaceCollection>('/api/platform/workspaces')
    updateCache(collection.workspaces)
    return collection
  }

  const identityProjectDirectory = () => authenticatedFetch<PlatformIdentityProjectDirectory>('/api/platform/identity-projects')
  const identityApplicationDirectory = () => authenticatedFetch<PlatformIdentityApplicationDirectory>('/api/platform/identity-applications')

  const ensureDefault = async () => {
    const response = await authenticatedFetch<PlatformWorkspaceResponse>('/api/platform/workspaces/default', {
      method: 'POST',
      headers: writeHeaders
    })
    const cached = workspaceCache.value[cacheKey.value]
    if (cached) {
      updateCache([
        ...cached.workspaces
          .filter(workspace => workspace.id !== response.workspace.id)
          .map(workspace => ({ ...workspace, is_default: false })),
        response.workspace
      ])
    }
    return response
  }

  const create = async (body: { name: string, slug?: string }) => {
    const response = await authenticatedFetch<PlatformWorkspaceResponse>('/api/platform/workspaces', {
      method: 'POST',
      headers: writeHeaders,
      body
    })
    const cached = workspaceCache.value[cacheKey.value]
    if (cached) updateCache([...cached.workspaces, response.workspace])
    return response
  }

  const organizationRequest = async <T>(path: string, method: 'GET' | 'POST' | 'PATCH' | 'DELETE' = 'GET', body?: unknown) => {
    const response = await authenticatedFetch<OrganizationPayload<T>>(`/api/platform/organization/${path}`, {
      method,
      headers: method === 'GET' ? undefined : writeHeaders,
      ...(body === undefined ? {} : { body })
    })
    return organizationData(response)
  }

  const updateWorkspace = (workspaceId: string, body: { name: string }) => organizationRequest<{ workspace: PlatformWorkspace }>(`workspaces/${workspaceId}`, 'PATCH', body)
  const getWorkspace = (workspaceId: string) => organizationRequest<PlatformWorkspaceResponse>(`workspaces/${workspaceId}`)
  const getWorkspaceOverview = (workspaceId: string) => organizationRequest<PlatformWorkspaceOverview>(`workspaces/${workspaceId}/overview`)
  const availableIdentityProjects = (workspaceId: string) => organizationRequest<{ availability: string, projects: PlatformIdentityProject[] }>(`workspaces/${workspaceId}/resources/identity-projects/available`)
  const linkIdentityProject = (workspaceId: string, identityProjectId: string) => organizationRequest<{ resource: { id: string, workspace_id: string, type: 'identity_project', external_resource_id: string } }>(`workspaces/${workspaceId}/resources/identity-projects`, 'POST', { identity_project_id: identityProjectId })
  const unlinkIdentityProject = (workspaceId: string, identityProjectId: string) => organizationRequest<{ linked: false }>(`workspaces/${workspaceId}/resources/identity-projects/${identityProjectId}`, 'DELETE')
  const listWorkspaceMembers = (workspaceId: string) => organizationRequest<{ members: PlatformWorkspaceMember[] }>(`workspaces/${workspaceId}/members`)
  const updateWorkspaceMember = (workspaceId: string, accountId: string, role: 'admin' | 'member') => organizationRequest<{ account_id: string, role: string }>(`workspaces/${workspaceId}/members/${accountId}`, 'PATCH', { role })
  const removeWorkspaceMember = (workspaceId: string, accountId: string) => organizationRequest<{ status: string }>(`workspaces/${workspaceId}/members/${accountId}`, 'DELETE')
  const listWorkspaceInvitations = (workspaceId: string) => organizationRequest<{ invitations: PlatformWorkspaceInvitation[] }>(`workspaces/${workspaceId}/invitations`)
  const createWorkspaceInvitation = (workspaceId: string, body: { email: string, role: 'admin' | 'member' }) => organizationRequest<PlatformWorkspaceInvitation>(`workspaces/${workspaceId}/invitations`, 'POST', body)
  const revokeWorkspaceInvitation = (workspaceId: string, invitationId: string) => organizationRequest<{ status: string, identity_login_invitation_revoked: boolean }>(`workspaces/${workspaceId}/invitations/${invitationId}`, 'DELETE')
  const acceptWorkspaceInvitation = (token: string) => organizationRequest<{ workspace: PlatformWorkspace, already_a_member: boolean }>('invitations/accept', 'POST', { token })
  const transferWorkspaceOwnership = (workspaceId: string, accountId: string) => organizationRequest<{ owner_account_id: string }>(`workspaces/${workspaceId}/ownership`, 'POST', { account_id: accountId })

  return {
    list,
    identityProjectDirectory,
    identityApplicationDirectory,
    create,
    ensureDefault,
    updateWorkspace,
    getWorkspace,
    getWorkspaceOverview,
    availableIdentityProjects,
    linkIdentityProject,
    unlinkIdentityProject,
    listWorkspaceMembers,
    updateWorkspaceMember,
    removeWorkspaceMember,
    listWorkspaceInvitations,
    createWorkspaceInvitation,
    revokeWorkspaceInvitation,
    acceptWorkspaceInvitation,
    transferWorkspaceOwnership
  }
}
