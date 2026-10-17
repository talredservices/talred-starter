<script setup lang="ts">
import type { NavigationMenuItem } from '@nuxt/ui'
import { STARTER_REPOSITORY_URL } from '~/utils/starter-links'

const publicShellCopy = useStarterPublicPresentation()

const copy = useProjectsHomePresentation()
const localePath = useLocalePath()
const route = useRoute()
const runtimeConfig = useRuntimeConfig()
const session = useProjectsIdentitySession()
const dashboardPath = localePath('/projects/dashboard')
const loginUrl = session.authorize(dashboardPath)
const signupUrl = session.authorize(dashboardPath, 'register')

await session.fetch()

const items = computed<NavigationMenuItem[]>(() => [
  { label: copy.value.navigation.home, to: localePath('/projects'), active: route.path === localePath('/projects') },
  { label: copy.value.navigation.about, to: localePath('/projects/about'), active: route.path === localePath('/projects/about') }
])

const footerColumns = computed(() => [
  {
    label: copy.value.footer.product,
    children: [
      { label: copy.value.navigation.home, to: localePath('/projects') },
      { label: copy.value.navigation.about, to: localePath('/projects/about') },
      { label: copy.value.footer.tasks, to: localePath('/projects/dashboard/tasks') }
    ]
  },
  {
    label: copy.value.footer.resources,
    children: [
      { label: copy.value.footer.support, to: runtimeConfig.public.docsUrl },
      { label: copy.value.footer.sourceCode, to: STARTER_REPOSITORY_URL, target: '_blank', rel: 'noopener noreferrer' }
    ]
  },
  {
    label: copy.value.footer.legal,
    children: [
      { label: copy.value.footer.terms, to: localePath('/projects/terms') },
      { label: copy.value.footer.privacy, to: localePath('/projects/privacy') }
    ]
  }
])
</script>

<template>
  <div class="projects-theme min-h-screen">
    <a
      href="#starter-main"
      class="starter-public-skip"
    >{{ publicShellCopy.skip }}</a>
    <UHeader
      :menu="{ title: 'Navigation', description: 'Talred Tasks' }"
      :ui="{ container: 'max-w-6xl' }"
    >
      <template #toggle="{ open, toggle }">
        <UButton
          :aria-label="open ? publicShellCopy.closeMenu : publicShellCopy.openMenu"
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
          :to="localePath('/projects')"
          class="rounded-lg focus-visible:outline-2 focus-visible:outline-primary"
        >
          <ProjectsBrand compact-on-mobile />
        </NuxtLink>
      </template>

      <UNavigationMenu
        :items="items"
        class="hidden lg:flex"
      />

      <template #right>
        <div class="hidden items-center gap-2 lg:flex">
          <LanguageSwitcher />
          <UColorModeButton
            color="neutral"
            variant="ghost"
          />
          <UButton
            v-if="session.loggedIn.value"
            :label="copy.navigation.dashboard"
            :to="dashboardPath"
            trailing-icon="i-lucide-arrow-right"
          />
          <template v-else>
            <UButton
              :label="copy.navigation.login"
              :href="loginUrl"
              external
              color="neutral"
              variant="ghost"
            />
            <UButton
              :label="copy.navigation.start"
              :href="signupUrl"
              external
              trailing-icon="i-lucide-arrow-right"
            />
          </template>
        </div>
      </template>

      <template #body>
        <UNavigationMenu
          :items="items"
          orientation="vertical"
          class="mb-4"
        />
        <UButton
          v-if="session.loggedIn.value"
          :label="copy.navigation.dashboard"
          :to="dashboardPath"
          block
        />
        <div
          v-else
          class="grid grid-cols-2 gap-2"
        >
          <UButton
            :label="copy.navigation.login"
            :href="loginUrl"
            external
            color="neutral"
            variant="outline"
            block
          />
          <UButton
            :label="copy.navigation.start"
            :href="signupUrl"
            external
            block
          />
        </div>
        <div class="mt-4 flex flex-wrap items-center gap-2 border-t border-default pt-4">
          <LanguageSwitcher />
          <UColorModeButton
            color="neutral"
            variant="ghost"
          />
          <UButton
            label="GitHub"
            icon="i-simple-icons-github"
            color="neutral"
            variant="ghost"
            :to="STARTER_REPOSITORY_URL"
            external
          />
        </div>
      </template>
    </UHeader>

    <StarterBetaBanner />

    <UMain
      id="starter-main"
      tabindex="-1"
    >
      <slot />
    </UMain>

    <StarterPublicFooter
      :columns="footerColumns"
      :description="copy.footer.description"
      :maintainer="copy.footer.maintainer"
    >
      <template #brand>
        <ProjectsBrand />
      </template>
      <template #actions>
        <LanguageSwitcher />
        <UColorModeButton
          color="neutral"
          variant="ghost"
        />
      </template>
    </StarterPublicFooter>
  </div>
</template>
