import { useI18n } from 'vue-i18n'

export function useNavLinks() {
  const localePath = useLocalePath()
  const route = useRoute()
  const { t } = useI18n()
  const docsPath = localePath('/docs')
  const marketplacePath = localePath('/docs/marketplace')

  return [
    {
      label: t('saas.nav.docs'),
      icon: 'i-lucide-book',
      to: localePath('/docs/getting-started'),
      active: route.path === docsPath || route.path.startsWith(`${docsPath}/`)
    },
    {
      label: t('saas.nav.marketplace'),
      icon: 'i-lucide-store',
      to: localePath('/docs/marketplace/catalog'),
      active:
        route.path === marketplacePath
        || route.path.startsWith(`${marketplacePath}/`)
    },
    {
      label: t('saas.nav.pricing'),
      icon: 'i-lucide-credit-card',
      to: localePath('/pricing')
    },
    {
      label: t('saas.nav.blog'),
      icon: 'i-lucide-pencil',
      to: localePath('/blog')
    }
  ]
}
