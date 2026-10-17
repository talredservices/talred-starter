import { handleTalredApiError } from '#server/http'
import { projectsService } from '../../utils/projects.service'
import { parseProjectId } from '../../utils/projects.schema'

export default defineEventHandler(async (event) => {
  await requireAuthSession(event, 'projects')
  const projectId = parseProjectId(getRouterParam(event, 'id'), 'projectId')
  try {
    return await (await projectsService(event)).deleteProject(projectId)
  } catch (error) {
    return handleTalredApiError(error)
  }
})
