<script setup lang="ts">
import type { NavigationMenuItem, CommandPaletteGroup, CommandPaletteItem } from '@nuxt/ui'

const route = useRoute()
const localePath = useLocalePath()
const { locale, t } = useI18n()
const open = ref(false)
const config = useRuntimeConfig()
const ecosystemUrl = useStarterEcosystemUrl()

const localizedPath = (path: string) => localePath(path, locale.value)

const closeSidebar = () => {
  open.value = false
}

const primaryLinks = computed<NavigationMenuItem[][]>(() => [[
  { label: t('projects.nav.overview'), icon: 'i-lucide-layout-dashboard', to: localizedPath('/projects/dashboard'), active: route.path === localizedPath('/projects/dashboard'), onSelect: closeSidebar },
  { label: t('projects.nav.tasks'), icon: 'i-lucide-list-checks', to: localizedPath('/projects/dashboard/tasks'), active: route.path.startsWith(localizedPath('/projects/dashboard/tasks')), onSelect: closeSidebar }
], [
  { label: t('projects.nav.settings'), icon: 'i-lucide-settings-2', to: localizedPath('/projects/dashboard/settings'), active: route.path.startsWith(localizedPath('/projects/dashboard/settings')), onSelect: closeSidebar }
]])

const searchGroups = computed<CommandPaletteGroup<CommandPaletteItem>[]>(() => [{
  id: 'tasks-navigation',
  label: t('projects.nav.primaryNavigation'),
  items: primaryLinks.value.flat().map(item => ({ label: item.label, icon: item.icon, to: item.to, onSelect: closeSidebar }))
}])

const secondaryLinks = computed<NavigationMenuItem[][]>(() => [[
  { label: t('projects.nav.home'), icon: 'i-lucide-house', to: localizedPath('/projects'), onSelect: closeSidebar },
  { label: t('projects.nav.talredServices'), icon: 'i-lucide-globe', to: ecosystemUrl.value, target: '_blank', rel: 'noopener noreferrer', onSelect: closeSidebar },
  { label: t('projects.nav.documentation'), icon: 'i-lucide-file-text', to: String(config.public.docsUrl), target: '_blank', rel: 'noopener noreferrer', onSelect: closeSidebar },
  { label: t('projects.nav.starter'), icon: 'i-lucide-panels-top-left', to: localizedPath('/'), onSelect: closeSidebar }
]])
</script>

<template>
  <div class="projects-theme min-h-screen">
    <DashboardShellFrame
      v-model:open="open"
      sidebar-id="projects-dashboard"
      :desktop-collapsed="false"
      :default-size="12"
      :primary-links="primaryLinks"
      :search-groups="searchGroups"
      :secondary-links="secondaryLinks"
      :primary-navigation-label="t('projects.nav.primaryNavigation')"
      :secondary-navigation-label="t('projects.nav.secondaryNavigation')"
      :search-label="t('projects.nav.search')"
    >
      <template #sidebar-header="{ collapsed }">
        <NuxtLink
          :to="localizedPath('/projects/dashboard')"
          class="inline-flex rounded-lg transition hover:bg-elevated focus-visible:outline-2 focus-visible:outline-primary"
          :aria-label="t('projects.brand')"
        >
          <ProjectsBrand :collapsed="collapsed" />
        </NuxtLink>
      </template>

      <template #sidebar-footer="{ collapsed }">
        <ProjectsUserMenu :collapsed="collapsed" />
      </template>

      <template #workspace>
        <slot />
      </template>
    </DashboardShellFrame>
  </div>
</template>
