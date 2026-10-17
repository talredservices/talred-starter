export function useStarterEcosystemUrl() {
  const { locale } = useI18n()
  const config = useRuntimeConfig()
  return computed(() => {
    const url = new URL(String(config.public.zoltasoftSiteUrl))
    const path = url.pathname.replace(/^\/(?:en|fr)(?=\/|$)/, '')
    url.pathname = `/${locale.value}${path === '/' ? '' : path}`
    return url.toString()
  })
}
