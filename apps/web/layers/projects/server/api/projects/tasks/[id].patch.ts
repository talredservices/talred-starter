import { handleTalredApiError } from '#server/http'
import { validateBody } from '#server/utils/zod'
import { projectsService } from '../../../utils/projects.service'
import { parseProjectId, updateTaskSchema } from '../../../utils/projects.schema'

export default defineEventHandler(async (event) => {
  await requireAuthSession(event, 'projects')
  const taskId = parseProjectId(getRouterParam(event, 'id'), 'taskId')
  const body = await validateBody(updateTaskSchema, event)
  try {
    return await (await projectsService(event)).updateTask(taskId, body)
  } catch (error) {
    return handleTalredApiError(error)
  }
})
