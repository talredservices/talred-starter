import { handleTalredApiError } from '#server/http'
import { isPlatformOrganizationRouteAllowed } from '#server/utils/platform-organization-routes'
import { validatePlatformOrganizationBody } from '#server/utils/platform-organization-validation'
import { platformApi } from '#server/utils/platform.service'

export default defineEventHandler(async (event) => {
  await requireAuthSession(event, 'starter')

  const path = getRouterParam(event, 'path') || ''
  const method = getMethod(event).toUpperCase()
  if (!isPlatformOrganizationRouteAllowed(path, method)) {
    throw createError({ statusCode: 404, statusMessage: 'Platform organization route not found.' })
  }

  const body = ['GET', 'HEAD', 'DELETE'].includes(method)
    ? undefined
    : validatePlatformOrganizationBody(path, method, await readBody(event))

  try {
    const client = await platformApi(event)
    const options: Record<string, unknown> = { method }
    if (body !== undefined) options.body = body
    return await client<unknown>(`/api/platform/v1/${path}`, options)
  } catch (error) {
    return handleTalredApiError(error)
  }
})
