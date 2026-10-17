import { createError, defineEventHandler, getRouterParam } from 'h3'
import { redirectToHostedIdentityAuth } from '../../../../utils/hosted-identity-auth-redirect'

export default defineEventHandler((event) => {
  const locale = getRouterParam(event, 'locale')
  if (!['en', 'fr'].includes(locale ?? '')) {
    throw createError({ statusCode: 404, statusMessage: 'Page not found.' })
  }

  return redirectToHostedIdentityAuth(event, 'projects', `/${locale}/projects/dashboard`)
})
