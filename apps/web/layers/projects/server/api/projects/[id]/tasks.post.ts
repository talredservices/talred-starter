import { handleTalredApiError } from '#server/http'
import { validateBody } from '#server/utils/zod'
import { projectsService } from '../../../utils/projects.service'
import { createTaskSchema, parseProjectId } from '../../../utils/projects.schema'

export default defineEventHandler(async (event) => {
  await requireAuthSession(event, 'projects')
  const projectId = parseProjectId(getRouterParam(event, 'id'), 'projectId')
  const body = await validateBody(createTaskSchema, event)
  try {
    return await (await projectsService(event)).createTask(projectId, body)
  } catch (error) {
    return handleTalredApiError(error)
  }
})
