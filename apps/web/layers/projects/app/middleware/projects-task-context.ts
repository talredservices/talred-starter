import { isIdentityUnauthorized } from '~~/shared/utils/is-identity-unauthorized'

export default defineNuxtRouteMiddleware(async (to) => {
  if (to.query.project) return

  const api = useProjectsClient()
  const session = useProjectsIdentitySession()
  const authorizeUrl = session.authorize(to.fullPath)

  try {
    const result = await api.list()
    const [project] = result.projects
    if (result.projects.length === 1 && project) {
      return navigateTo({
        path: to.path,
        query: { project: project.id }
      }, { replace: true })
    }
  } catch (error) {
    if (!isIdentityUnauthorized(error)) throw error
    return navigateTo(authorizeUrl, { external: true })
  }
})
