import { handleTalredApiError } from '#server/http'
import { listPlatformIdentityApplications, platformApi } from '#server/utils/platform.service'

export default defineEventHandler(async (event) => {
  await requireAuthSession(event, 'starter')
  try {
    return await listPlatformIdentityApplications(await platformApi(event))
  } catch (error) {
    return handleTalredApiError(error)
  }
})
