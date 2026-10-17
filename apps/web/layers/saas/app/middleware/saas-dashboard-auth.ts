import { isIdentitySessionExpired } from '~~/shared/utils/identity-session'
import { isIdentityUnauthorized } from '~~/shared/utils/is-identity-unauthorized'

export default defineNuxtRouteMiddleware(async (to) => {
  const session = useSaasIdentitySession()
  const authorizeUrl = session.authorize(to.fullPath)

  try {
    await session.fetch()
  } catch (error) {
    if (!isIdentityUnauthorized(error)) throw error
    return navigateTo(authorizeUrl, { external: true })
  }

  if (isIdentitySessionExpired(session.user.value)) {
    await session.clear(`/saas/auth/login?redirect=${encodeURIComponent(to.fullPath)}&expired=1`)
    return
  }

  if (!session.loggedIn.value) {
    return navigateTo(authorizeUrl, { external: true })
  }
})
