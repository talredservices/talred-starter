import { handleTalredApiError } from '#server/http'
import { listPlatformWorkspaces, platformApi } from '#server/utils/platform.service'

export default defineEventHandler(async (event) => {
  await requireAuthSession(event, 'starter')
  try {
    return await listPlatformWorkspaces(await platformApi(event))
  } catch (error) {
    return handleTalredApiError(error)
  }
})
