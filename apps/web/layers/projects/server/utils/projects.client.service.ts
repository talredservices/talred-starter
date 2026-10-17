import type { H3Event } from 'h3'
import type { TalredApiEnvelope, TalredApiFetchClient } from '@talred/api-client'
import type { DeletedProjectResponse, DeletedTaskResponse, Project, ProjectTask, TaskFilters } from '../../shared/types/projects'
import { createTalredApiClient } from '#server/utils/create-talred-api-client'

type UpstreamBody = Record<string, unknown>

export function projectsApi(event: H3Event): Promise<TalredApiFetchClient> {
  return createTalredApiClient(event, 'projects')
}

export function listProjects(client: TalredApiFetchClient) {
  return client<TalredApiEnvelope<{ projects: Project[] }>>('/api/projects')
}

export function createProject(client: TalredApiFetchClient, body: UpstreamBody) {
  return client<TalredApiEnvelope<{ project: Project }>>('/api/projects', { method: 'POST', body })
}

export function deleteProject(client: TalredApiFetchClient, projectId: string) {
  return client<TalredApiEnvelope<DeletedProjectResponse>>(`/api/projects/${projectId}`, { method: 'DELETE' })
}

export function listProjectTasks(client: TalredApiFetchClient, projectId: string, filters: TaskFilters = {}) {
  return client<TalredApiEnvelope<{ project: Project, tasks: ProjectTask[] }>>(`/api/projects/${projectId}/tasks`, {
    query: Object.fromEntries(Object.entries(filters).filter(([, value]) => value !== undefined && value !== ''))
  })
}

export function createTask(client: TalredApiFetchClient, projectId: string, body: UpstreamBody) {
  return client<TalredApiEnvelope<{ task: ProjectTask }>>(`/api/projects/${projectId}/tasks`, { method: 'POST', body })
}

export function updateTask(client: TalredApiFetchClient, taskId: string, body: UpstreamBody) {
  return client<TalredApiEnvelope<{ task: ProjectTask }>>(`/api/tasks/${taskId}`, { method: 'PATCH', body })
}

export function deleteTask(client: TalredApiFetchClient, taskId: string) {
  return client<TalredApiEnvelope<DeletedTaskResponse>>(`/api/tasks/${taskId}`, { method: 'DELETE' })
}
