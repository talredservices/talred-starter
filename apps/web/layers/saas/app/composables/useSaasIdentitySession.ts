export function useSaasIdentitySession() {
  const { csrf, headerName } = useCsrf()
  const identity = useTalredIdentity('saas', {
    defaultRedirect: '/saas/dashboard',
    logoutHeaders: () => ({ [headerName]: csrf })
  })

  return {
    ...identity,
    fetch: identity.refresh,
    clear: identity.logout
  }
}
