<script setup lang="ts">
// Dashboard panels render fragments and cannot participate in page transitions.
definePageMeta({ layout: 'saas-dashboard', middleware: ['saas-dashboard-auth'], pageTransition: false })

const { t } = useI18n()
const session = useSaasIdentitySession()
const localePath = useLocalePath()
const runtimeConfig = useRuntimeConfig()

const name = computed(() => {
  const user = session.user.value as { name?: string, username?: string } | null
  return user?.name || user?.username || 'there'
})
const isTemporaryAccount = computed(() => Boolean((session.user.value as { isTemporary?: boolean } | null)?.isTemporary))

const welcome = computed(() => t('dashboard.welcome', { name: name.value }))
const identityConsoleUrl = new URL('/admin/projects', runtimeConfig.public.identityAuthUrl).toString()
const products = computed(() => [
  { key: 'starter', title: t('dashboard.productStarter'), description: t('dashboard.productStarterDescription'), icon: 'i-lucide-panels-top-left', to: localePath('/') },
  { key: 'tasks', title: t('dashboard.productTasks'), description: t('dashboard.productTasksDescription'), icon: 'i-lucide-list-checks', to: localePath('/projects') },
  { key: 'saas', title: t('dashboard.productSaas'), description: t('dashboard.productSaasDescription'), icon: 'i-lucide-layout-dashboard', to: localePath('/saas') },
  { key: 'identity', title: t('dashboard.productIdentity'), description: t('dashboard.productIdentityDescription'), icon: 'i-lucide-shield-check', to: identityConsoleUrl, external: true },
  { key: 'docs', title: t('dashboard.productDocs'), description: t('dashboard.productDocsDescription'), icon: 'i-lucide-book-open-text', to: String(runtimeConfig.public.docsUrl), external: true }
].filter(product => !(isTemporaryAccount.value && product.key === 'identity')))

useSeoMeta({
  title: () => `${t('dashboard.home')} · ${t('dashboard.brand')}`,
  robots: 'noindex, nofollow'
})
</script>

<template>
  <UDashboardPanel id="saas-home">
    <template #header>
      <DashboardPageNavbar>
        <template #title>
          <div class="flex items-center gap-2 font-semibold text-highlighted">
            <UIcon
              name="i-lucide-layout-dashboard"
              class="size-5 shrink-0 text-primary sm:size-6"
            />
            {{ t('dashboard.home') }}
          </div>
        </template>
      </DashboardPageNavbar>
    </template>

    <template #body>
      <UContainer class="py-6">
        <div class="w-full max-w-(--ui-container) space-y-10">
          <header class="flex flex-col gap-5 sm:flex-row sm:items-start sm:justify-between">
            <div>
              <h1 class="flex items-center gap-3 text-3xl font-semibold tracking-tight text-highlighted sm:text-4xl">
                <UIcon
                  name="i-lucide-hand"
                  class="size-7 shrink-0 text-primary sm:size-8"
                  aria-hidden="true"
                />
                <span>{{ welcome }}</span>
              </h1>
            </div>
            <UButton
              :label="t('dashboard.settings')"
              icon="i-lucide-settings"
              :to="localePath('/saas/dashboard/settings')"
              color="neutral"
              variant="outline"
              class="shrink-0"
            />
          </header>

          <section
            id="products"
            class="space-y-4"
            aria-labelledby="talred-products-title"
          >
            <div>
              <h2
                id="talred-products-title"
                class="text-xl font-semibold text-highlighted"
              >
                {{ t('dashboard.productsTitle') }}
              </h2>
              <p class="mt-1 text-sm leading-6 text-muted">
                {{ t('dashboard.productsDescription') }}
              </p>
            </div>
            <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
              <UCard
                v-for="product in products"
                :key="product.key"
                variant="outline"
                class="h-full"
              >
                <div class="flex h-full flex-col items-start">
                  <UIcon
                    :name="product.icon"
                    class="size-5 text-primary"
                    aria-hidden="true"
                  />
                  <h3 class="mt-4 font-semibold text-highlighted">
                    {{ product.title }}
                  </h3>
                  <p class="mt-2 flex-1 text-sm leading-6 text-muted">
                    {{ product.description }}
                  </p>
                  <UButton
                    :to="product.to"
                    :external="product.external"
                    :target="product.external ? '_blank' : undefined"
                    :rel="product.external ? 'noopener noreferrer' : undefined"
                    color="neutral"
                    variant="link"
                    trailing-icon="i-lucide-arrow-up-right"
                    class="mt-4 px-0"
                  >
                    {{ t('dashboard.exploreProduct') }}
                  </UButton>
                </div>
              </UCard>
            </div>
          </section>
        </div>
      </UContainer>
    </template>
  </UDashboardPanel>
</template>
