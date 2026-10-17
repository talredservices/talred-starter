import { handleTalredApiError } from '#server/http'
import { validateQuery } from '#server/utils/zod'
import { projectsService } from '../../../utils/projects.service'
import { parseProjectId, taskFiltersSchema } from '../../../utils/projects.schema'

export default defineEventHandler(async (event) => {
  await requireAuthSession(event, 'projects')
  const projectId = parseProjectId(getRouterParam(event, 'id'), 'projectId')
  const filters = validateQuery(taskFiltersSchema, event)
  try {
    return await (await projectsService(event)).tasks(projectId, filters)
  } catch (error) {
    return handleTalredApiError(error)
  }
})
