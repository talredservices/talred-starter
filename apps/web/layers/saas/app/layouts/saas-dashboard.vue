<script setup lang="ts">
import type { NavigationMenuItem } from '@nuxt/ui'

const open = ref(false)
const route = useRoute()
const localePath = useLocalePath()
const { t } = useI18n()
const config = useRuntimeConfig()
const ecosystemUrl = useStarterEcosystemUrl()

const closeSidebar = () => {
  open.value = false
}

const primaryLinks = computed<NavigationMenuItem[][]>(() => [[
  {
    label: t('dashboard.home'),
    icon: 'i-lucide-layout-dashboard',
    to: localePath('/saas/dashboard'),
    active: route.path === localePath('/saas/dashboard'),
    onSelect: closeSidebar
  },
  {
    label: t('dashboard.settings'),
    icon: 'i-lucide-settings',
    to: localePath('/saas/dashboard/settings'),
    active: route.path.startsWith(localePath('/saas/dashboard/settings')),
    onSelect: closeSidebar
  }
]])

const secondaryLinks = computed<NavigationMenuItem[][]>(() => [[
  {
    label: t('dashboard.product'),
    icon: 'i-lucide-globe',
    to: localePath('/saas'),
    onSelect: closeSidebar
  },
  {
    label: t('dashboard.talredServices'),
    icon: 'i-lucide-globe-2',
    to: ecosystemUrl.value,
    target: '_blank',
    rel: 'noopener noreferrer',
    onSelect: closeSidebar
  },
  {
    label: t('dashboard.documentation'),
    icon: 'i-lucide-file-text',
    to: String(config.public.docsUrl),
    target: '_blank',
    rel: 'noopener noreferrer',
    onSelect: closeSidebar
  },
  {
    label: t('dashboard.starter'),
    icon: 'i-lucide-panels-top-left',
    to: localePath('/'),
    onSelect: closeSidebar
  }
]])
</script>

<template>
  <div class="saas-dashboard-theme min-h-screen">
    <DashboardShellFrame
      v-model:open="open"
      sidebar-id="saas-dashboard"
      :desktop-collapsed="true"
      :primary-links="primaryLinks"
      :secondary-links="secondaryLinks"
      :primary-navigation-label="t('dashboard.primaryNavigation')"
      :secondary-navigation-label="t('dashboard.secondaryNavigation')"
      :search-label="t('dashboard.search')"
    >
      <template #sidebar-header="{ collapsed }">
        <NuxtLink
          :to="localePath('/saas/dashboard')"
          class="inline-flex rounded-lg transition hover:bg-elevated focus-visible:outline-2 focus-visible:outline-primary"
          :aria-label="t('dashboard.brand')"
        >
          <SaasBrand :collapsed="collapsed" />
        </NuxtLink>
      </template>

      <template #sidebar-footer="{ collapsed }">
        <SaasUserMenu
          :collapsed="collapsed"
          home-to="/saas/dashboard"
          settings-to="/saas/dashboard/settings"
        />
      </template>

      <slot />
    </DashboardShellFrame>
  </div>
</template>
