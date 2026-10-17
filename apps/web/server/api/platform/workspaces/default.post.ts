import { handleTalredApiError } from '#server/http'
import { ensureDefaultPlatformWorkspace, platformApi } from '#server/utils/platform.service'

export default defineEventHandler(async (event) => {
  await requireAuthSession(event, 'starter')
  try {
    return await ensureDefaultPlatformWorkspace(await platformApi(event))
  } catch (error) {
    return handleTalredApiError(error)
  }
})
