<script setup lang="ts">
import type { NavigationMenuItem } from '@nuxt/ui'
import { STARTER_REPOSITORY_URL } from '~/utils/starter-links'

const copy = useSaasTemplatePresentation()
const publicCopy = useStarterPublicPresentation()
const docsUrl = useRuntimeConfig().public.docsUrl
const localePath = useLocalePath()
const route = useRoute()
const { t } = useI18n()
const identity = useIdentitySession()
if (import.meta.client) {
  void identity.fetch()
}
const dashboardPath = localePath('/dashboard')
const loginUrl = identity.authorize(dashboardPath, 'login')
const identityActionLabel = computed(() => identity.loggedIn.value ? t('home.identityDashboardAction') : t('home.identityLoginAction'))
const identityActionTarget = computed(() => identity.loggedIn.value ? dashboardPath : loginUrl)
const identityActionExternal = computed(() => !identity.loggedIn.value)
const items = computed<NavigationMenuItem[]>(() => [
  {
    label: copy.value.navigation.home,
    to: localePath('/'),
    active: route.path === localePath('/')
  },
  {
    label: copy.value.navigation.about,
    to: localePath('/about'),
    active: route.path === localePath('/about')
  },
  { label: publicCopy.value.docs, to: docsUrl, external: true }
])
</script>

<template>
  <UHeader class="border-b border-default bg-default">
    <template #toggle="{ open, toggle }">
      <UButton
        :aria-label="open ? publicCopy.closeMenu : publicCopy.openMenu"
        :icon="open ? 'i-lucide-x' : 'i-lucide-menu'"
        color="neutral"
        variant="ghost"
        data-slot="toggle"
        class="min-h-10 min-w-10 lg:hidden"
        @click="toggle"
      />
    </template>
    <template #left>
      <NuxtLink
        :to="localePath('/')"
        class="rounded-lg focus-visible:outline-2 focus-visible:outline-primary"
      >
        <StarterBrand compact-on-mobile />
      </NuxtLink>
    </template>

    <UNavigationMenu
      :items="items"
      class="hidden lg:flex"
    />

    <template #right>
      <div class="hidden items-center gap-2 lg:flex">
        <LanguageSwitcher />
        <UButton
          :to="STARTER_REPOSITORY_URL"
          :aria-label="copy.navigation.github"
          icon="i-simple-icons-github"
          color="neutral"
          variant="ghost"
          target="_blank"
          rel="noopener noreferrer"
        />
        <UColorModeButton
          color="neutral"
          variant="ghost"
        />
        <UButton
          :label="identityActionLabel"
          :to="identityActionTarget"
          :external="identityActionExternal"
          trailing-icon="i-lucide-arrow-right"
        />
      </div>
    </template>

    <template #body>
      <UNavigationMenu
        :items="items"
        orientation="vertical"
        class="mb-4 border-b border-default pb-4"
      />
      <div class="flex flex-wrap items-center justify-between gap-3">
        <div class="flex items-center gap-2">
          <LanguageSwitcher />
          <UButton
            :to="STARTER_REPOSITORY_URL"
            :aria-label="copy.navigation.github"
            :label="copy.navigation.github"
            icon="i-simple-icons-github"
            color="neutral"
            variant="ghost"
            target="_blank"
            rel="noopener noreferrer"
          />
          <UColorModeButton
            color="neutral"
            variant="ghost"
          />
        </div>
      </div>
      <UButton
        :label="identityActionLabel"
        :to="identityActionTarget"
        :external="identityActionExternal"
        trailing-icon="i-lucide-arrow-right"
        block
        class="mt-4"
      />
    </template>
  </UHeader>
</template>
