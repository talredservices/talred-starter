<script setup lang="ts">
import type { PlatformIdentityApplication, PlatformIdentityProject, PlatformWorkspace } from '~~/shared/types/platform'
import { identityApplicationLaunchUrl } from '~/utils/identity-application-links'

definePageMeta({ layout: 'dashboard', middleware: ['auth'], pageTransition: false })

const { t } = useI18n()
const localePath = useLocalePath()
const runtimeConfig = useRuntimeConfig()
const client = usePlatformClient()

const workspaces = ref<PlatformWorkspace[]>([])
const identityApplications = ref<PlatformIdentityApplication[]>([])
const identityApplicationAvailability = ref('available')
const identityProjects = ref<PlatformIdentityProject[]>([])
const identityAvailability = ref('available')
const workspacesLoading = ref(true)
const identityApplicationsLoading = ref(true)
const identityProjectsLoading = ref(true)
const workspaceError = ref(false)
const identityApplicationsError = ref(false)
const identityProjectsError = ref(false)

const identityConsoleUrl = (projectId?: string) => new URL(
  projectId ? `/admin/projects/${projectId}` : '/admin/projects',
  runtimeConfig.public.identityAuthUrl
).toString()
const applicationLaunchUrl = (application: PlatformIdentityApplication) => identityApplicationLaunchUrl(application)

async function loadWorkspaces() {
  workspaceError.value = false
  try {
    let collection = await client.list()
    if (!collection.workspaces.some(workspace => workspace.is_default)) {
      await client.ensureDefault()
      collection = await client.list({ force: true })
    }
    workspaces.value = collection.workspaces
  } catch {
    workspaceError.value = true
  } finally {
    workspacesLoading.value = false
  }
}

async function loadIdentityProjects() {
  identityProjectsError.value = false
  try {
    const directory = await client.identityProjectDirectory()
    identityAvailability.value = directory.availability
    identityProjects.value = directory.projects
  } catch {
    identityAvailability.value = 'unavailable'
    identityProjects.value = []
    identityProjectsError.value = true
  } finally {
    identityProjectsLoading.value = false
  }
}

async function loadIdentityApplications() {
  identityApplicationsError.value = false
  try {
    const directory = await client.identityApplicationDirectory()
    identityApplicationAvailability.value = directory.availability
    identityApplications.value = directory.applications
  } catch {
    identityApplicationAvailability.value = 'unavailable'
    identityApplications.value = []
    identityApplicationsError.value = true
  } finally {
    identityApplicationsLoading.value = false
  }
}

onMounted(() => {
  void loadWorkspaces()
  void loadIdentityApplications()
  void loadIdentityProjects()
})

useSeoMeta({
  title: () => `${t('starterDashboard.home')} · ${t('starterDashboard.brand')}`,
  robots: 'noindex, nofollow'
})
</script>

<template>
  <UDashboardPanel id="starter-home">
    <template #header>
      <DashboardPageNavbar>
        <template #title>
          <div class="flex items-center gap-2 font-semibold text-highlighted">
            <UIcon
              name="i-lucide-layout-dashboard"
              class="size-4 text-primary"
            />
            {{ t('starterDashboard.home') }}
          </div>
        </template>
      </DashboardPageNavbar>
    </template>

    <template #body>
      <UContainer class="w-full max-w-none px-4 py-6 sm:px-6 lg:px-8">
        <div class="mx-auto w-full max-w-6xl space-y-9">
          <header>
            <h1 class="text-3xl font-semibold tracking-tight text-highlighted">
              {{ t('starterDashboard.globalHomeTitle') }}
            </h1>
            <p class="mt-2 max-w-3xl text-sm leading-6 text-muted">
              {{ t('starterDashboard.globalHomeDescription') }}
            </p>
          </header>

          <section
            class="space-y-4"
            aria-labelledby="home-applications-heading"
          >
            <div>
              <h2
                id="home-applications-heading"
                class="text-xl font-semibold text-highlighted"
              >
                {{ t('starterDashboard.applications') }}
              </h2>
              <p class="mt-1 text-sm text-muted">
                {{ t('starterDashboard.homeApplicationsDescription') }}
              </p>
            </div>

            <div
              v-if="identityApplicationsLoading"
              class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3"
              aria-busy="true"
            >
              <USkeleton
                v-for="item in 3"
                :key="item"
                class="h-44 rounded-xl"
              />
            </div>
            <UAlert
              v-else-if="identityApplicationAvailability !== 'available' || identityApplicationsError"
              :color="identityApplicationAvailability === 'access_needed' ? 'warning' : 'error'"
              variant="subtle"
              :title="identityApplicationAvailability === 'access_needed' ? t('starterDashboard.identityApplicationAccessNeeded') : t('starterDashboard.identityUnavailable')"
              :description="identityApplicationAvailability === 'access_needed' ? t('starterDashboard.identityApplicationAccessNeededDescription') : t('starterDashboard.identityApplicationDirectoryUnavailable')"
            />
            <div
              v-else-if="identityApplications.length"
              class="grid items-stretch gap-4 sm:grid-cols-2 xl:grid-cols-3"
            >
              <UPageCard
                v-for="application in identityApplications"
                :key="application.id"
                variant="subtle"
                class="h-full min-w-0"
              >
                <div class="flex h-full min-h-40 flex-col gap-4">
                  <div class="flex items-start justify-between gap-3">
                    <div class="flex min-w-0 items-start gap-3">
                      <span class="flex size-9 shrink-0 items-center justify-center rounded-lg bg-primary/10 text-primary">
                        <UIcon
                          name="i-lucide-panels-top-left"
                          class="size-4"
                        />
                      </span>
                      <div class="min-w-0">
                        <h3 class="truncate font-semibold text-highlighted">
                          {{ application.name }}
                        </h3>
                        <p class="mt-1 truncate text-sm text-muted">
                          {{ application.project.name }}
                        </p>
                      </div>
                    </div>
                    <UBadge
                      :label="application.status"
                      :color="application.status === 'active' ? 'success' : 'neutral'"
                      variant="subtle"
                    />
                  </div>

                  <div class="min-w-0 space-y-1 text-xs text-muted">
                    <p
                      v-if="application.key"
                      class="truncate font-mono"
                    >
                      {{ application.key }}
                    </p>
                    <p class="truncate">
                      {{ application.project.mode }} · {{ application.project.status }}
                    </p>
                    <p
                      v-if="applicationLaunchUrl(application)"
                      class="truncate"
                      :title="applicationLaunchUrl(application) || undefined"
                    >
                      {{ applicationLaunchUrl(application) }}
                    </p>
                  </div>

                  <div class="mt-auto flex flex-wrap items-center gap-2 border-t border-default pt-3">
                    <UButton
                      v-if="applicationLaunchUrl(application)"
                      :to="applicationLaunchUrl(application) || undefined"
                      target="_blank"
                      rel="noopener noreferrer"
                      size="xs"
                      color="primary"
                      variant="soft"
                      trailing-icon="i-lucide-arrow-up-right"
                    >
                      {{ t('starterDashboard.openApplication') }}
                    </UButton>
                    <UButton
                      :to="identityConsoleUrl(application.project.id)"
                      target="_blank"
                      rel="noopener noreferrer"
                      size="xs"
                      color="neutral"
                      variant="ghost"
                      trailing-icon="i-lucide-arrow-up-right"
                    >
                      {{ t('starterDashboard.openIdentityConsole') }}
                    </UButton>
                  </div>
                </div>
              </UPageCard>
            </div>
            <UPageCard
              v-else
              variant="subtle"
            >
              <div class="flex flex-col items-start gap-3 sm:flex-row sm:items-center sm:justify-between">
                <div>
                  <h3 class="font-semibold text-highlighted">
                    {{ t('starterDashboard.noAccessibleApplicationsTitle') }}
                  </h3>
                  <p class="mt-1 text-sm text-muted">
                    {{ t('starterDashboard.noAccessibleApplicationsDescription') }}
                  </p>
                </div>
                <UButton
                  :to="identityConsoleUrl()"
                  target="_blank"
                  rel="noopener noreferrer"
                  color="neutral"
                  variant="outline"
                  trailing-icon="i-lucide-arrow-up-right"
                >
                  {{ t('starterDashboard.openIdentityConsole') }}
                </UButton>
              </div>
            </UPageCard>
          </section>

          <section
            class="space-y-4"
            aria-labelledby="home-identity-projects-heading"
          >
            <div>
              <h2
                id="home-identity-projects-heading"
                class="text-xl font-semibold text-highlighted"
              >
                {{ t('starterDashboard.identityProjects') }}
              </h2>
              <p class="mt-1 text-sm text-muted">
                {{ t('starterDashboard.homeIdentityProjectsDescription') }}
              </p>
            </div>

            <div
              v-if="identityProjectsLoading"
              class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3"
              aria-busy="true"
            >
              <USkeleton
                v-for="item in 3"
                :key="item"
                class="h-44 rounded-xl"
              />
            </div>
            <UAlert
              v-else-if="identityAvailability !== 'available' || identityProjectsError"
              color="warning"
              variant="subtle"
              :title="identityAvailability === 'access_needed' ? t('starterDashboard.identityAccessNeeded') : t('starterDashboard.identityUnavailable')"
              :description="identityAvailability === 'access_needed' ? t('starterDashboard.identityAccessNeededDescription') : t('starterDashboard.identityProjectDirectoryUnavailable')"
            />
            <div
              v-else-if="identityProjects.length"
              class="grid items-stretch gap-4 sm:grid-cols-2 xl:grid-cols-3"
            >
              <UPageCard
                v-for="project in identityProjects"
                :key="project.id"
                variant="subtle"
                class="h-full min-w-0"
              >
                <div class="flex h-full min-h-40 flex-col gap-4">
                  <div class="flex items-start justify-between gap-3">
                    <div class="min-w-0">
                      <h3 class="truncate font-semibold text-highlighted">
                        {{ project.name || project.slug || t('starterDashboard.identityProject') }}
                      </h3>
                      <p
                        v-if="project.slug"
                        class="mt-1 truncate font-mono text-xs text-muted"
                      >
                        {{ project.slug }}
                      </p>
                    </div>
                    <UBadge
                      :label="project.status || 'unknown'"
                      :color="project.status === 'active' ? 'success' : 'neutral'"
                      variant="subtle"
                    />
                  </div>

                  <div class="mt-auto border-t border-default pt-3">
                    <UButton
                      :to="identityConsoleUrl(project.id)"
                      target="_blank"
                      rel="noopener noreferrer"
                      size="xs"
                      color="neutral"
                      variant="ghost"
                      trailing-icon="i-lucide-arrow-up-right"
                    >
                      {{ t('starterDashboard.openIdentityConsole') }}
                    </UButton>
                  </div>
                </div>
              </UPageCard>
            </div>
            <UPageCard
              v-else
              variant="subtle"
            >
              <div class="flex flex-col items-start gap-3 sm:flex-row sm:items-center sm:justify-between">
                <div>
                  <h3 class="font-semibold text-highlighted">
                    {{ t('starterDashboard.noAccessibleIdentityProjectsTitle') }}
                  </h3>
                  <p class="mt-1 text-sm text-muted">
                    {{ t('starterDashboard.noAccessibleIdentityProjectsDescription') }}
                  </p>
                </div>
                <UButton
                  :to="identityConsoleUrl()"
                  target="_blank"
                  rel="noopener noreferrer"
                  color="neutral"
                  variant="outline"
                  trailing-icon="i-lucide-arrow-up-right"
                >
                  {{ t('starterDashboard.openIdentityConsole') }}
                </UButton>
              </div>
            </UPageCard>
          </section>

          <section
            class="space-y-4"
            aria-labelledby="home-workspaces-heading"
          >
            <div class="flex flex-wrap items-end justify-between gap-3">
              <div>
                <h2
                  id="home-workspaces-heading"
                  class="text-xl font-semibold text-highlighted"
                >
                  {{ t('starterDashboard.workspaces') }}
                </h2>
                <p class="mt-1 text-sm text-muted">
                  {{ t('starterDashboard.homeWorkspacesDescription') }}
                </p>
              </div>
              <UButton
                :to="localePath('/dashboard/workspaces')"
                color="neutral"
                variant="ghost"
                trailing-icon="i-lucide-arrow-right"
              >
                {{ t('starterDashboard.viewAllWorkspaces') }}
              </UButton>
            </div>

            <div
              v-if="workspacesLoading"
              class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3"
              aria-busy="true"
            >
              <USkeleton
                v-for="item in 3"
                :key="item"
                class="h-52 rounded-xl"
              />
            </div>
            <UAlert
              v-else-if="workspaceError"
              color="error"
              variant="subtle"
              :title="t('starterDashboard.workspaceServiceUnavailable')"
              :description="t('starterDashboard.workspaceServiceUnavailable')"
            />
            <div
              v-else-if="workspaces.length"
              class="grid items-stretch gap-4 sm:grid-cols-2 xl:grid-cols-3"
            >
              <PlatformWorkspaceCard
                v-for="workspace in workspaces"
                :key="workspace.id"
                :workspace="workspace"
              />
            </div>
            <UPageCard
              v-else
              variant="subtle"
            >
              <div class="flex flex-col items-start gap-3 sm:flex-row sm:items-center sm:justify-between">
                <div>
                  <h3 class="font-semibold text-highlighted">
                    {{ t('starterDashboard.noWorkspacesTitle') }}
                  </h3>
                  <p class="mt-1 text-sm text-muted">
                    {{ t('starterDashboard.noWorkspacesDescription') }}
                  </p>
                </div>
                <UButton :to="localePath('/dashboard/workspaces')">
                  {{ t('starterDashboard.viewAllWorkspaces') }}
                </UButton>
              </div>
            </UPageCard>
          </section>
        </div>
      </UContainer>
    </template>
  </UDashboardPanel>
</template>
