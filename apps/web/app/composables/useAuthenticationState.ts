export function useAuthenticationState() {
  const session = useIdentitySession()
  const localePath = useLocalePath()
  const init = async () => {
    if (!session.ready.value) {
      await session.fetch()
    }
  }

  const login = async (redirect?: string | string[] | null, intent: 'login' | 'register' = 'login') => {
    const destination = resolveIdentityRedirect(redirect, localePath('/dashboard'))
    await navigateTo({
      path: localePath(intent === 'register' ? '/auth/signup' : '/auth/login'),
      query: { redirect: destination }
    })
  }

  const logout = async () => {
    await session.clear()
  }

  // Public routes are prerendered without a live Identity service. Refresh the
  // browser session after hydration instead of making that external request
  // while rendering a public page at build time.
  if (import.meta.client) {
    void init()
  }

  return {
    session,
    logout,
    login
  }
}
