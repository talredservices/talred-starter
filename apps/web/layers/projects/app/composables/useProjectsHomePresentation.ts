export function useProjectsHomePresentation() {
  const { t } = useI18n()

  return computed(() => ({
    brand: t('projects.brand'),
    navigation: {
      home: t('projects.nav.home'),
      about: t('projects.nav.about'),
      login: t('projects.login'),
      dashboard: t('projects.dashboard'),
      start: t('projects.start')
    },
    footer: {
      product: t('projects.brand'),
      tasks: t('projects.nav.tasks'),
      description: t('projects.footer.description'),
      resources: t('projects.footer.resources'),
      support: t('projects.footer.support'),
      sourceCode: t('projects.footer.sourceCode'),
      legal: t('projects.footer.legal'),
      terms: t('projects.footer.terms'),
      privacy: t('projects.footer.privacy'),
      maintainer: t('projects.footer.maintainer')
    },
    hero: {
      title: t('projects.title'),
      description: t('projects.description'),
      start: t('projects.start'),
      seeTasks: t('projects.nav.tasks')
    }
  }))
}
