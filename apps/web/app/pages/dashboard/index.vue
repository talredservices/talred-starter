<script setup lang="ts">
// Dashboard panels render fragments and cannot participate in page transitions.
definePageMeta({ layout: 'dashboard', middleware: ['auth'], pageTransition: false })

const { t } = useI18n()
const localePath = useLocalePath()
const runtimeConfig = useRuntimeConfig()
const session = useIdentitySession()

const name = computed(() => session.user.value?.name || session.user.value?.username || t('userMenu.authenticatedUser'))
const identityConsoleUrl = new URL('/admin/projects', runtimeConfig.public.identityAuthUrl).toString()

const products = computed(() => [
  { key: 'tasks', title: t('starterDashboard.productTasks'), description: t('starterDashboard.productTasksDescription'), icon: 'i-lucide-list-checks', to: localePath('/projects'), action: t('starterDashboard.openProduct') },
  { key: 'saas', title: t('starterDashboard.productSaas'), description: t('starterDashboard.productSaasDescription'), icon: 'i-lucide-panels-top-left', to: localePath('/saas'), action: t('starterDashboard.openProduct') },
  ...(!session.user.value?.isTemporary
    ? [{ key: 'identity', title: t('starterDashboard.productIdentity'), description: t('starterDashboard.productIdentityDescription'), icon: 'i-lucide-shield-check', to: identityConsoleUrl, external: true, action: t('starterDashboard.openConsole') }]
    : []),
  { key: 'docs', title: t('starterDashboard.productDocs'), description: t('starterDashboard.productDocsDescription'), icon: 'i-lucide-book-open-text', to: String(runtimeConfig.public.docsUrl), external: true, action: t('starterDashboard.openDocs') }
])

const capabilities = [
  { key: 'architecture', icon: 'i-lucide-layers-3', title: t('starterDashboard.capabilityArchitectureTitle'), description: t('starterDashboard.capabilityArchitectureDescription') },
  { key: 'api', icon: 'i-lucide-route', title: t('starterDashboard.capabilityApiTitle'), description: t('starterDashboard.capabilityApiDescription') },
  { key: 'identity', icon: 'i-lucide-shield-check', title: t('starterDashboard.capabilityIdentityTitle'), description: t('starterDashboard.capabilityIdentityDescription') },
  { key: 'client', icon: 'i-lucide-panels-top-left', title: t('starterDashboard.capabilityClientTitle'), description: t('starterDashboard.capabilityClientDescription') }
]

useSeoMeta({
  title: () => `${t('starterDashboard.home')} · ${t('starterDashboard.brand')}`,
  robots: 'noindex, nofollow'
})
</script>

<template>
  <UDashboardPanel
    id="starter-home"
    class="min-w-0 flex-1"
  >
    <template #header>
      <DashboardPageNavbar>
        <template #title>
          <div class="flex items-center gap-2 font-semibold text-highlighted">
            <UIcon
              name="i-lucide-layout-dashboard"
              class="size-4 text-primary"
              aria-hidden="true"
            />
            {{ t('starterDashboard.home') }}
          </div>
        </template>
      </DashboardPageNavbar>
    </template>

    <template #body>
      <UContainer class="py-6">
        <div class="w-full space-y-10">
          <header class="space-y-2">
            <h1 class="text-2xl font-semibold tracking-tight text-highlighted sm:text-3xl">
              {{ t('starterDashboard.welcome', { name }) }}
            </h1>
            <p class="max-w-3xl text-sm leading-6 text-muted">
              {{ t('starterDashboard.welcomeDescription') }}
            </p>
          </header>

          <section
            class="space-y-4"
            aria-labelledby="starter-products-title"
          >
            <div>
              <h2
                id="starter-products-title"
                class="text-xl font-semibold text-highlighted"
              >
                {{ t('starterDashboard.productsTitle') }}
              </h2>
              <p class="mt-1 text-sm leading-6 text-muted">
                {{ t('starterDashboard.productsDescription') }}
              </p>
            </div>
            <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
              <UCard
                v-for="product in products"
                :key="product.key"
                variant="outline"
                class="h-full"
              >
                <div class="flex h-full flex-col items-start">
                  <span class="flex size-10 items-center justify-center rounded-xl bg-primary/10 text-primary">
                    <UIcon
                      :name="product.icon"
                      class="size-5"
                      aria-hidden="true"
                    />
                  </span>
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
                    {{ product.action }}
                    <span class="sr-only">: {{ product.title }}</span>
                  </UButton>
                </div>
              </UCard>
            </div>
          </section>

          <section
            class="space-y-4"
            aria-labelledby="starter-capabilities-title"
          >
            <div>
              <h2
                id="starter-capabilities-title"
                class="text-xl font-semibold text-highlighted"
              >
                {{ t('starterDashboard.capabilitiesTitle') }}
              </h2>
              <p class="mt-1 text-sm leading-6 text-muted">
                {{ t('starterDashboard.capabilitiesDescription') }}
              </p>
            </div>
            <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
              <UCard
                v-for="capability in capabilities"
                :key="capability.key"
                variant="subtle"
              >
                <UIcon
                  :name="capability.icon"
                  class="size-5 text-primary"
                  aria-hidden="true"
                />
                <h3 class="mt-3 font-medium text-highlighted">
                  {{ capability.title }}
                </h3>
                <p class="mt-1 text-sm leading-6 text-muted">
                  {{ capability.description }}
                </p>
              </UCard>
            </div>
          </section>
        </div>
      </UContainer>
    </template>
  </UDashboardPanel>
</template>
