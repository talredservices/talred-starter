export type ProjectStatus = 'active' | 'archived'
export type TaskStatus = 'todo' | 'in_progress' | 'done'
export type TaskPriority = 'low' | 'medium' | 'high'

export type Project = {
  id: string
  name: string
  key: string
  description: string | null
  status: ProjectStatus
  tasks_count: number
}

export type ProjectTask = {
  id: string
  project_id: string
  title: string
  description: string | null
  status: TaskStatus
  priority: TaskPriority
}

export type TaskFilters = {
  status?: TaskStatus
  priority?: TaskPriority
  search?: string
}

export type ProjectCollection = { projects: Project[] }
export type ProjectResponse = { project: Project }
export type DeletedProjectResponse = { deleted: boolean, project_id: string }
export type ProjectTasksResponse = { project: Project, tasks: ProjectTask[] }
export type TaskResponse = { task: ProjectTask }
export type DeletedTaskResponse = { deleted: boolean, task_id: string }
