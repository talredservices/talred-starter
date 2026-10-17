import type { DeletedProjectResponse, DeletedTaskResponse, Project, ProjectCollection, ProjectResponse, ProjectTask, ProjectTasksResponse, TaskFilters, TaskPriority, TaskResponse, TaskStatus } from '../../shared/types/projects'

export type { DeletedProjectResponse, DeletedTaskResponse, Project, ProjectCollection, ProjectResponse, ProjectTask, ProjectTasksResponse, TaskFilters, TaskPriority, TaskResponse, TaskStatus }

export function useProjectsClient() {
  const { csrf, headerName } = useCsrf()
  const localePath = useLocalePath()
  const authenticatedFetch = useTalredIdentityFetch('projects', {
    defaultRedirect: localePath('/projects/dashboard'),
    logoutHeaders: () => ({ [headerName]: csrf })
  })
  const writeHeaders = { [headerName]: csrf }

  const list = () => authenticatedFetch<ProjectCollection>('/api/projects')
  const create = (body: { name: string, key: string, description?: string }) => authenticatedFetch<ProjectResponse>('/api/projects', { method: 'POST', headers: writeHeaders, body })
  const deleteProject = (id: string) => authenticatedFetch<DeletedProjectResponse>(`/api/projects/${id}`, { method: 'DELETE', headers: writeHeaders })
  const tasks = (id: string, filters: TaskFilters = {}) => authenticatedFetch<ProjectTasksResponse>(`/api/projects/${id}/tasks`, { query: filters })
  const createTask = (id: string, body: { title: string, description?: string, priority: TaskPriority }) => authenticatedFetch<TaskResponse>(`/api/projects/${id}/tasks`, { method: 'POST', headers: writeHeaders, body })
  const updateTask = (id: string, body: Partial<Pick<ProjectTask, 'status' | 'priority' | 'title' | 'description'>>) => authenticatedFetch<TaskResponse>(`/api/projects/tasks/${id}`, { method: 'PATCH', headers: writeHeaders, body })
  const deleteTask = (id: string) => authenticatedFetch<DeletedTaskResponse>(`/api/projects/tasks/${id}`, { method: 'DELETE', headers: writeHeaders })

  return { list, create, deleteProject, tasks, createTask, updateTask, deleteTask }
}
