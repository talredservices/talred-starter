<script setup lang="ts">
import type { NavigationMenuItem } from '@nuxt/ui'

const open = ref(false)
const route = useRoute()
const localePath = useLocalePath()
const runtimeConfig = useRuntimeConfig()
const { t } = useI18n()

const identityConsoleUrl = computed(() => {
  const url = new URL('/admin/projects', runtimeConfig.public.identityAuthUrl)
  return url.toString()
})

const closeSidebar = () => {
  open.value = false
}

const primaryLinks = computed<NavigationMenuItem[][]>(() => [[
  {
    label: t('starterDashboard.home'),
    icon: 'i-lucide-layout-dashboard',
    to: localePath('/dashboard'),
    active: route.path === localePath('/dashboard'),
    onSelect: closeSidebar
  }
], [
  {
    label: t('starterDashboard.workspaces'),
    icon: 'i-lucide-panels-top-left',
    to: localePath('/dashboard/workspaces'),
    active: route.path === localePath('/dashboard/workspaces') || route.path.startsWith(`${localePath('/dashboard/workspaces')}/`),
    onSelect: closeSidebar
  }
], [
  {
    label: t('starterDashboard.settings'),
    icon: 'i-lucide-settings',
    to: localePath('/dashboard/settings'),
    active: route.path.startsWith(localePath('/dashboard/settings')),
    onSelect: closeSidebar
  }
]])

const secondaryLinks = computed<NavigationMenuItem[][]>(() => [[
  {
    label: t('starterDashboard.product'),
    icon: 'i-lucide-house',
    to: localePath('/'),
    onSelect: closeSidebar
  },
  {
    label: t('starterDashboard.support'),
    icon: 'i-lucide-circle-help',
    to: runtimeConfig.public.docsUrl,
    target: '_blank',
    onSelect: closeSidebar
  },
  {
    label: t('starterDashboard.identityConsole'),
    icon: 'i-lucide-shield-check',
    to: identityConsoleUrl.value,
    target: '_blank',
    onSelect: closeSidebar
  }
]])
</script>

<template>
  <div class="starter-product-theme min-h-screen">
    <DashboardShellFrame
      v-model:open="open"
      sidebar-id="starter-dashboard"
      :desktop-collapsed="true"
      :primary-links="primaryLinks"
      :secondary-links="secondaryLinks"
      :primary-navigation-label="t('starterDashboard.primaryNavigation')"
      :secondary-navigation-label="t('starterDashboard.secondaryNavigation')"
      :search-label="t('starterDashboard.search')"
    >
      <template #sidebar-header="{ collapsed }">
        <NuxtLink
          :to="localePath('/dashboard')"
          class="inline-flex rounded-lg transition hover:bg-elevated focus-visible:outline-2 focus-visible:outline-primary"
          :aria-label="t('starterDashboard.brand')"
        >
          <StarterBrand :collapsed="collapsed" />
        </NuxtLink>
      </template>

      <template #sidebar-footer="{ collapsed }">
        <DashboardUserMenu
          :collapsed="collapsed"
          home-to="/dashboard"
          settings-to="/dashboard/settings"
        />
      </template>

      <slot />
    </DashboardShellFrame>
  </div>
</template>
