import { isIdentityUnauthorized } from '~~/shared/utils/is-identity-unauthorized'

export default defineNuxtRouteMiddleware(async (to) => {
  const session = useProjectsIdentitySession()
  const authorizeUrl = session.authorize(to.fullPath)
  try {
    await session.fetch()
  } catch (error) {
    if (!isIdentityUnauthorized(error)) throw error
    return navigateTo(authorizeUrl, { external: true })
  }
  if (!session.loggedIn.value) return navigateTo(authorizeUrl, { external: true })
})
