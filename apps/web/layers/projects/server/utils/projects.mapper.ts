import type { TalredApiEnvelope } from '@talred/api-client'
import type { DeletedProjectResponse, DeletedTaskResponse, Project, ProjectCollection, ProjectResponse, ProjectTask, ProjectTasksResponse, TaskResponse } from '../../shared/types/projects'

type ResponsePayload<T extends object> = TalredApiEnvelope<T> | T | { response?: T, data?: T }

function responseData<T extends object>(payload: ResponsePayload<T>): T {
  if ('data' in payload && payload.data !== undefined) return payload.data
  if ('response' in payload && payload.response !== undefined) return payload.response
  return payload as T
}

function mapProject(project: Project): Project {
  return { ...project, description: project.description ?? null }
}

function mapTask(task: ProjectTask): ProjectTask {
  return { ...task, description: task.description ?? null }
}

export function mapProjectsEnvelope(envelope: ResponsePayload<{ projects: Project[] }>): ProjectCollection {
  return { projects: responseData(envelope).projects.map(mapProject) }
}

export function mapProjectEnvelope(envelope: ResponsePayload<{ project: Project }>): ProjectResponse {
  return { project: mapProject(responseData(envelope).project) }
}

export function mapTasksEnvelope(envelope: ResponsePayload<{ project: Project, tasks: ProjectTask[] }>): ProjectTasksResponse {
  const data = responseData(envelope)
  return {
    project: mapProject(data.project),
    tasks: data.tasks.map(mapTask)
  }
}

export function mapTaskEnvelope(envelope: ResponsePayload<{ task: ProjectTask }>): TaskResponse {
  return { task: mapTask(responseData(envelope).task) }
}

export function mapDeletedTaskEnvelope(envelope: ResponsePayload<DeletedTaskResponse>): DeletedTaskResponse {
  return responseData(envelope)
}

export function mapDeletedProjectEnvelope(envelope: ResponsePayload<DeletedProjectResponse>): DeletedProjectResponse {
  return responseData(envelope)
}
