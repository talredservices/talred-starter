import { handleTalredApiError } from '#server/http'
import { validateRequestData } from '#server/validation'
import { createPlatformWorkspace, platformApi } from '#server/utils/platform.service'
import { z } from 'zod/v4'

export default defineEventHandler(async (event) => {
  await requireAuthSession(event, 'starter')
  try {
    const body = validateRequestData({
      schema: {
        name: z.string().trim().min(1).max(120),
        slug: z.string().trim().max(64).regex(/^[a-z0-9]+(?:-[a-z0-9]+)*$/).optional()
      },
      data: await readBody(event),
      statusCode: 400,
      statusMessage: 'Invalid workspace request.',
      strict: true
    })
    return await createPlatformWorkspace(await platformApi(event), body)
  } catch (error) {
    return handleTalredApiError(error)
  }
})
