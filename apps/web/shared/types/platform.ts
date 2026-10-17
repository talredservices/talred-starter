export interface PlatformWorkspace {
  id: string
  name: string
  slug: string
  status: 'active' | 'archived' | string
  role: 'owner' | 'admin' | 'member' | string
  owner_account_id?: string
  member_count: number
  is_default: boolean
}

export interface PlatformWorkspaceCollection {
  workspaces: PlatformWorkspace[]
}

export interface PlatformWorkspaceResponse {
  workspace: PlatformWorkspace
}

export interface PlatformIdentityProject {
  id: string
  name?: string
  slug?: string
  status?: string
  availability: 'available' | 'access_needed' | 'unavailable' | string
  identity_project?: { id: string, name: string, slug: string, status: string }
  clients?: Array<{ id: string, name: string, secret_prefix: string | null, status: string }>
  applications?: Array<{ id: string, name: string, key: string, status: string, application_url: string | null }>
}

export interface PlatformIdentityProjectDirectory {
  availability: 'available' | 'access_needed' | 'unavailable' | string
  projects: PlatformIdentityProject[]
}

export interface PlatformIdentityApplication {
  id: string
  name: string
  key: string
  status: string
  application_url: string | null
  project: {
    id: string
    name: string
    slug: string
    status: string
    mode: string
  }
}

export interface PlatformIdentityApplicationDirectory {
  availability: 'available' | 'access_needed' | 'unavailable' | string
  applications: PlatformIdentityApplication[]
}

export interface PlatformIdentityProjectSection {
  availability: 'available' | 'access_needed' | 'unavailable' | string
  projects: PlatformIdentityProject[]
}

export interface PlatformWorkspaceOverview {
  workspace: PlatformWorkspace
  resources: {
    identity_projects: PlatformIdentityProjectSection
  }
}

export interface PlatformWorkspaceInvitation {
  id: string
  workspace_id: string
  email: string
  role: 'admin' | 'member'
  invite_token?: string
  identity_invitation_token?: string | null
  identity_already_a_member?: boolean
  expires_at: string
  accepted_at?: string | null
  revoked_at?: string | null
  status?: 'pending' | 'accepted' | 'expired' | 'revoked' | string
}

export interface PlatformWorkspaceMember {
  account_id: string
  role: 'owner' | 'admin' | 'member' | string
  joined_at?: string
}
