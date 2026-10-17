import { createError, type H3Event } from 'h3'
import { logoutIdentityApplication, requireIdentityUser } from '@talred/identity-consumer-nuxt/runtime'
import type { IdentityAuthenticatedUser } from '~~/shared/types/identity-authenticated-user'
import { isIdentitySessionExpired } from '~~/shared/utils/identity-session'

export async function requireAuthSession(event: H3Event, application: 'starter' | 'projects' = 'starter'): Promise<IdentityAuthenticatedUser> {
  const user = await requireIdentityUser(event, application) as IdentityAuthenticatedUser
  if (isIdentitySessionExpired(user)) {
    await logoutIdentityApplication(event, application)
    throw createError({ statusCode: 401, statusMessage: 'Your Identity session has expired.' })
  }
  return user
}
