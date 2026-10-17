import { handleTalredApiError } from '#server/http'
import { projectsService } from '../../utils/projects.service'

export default defineEventHandler(async (event) => {
  await requireAuthSession(event, 'projects')
  try {
    return await (await projectsService(event)).list()
  } catch (error) {
    return handleTalredApiError(error)
  }
})
