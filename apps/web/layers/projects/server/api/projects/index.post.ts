import { handleTalredApiError } from '#server/http'
import { validateBody } from '#server/utils/zod'
import { projectsService } from '../../utils/projects.service'
import { createProjectSchema } from '../../utils/projects.schema'

export default defineEventHandler(async (event) => {
  await requireAuthSession(event, 'projects')
  const body = await validateBody(createProjectSchema, event)
  try {
    return await (await projectsService(event)).create(body)
  } catch (error) {
    return handleTalredApiError(error)
  }
})
