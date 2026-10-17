import { handleTalredApiError } from '#server/http'
import { validateRequestData } from '#server/validation'
import { getPlatformWorkspace, platformApi } from '#server/utils/platform.service'
import { z } from 'zod/v4'

export default defineEventHandler(async (event) => {
  await requireAuthSession(event, 'starter')
  const { id: workspaceId } = validateRequestData({
    schema: { id: z.string().uuid() },
    data: { id: getRouterParam(event, 'id') },
    statusCode: 400,
    statusMessage: 'A valid workspace id is required.'
  })

  try {
    return await getPlatformWorkspace(await platformApi(event), workspaceId)
  } catch (error) {
    return handleTalredApiError(error)
  }
})
