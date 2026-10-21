<script setup lang="ts">
import type { Project } from '../../../../shared/types/projects'

definePageMeta({ layout: 'projects-dashboard', middleware: ['projects-auth'], pageTransition: false })

const { t } = useI18n()
const copy = useTasksDashboardPresentation()
const session = useProjectsIdentitySession()
const localePath = useLocalePath()
const runtimeConfig = useRuntimeConfig()
const overview = useProjectsOverview()
const { projects, pending: projectsPending, error: projectsError } = overview
const createProjectOpen = ref(false)
const deleteProjectOpen = ref(false)
const projectToDelete = ref<Project | null>(null)
const dashboardReady = ref(false)

const name = computed(() => {
  const user = session.user.value as { name?: string, username?: string } | null
  return user?.name || user?.username || 'there'
})
const isTemporaryAccount = computed(() => Boolean((session.user.value as { isTemporary?: boolean } | null)?.isTemporary))

const welcome = computed(() => copy.value.dashboardWelcome.replace('{name}', name.value))
const identityConsoleUrl = new URL('/admin/projects', runtimeConfig.public.identityAuthUrl).toString()
const products = computed(() => [
  { key: 'starter', title: copy.value.dashboardProductStarter, description: copy.value.dashboardProductStarterDescription, icon: 'i-lucide-panels-top-left', to: localePath('/') },
  { key: 'tasks', title: copy.value.dashboardProductTasks, description: copy.value.dashboardProductTasksDescription, icon: 'i-lucide-list-checks', to: localePath('/projects') },
  { key: 'saas', title: copy.value.dashboardProductSaas, description: copy.value.dashboardProductSaasDescription, icon: 'i-lucide-layout-dashboard', to: localePath('/saas') },
  { key: 'identity', title: copy.value.dashboardProductIdentity, description: copy.value.dashboardProductIdentityDescription, icon: 'i-lucide-shield-check', to: identityConsoleUrl, external: true },
  { key: 'docs', title: copy.value.dashboardProductDocs, description: copy.value.dashboardProductDocsDescription, icon: 'i-lucide-book-open-text', to: String(runtimeConfig.public.docsUrl), external: true }
].filter(product => !(isTemporaryAccount.value && product.key === 'identity')))

onMounted(async () => {
  await overview.load()
  dashboardReady.value = true
})

const retryDashboard = async () => {
  await overview.load()
}

const projectCreated = (project: Project) => {
  if (!projects.value.some(item => item.id === project.id)) projects.value.unshift(project)
}

const requestProjectDeletion = (project: Project) => {
  projectToDelete.value = project
  deleteProjectOpen.value = true
}

const projectDeleted = (project: Project) => {
  projects.value = projects.value.filter(item => item.id !== project.id)
}

watch(deleteProjectOpen, (isOpen) => {
  if (!isOpen) projectToDelete.value = null
})

useSeoMeta({ title: () => t('projects.nav.overview') })
</script>

<template>
  <div class="contents">
    <UDashboardPanel
      id="projects-overview"
      class="min-w-0 flex-1"
    >
      <template #header>
        <DashboardPageNavbar :title="t('projects.nav.overview')">
          <template #title>
            <span class="flex min-w-0 items-center gap-2 font-semibold text-highlighted">
              <UIcon
                name="i-lucide-layout-dashboard"
                class="size-4 shrink-0 text-primary sm:size-5"
                aria-hidden="true"
              />
              <span class="truncate">{{ t('projects.nav.overview') }}</span>
            </span>
          </template>
        </DashboardPageNavbar>
      </template>

      <template #body>
        <UContainer class="py-6">
          <div class="w-full max-w-(--ui-container) space-y-10">
            <header class="flex flex-col gap-5 sm:flex-row sm:items-start sm:justify-between">
              <h1 class="flex items-center gap-3 text-3xl font-semibold tracking-tight text-highlighted sm:text-4xl">
                <UIcon
                  name="i-lucide-hand"
                  class="size-7 shrink-0 text-primary sm:size-8"
                  aria-hidden="true"
                />
                <span>{{ welcome }}</span>
              </h1>
              <UButton
                :label="t('projects.nav.settings')"
                icon="i-lucide-settings-2"
                :to="localePath('/projects/dashboard/settings')"
                color="neutral"
                variant="outline"
                class="shrink-0"
              />
            </header>

            <section
              id="projects"
              class="space-y-4"
              aria-labelledby="dashboard-projects-title"
            >
              <div class="flex flex-wrap items-end justify-between gap-3">
                <div>
                  <h2
                    id="dashboard-projects-title"
                    class="text-xl font-semibold text-highlighted"
                  >
                    {{ t('projects.projectsTitle') }}
                  </h2>
                  <p class="mt-1 text-sm leading-6 text-muted">
                    {{ t('projects.projectsDescription') }}
                  </p>
                </div>
                <UButton
                  :label="t('projects.create')"
                  icon="i-lucide-folder-plus"
                  @click="createProjectOpen = true"
                />
              </div>
              <UAlert
                v-if="dashboardReady && projectsError"
                color="error"
                variant="subtle"
                icon="i-lucide-folder-x"
                :title="t(projectsError === 'forbidden' ? 'projects.loadForbiddenTitle' : 'projects.loadFailedTitle')"
                :description="t(projectsError === 'forbidden' ? 'projects.loadForbiddenDescription' : 'projects.loadFailedDescription')"
                :ui="{
                  root: 'border border-error/30 bg-error/5 text-highlighted',
                  icon: 'text-error',
                  title: 'text-highlighted',
                  description: 'text-muted'
                }"
                role="alert"
              >
                <template #actions>
                  <UButton
                    :label="copy.retry"
                    icon="i-lucide-refresh-cw"
                    color="neutral"
                    variant="outline"
                    :loading="projectsPending"
                    @click="retryDashboard"
                  />
                </template>
              </UAlert>
              <ProjectsPortfolioTable
                v-else-if="dashboardReady && projects.length"
                :projects="projects"
                :loading="projectsPending"
                @delete="requestProjectDeletion"
              />
              <UCard
                v-else-if="dashboardReady && !projectsPending"
                variant="outline"
              >
                <div class="flex flex-col items-start gap-3 sm:flex-row sm:items-center sm:justify-between">
                  <p class="text-sm leading-6 text-muted">
                    {{ t('projects.noProjectsDescription') }}
                  </p>
                  <UButton
                    :label="t('projects.noProjectsAction')"
                    icon="i-lucide-folder-plus"
                    @click="createProjectOpen = true"
                  />
                </div>
              </UCard>
              <div
                v-if="!dashboardReady"
                class="space-y-3"
                aria-busy="true"
              >
                <UCard
                  variant="subtle"
                  class="h-20 animate-pulse"
                />
                <UCard
                  variant="subtle"
                  class="h-56 animate-pulse"
                />
              </div>
            </section>

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
                  {{ copy.dashboardProductsTitle }}
                </h2>
                <p class="mt-1 text-sm leading-6 text-muted">
                  {{ copy.dashboardProductsDescription }}
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
                      {{ copy.dashboardExploreProduct }}
                    </UButton>
                  </div>
                </UCard>
              </div>
            </section>
          </div>
        </UContainer>
      </template>
    </UDashboardPanel>
    <ProjectsCreateModal
      v-model:open="createProjectOpen"
      @created="projectCreated"
    />
    <ProjectsDeleteModal
      v-model:open="deleteProjectOpen"
      :project="projectToDelete"
      @deleted="projectDeleted"
    />
  </div>
</template>
