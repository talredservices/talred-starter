/**
 * Browser-facing session state for the Talred Starter Identity consumer.
 *
 * Identity owns the encrypted session cookie and all token material. This
 * adapter exposes only the user-facing state needed by the application shell.
 */
export function useIdentitySession() {
  const { csrf, headerName } = useCsrf()
  const identity = useTalredIdentity('starter', {
    defaultRedirect: '/dashboard',
    logoutHeaders: () => ({ [headerName]: csrf })
  })

  return {
    ...identity,
    fetch: identity.refresh,
    clear: identity.logout
  }
}
