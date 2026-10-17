import { handleTalredApiError } from '#server/http'
import { projectsService } from '../../../utils/projects.service'
import { parseProjectId } from '../../../utils/projects.schema'

export default defineEventHandler(async (event) => {
  await requireAuthSession(event, 'projects')
  const taskId = parseProjectId(getRouterParam(event, 'id'), 'taskId')
  try {
    return await (await projectsService(event)).deleteTask(taskId)
  } catch (error) {
    return handleTalredApiError(error)
  }
})
