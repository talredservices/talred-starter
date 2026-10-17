export function useAuthenticatedFetch() {
  const { csrf, headerName } = useCsrf()
  const localePath = useLocalePath()

  return useTalredIdentityFetch('starter', {
    defaultRedirect: localePath('/dashboard'),
    loginPath: localePath('/auth/login'),
    logoutHeaders: () => ({ [headerName]: csrf })
  })
}
