import { handleTalredApiError } from '#server/http'
import { listPlatformIdentityProjects, platformApi } from '#server/utils/platform.service'

export default defineEventHandler(async (event) => {
  await requireAuthSession(event, 'starter')
  try {
    return await listPlatformIdentityProjects(await platformApi(event))
  } catch (error) {
    return handleTalredApiError(error)
  }
})
