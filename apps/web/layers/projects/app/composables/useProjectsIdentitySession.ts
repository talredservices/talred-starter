export function useProjectsIdentitySession() {
  const { csrf, headerName } = useCsrf()
  const localePath = useLocalePath()
  const identity = useTalredIdentity('projects', {
    defaultRedirect: localePath('/projects/dashboard'),
    logoutRedirect: localePath('/projects'),
    logoutHeaders: () => ({ [headerName]: csrf })
  })
  return { ...identity, fetch: identity.refresh, clear: identity.logout }
}
