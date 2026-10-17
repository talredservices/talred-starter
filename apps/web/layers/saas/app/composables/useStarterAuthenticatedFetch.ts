export function useStarterAuthenticatedFetch() {
  const { csrf, headerName } = useCsrf()
  const localePath = useLocalePath()

  return useTalredIdentityFetch('starter', {
    defaultRedirect: localePath('/dashboard'),
    logoutHeaders: () => ({ [headerName]: csrf })
  })
}
