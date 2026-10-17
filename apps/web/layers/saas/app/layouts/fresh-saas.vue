<script setup lang="ts">
import type { NavigationMenuItem } from '@nuxt/ui'
import { STARTER_REPOSITORY_URL } from '~/utils/starter-links'

const publicShellCopy = useStarterPublicPresentation()

const localePath = useLocalePath()
const route = useRoute()
const { t } = useI18n()
const runtimeConfig = useRuntimeConfig()
const identity = useSaasIdentitySession()
const dashboardPath = localePath('/saas/dashboard')
const signInUrl = identity.authorize(dashboardPath, 'login')
const registrationUrl = identity.authorize(dashboardPath, 'register')
const items = computed<NavigationMenuItem[]>(() => [
  { label: t('freshSaas.nav.overview'), to: `${localePath('/saas')}#overview`, active: route.path === localePath('/saas') },
  { label: t('freshSaas.nav.experiences'), to: `${localePath('/saas')}#experiences` },
  { label: t('freshSaas.nav.pricing'), to: localePath('/saas/pricing'), active: route.path === localePath('/saas/pricing') }
])

const footerColumns = computed(() => [
  {
    label: t('freshSaas.footerLinks.product'),
    children: [
      { label: t('freshSaas.footerLinks.overview'), to: localePath('/saas') },
      { label: t('freshSaas.nav.pricing'), to: localePath('/saas/pricing') },
      { label: t('freshSaas.backToStarter'), to: localePath('/') }
    ]
  },
  {
    label: t('freshSaas.footerLinks.resources'),
    children: [
      { label: t('freshSaas.footerLinks.documentation'), to: runtimeConfig.public.docsUrl },
      { label: t('freshSaas.footerLinks.sourceCode'), to: STARTER_REPOSITORY_URL, target: '_blank', rel: 'noopener noreferrer' }
    ]
  },
  {
    label: t('freshSaas.footerLinks.legal'),
    children: [
      { label: t('freshSaas.footerLinks.terms'), to: localePath('/saas/terms') },
      { label: t('freshSaas.footerLinks.privacy'), to: localePath('/saas/privacy') }
    ]
  }
])
</script>

<template>
  <div class="saas-auth-theme min-h-screen bg-default">
    <a
      href="#starter-main"
      class="starter-public-skip"
    >{{ publicShellCopy.skip }}</a>
    <UHeader
      :menu="{ title: 'Navigation', description: t('freshSaas.brandName') }"
      :ui="{ root: 'border-b border-default bg-elevated/90 backdrop-blur', container: 'max-w-6xl' }"
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
          :to="localePath('/saas')"
          :aria-label="t('freshSaas.brandName')"
          class="rounded-lg focus-visible:outline-2 focus-visible:outline-primary"
        >
          <SaasIdentityBrand compact-on-mobile />
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
            :to="signInUrl"
            external
            color="neutral"
            variant="ghost"
            class="whitespace-nowrap"
          >
            {{ t('freshSaas.signIn') }}
          </UButton>
          <UButton
            :to="registrationUrl"
            external
            trailing-icon="i-lucide-arrow-right"
            class="whitespace-nowrap"
            :aria-label="t('freshSaas.createAccount')"
          >
            {{ t('freshSaas.headerDemo') }}
          </UButton>
        </div>
      </template>

      <template #body>
        <UNavigationMenu
          :items="items"
          orientation="vertical"
          class="mb-4"
        />
        <div class="grid grid-cols-2 gap-2">
          <UButton
            :to="signInUrl"
            external
            color="neutral"
            variant="outline"
            block
          >
            {{ t('freshSaas.signIn') }}
          </UButton>
          <UButton
            :to="registrationUrl"
            external
            block
            :aria-label="t('freshSaas.createAccount')"
          >
            {{ t('freshSaas.headerDemo') }}
          </UButton>
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
      :description="t('freshSaas.footer')"
      :maintainer="t('freshSaas.footerLinks.maintainer')"
    >
      <template #brand>
        <SaasIdentityBrand />
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
