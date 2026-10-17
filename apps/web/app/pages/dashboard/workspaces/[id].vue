<script setup lang="ts">
import type { PlatformIdentityProject, PlatformWorkspace } from '~~/shared/types/platform'

definePageMeta({ layout: 'dashboard', middleware: ['auth'], pageTransition: false })

const route = useRoute()
const localePath = useLocalePath()
const { t } = useI18n()
const runtimeConfig = useRuntimeConfig()
const client = usePlatformClient()
const workspace = ref<PlatformWorkspace | null>(null)
const workspaces = ref<PlatformWorkspace[]>([])
const identityProjects = ref<PlatformIdentityProject[]>([])
const availableIdentityProjects = ref<PlatformIdentityProject[]>([])
const identityAvailability = ref('available')
const availableIdentityAvailability = ref('available')
const pending = ref(true)
const saving = ref(false)
const createWorkspaceOpen = ref(false)
const errorMessage = ref('')
const linkIdentityOpen = ref(false)
const selectedIdentityProjectId = ref('')

const workspaceId = computed(() => String(route.params.id || ''))
const isSettingsRoute = computed(() => route.path.replace(/\/$/, '').endsWith('/settings'))
const createWorkspaceOption = '__create_workspace__'
const workspaceSelectItems = computed(() => {
  const availableWorkspaces = workspaces.value.map(item => ({ label: item.name, value: item.id }))
  if (!availableWorkspaces.length) {
    availableWorkspaces.push({ label: workspace.value?.name || t('starterDashboard.workspace'), value: workspaceId.value })
  }

  return [
    ...availableWorkspaces,
    { label: t('starterDashboard.createWorkspace'), value: createWorkspaceOption }
  ]
})
const canManage = computed(() => workspace.value?.role === 'owner' || workspace.value?.role === 'admin')
const identityItems = computed(() => availableIdentityProjects.value.map(project => ({ label: project.name || project.slug || project.id, value: project.id })))
const identityAvailabilityTitle = computed(() => identityAvailability.value === 'unavailable' ? t('starterDashboard.identityUnavailable') : t('starterDashboard.identityAccessNeeded'))
const identityAvailabilityDescription = computed(() => identityAvailability.value === 'unavailable' ? t('starterDashboard.identityUnavailableDescription') : t('starterDashboard.identityAccessNeededDescription'))

const identityConsoleUrl = (projectId: string) => new URL(`/admin/projects/${projectId}`, runtimeConfig.public.identityAuthUrl).toString()

let activeRequest = 0
const load = async () => {
  const requestId = ++activeRequest
  pending.value = true
  errorMessage.value = ''
  workspace.value = null

  try {
    const [overview, workspaceCollection] = await Promise.all([
      client.getWorkspaceOverview(workspaceId.value),
      client.list()
    ])
    if (requestId !== activeRequest) return

    workspace.value = overview.workspace
    identityProjects.value = overview.resources.identity_projects.projects
    identityAvailability.value = overview.resources.identity_projects.availability
    workspaces.value = workspaceCollection.workspaces
    if (requestId !== activeRequest) return
  } catch (error) {
    if (requestId !== activeRequest) return
    errorMessage.value = error instanceof Error ? error.message : t('starterDashboard.workspaceServiceUnavailable')
  } finally {
    if (requestId === activeRequest) pending.value = false
  }
}

const switchWorkspace = async (value: string) => {
  if (value === createWorkspaceOption) {
    createWorkspaceOpen.value = true
    return
  }

  if (!value || value === workspaceId.value) return
  await navigateTo(localePath(`/dashboard/workspaces/${value}`))
}

const openCreatedWorkspace = async (createdWorkspace: PlatformWorkspace) => {
  await navigateTo(localePath(`/dashboard/workspaces/${createdWorkspace.id}`))
}

const reloadOverview = async () => {
  const overview = await client.getWorkspaceOverview(workspaceId.value)
  workspace.value = overview.workspace
  identityProjects.value = overview.resources.identity_projects.projects
  identityAvailability.value = overview.resources.identity_projects.availability
}

const loadAvailableIdentityProjects = async () => {
  try {
    const result = await client.availableIdentityProjects(workspaceId.value)
    availableIdentityAvailability.value = result.availability
    availableIdentityProjects.value = result.projects
  } catch {
    availableIdentityAvailability.value = 'unavailable'
    availableIdentityProjects.value = []
  }
}

const linkIdentityProject = async () => {
  if (!workspaceId.value || !selectedIdentityProjectId.value) return
  saving.value = true
  errorMessage.value = ''
  try {
    await client.linkIdentityProject(workspaceId.value, selectedIdentityProjectId.value)
    linkIdentityOpen.value = false
    selectedIdentityProjectId.value = ''
    await Promise.all([reloadOverview(), loadAvailableIdentityProjects()])
  } catch (error) {
    errorMessage.value = error instanceof Error ? error.message : t('starterDashboard.identityLinkFailed')
  } finally {
    saving.value = false
  }
}

const unlinkIdentityProject = async (identityProjectId: string) => {
  saving.value = true
  errorMessage.value = ''
  try {
    await client.unlinkIdentityProject(workspaceId.value, identityProjectId)
    await Promise.all([reloadOverview(), loadAvailableIdentityProjects()])
  } catch (error) {
    errorMessage.value = error instanceof Error ? error.message : t('starterDashboard.identityUnlinkFailed')
  } finally {
    saving.value = false
  }
}

watch(linkIdentityOpen, (open) => {
  if (open) void loadAvailableIdentityProjects()
})
onMounted(() => {
  if (!isSettingsRoute.value) void load()
})
watch([workspaceId, isSettingsRoute], ([, settingsRoute]) => {
  if (!settingsRoute) void load()
})

useSeoMeta({
  title: () => `${workspace.value?.name || t('starterDashboard.workspace')} · ${t('starterDashboard.brand')}`,
  robots: 'noindex, nofollow'
})
</script>

<template>
  <NuxtPage v-if="isSettingsRoute" />
  <UDashboardPanel
    v-else
    id="starter-workspace"
    class="min-w-0 flex-1"
  >
    <template #header>
      <DashboardPageNavbar>
        <template #title>
          <USelect
            :model-value="workspaceId"
            :items="workspaceSelectItems"
            :loading="pending"
            :aria-label="t('starterDashboard.switchWorkspace')"
            icon="i-lucide-panels-top-left"
            size="md"
            class="min-w-0 max-w-64"
            @update:model-value="switchWorkspace"
          />
        </template>
        <template #right>
          <UBadge
            v-if="workspace?.is_default"
            :label="t('starterDashboard.defaultWorkspace')"
            color="primary"
            variant="subtle"
          />
          <UBadge
            v-if="workspace"
            :label="workspace.status"
            :color="workspace.status === 'active' ? 'success' : 'neutral'"
            variant="subtle"
          />
        </template>
      </DashboardPageNavbar>
    </template>

    <template #body>
      <UContainer class="w-full max-w-none px-4 py-8 sm:px-6 sm:py-10 lg:px-8">
        <div class="mx-auto w-full max-w-5xl space-y-7">
          <div
            v-if="pending"
            class="space-y-7"
            aria-busy="true"
          >
            <header class="space-y-3">
              <USkeleton class="h-9 w-64 max-w-full" />
              <USkeleton class="h-4 w-80 max-w-full" />
            </header>
            <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
              <USkeleton
                v-for="item in 3"
                :key="item"
                class="h-52 rounded-xl"
              />
            </div>
          </div>
          <UAlert
            v-else-if="errorMessage"
            color="error"
            variant="subtle"
            :title="t('starterDashboard.workspaceServiceUnavailable')"
            :description="errorMessage"
          />
          <template v-else-if="workspace">
            <header class="flex items-start justify-between gap-4">
              <div class="min-w-0">
                <h1 class="text-3xl font-semibold tracking-tight text-highlighted sm:text-4xl">
                  {{ workspace.name }}
                </h1>
                <p class="mt-2 max-w-3xl text-sm leading-6 text-muted">
                  {{ t('starterDashboard.workspaceOverviewDescription') }}
                </p>
              </div>
              <UTooltip
                v-if="canManage"
                :text="t('starterDashboard.configureWorkspace')"
              >
                <UButton
                  :to="localePath(`/dashboard/workspaces/${workspace.id}/settings`)"
                  color="neutral"
                  variant="ghost"
                  icon="i-lucide-settings-2"
                  :aria-label="t('starterDashboard.configureWorkspace')"
                />
              </UTooltip>
            </header>

            <section class="space-y-4">
              <div class="flex flex-wrap items-end justify-between gap-3">
                <div>
                  <h2 class="text-xl font-semibold text-highlighted">
                    {{ t('starterDashboard.identityProjects') }}
                  </h2>
                  <p class="mt-1 text-sm text-muted">
                    {{ t('starterDashboard.identityProjectsDescription') }}
                  </p>
                </div>
                <UButton
                  v-if="canManage"
                  icon="i-lucide-plus"
                  @click="linkIdentityOpen = !linkIdentityOpen"
                >
                  {{ t('starterDashboard.addIdentityProject') }}
                </UButton>
              </div>

              <UAlert
                v-if="identityAvailability !== 'available' && identityProjects.length === 0"
                color="warning"
                variant="subtle"
                :title="identityAvailabilityTitle"
                :description="identityAvailabilityDescription"
              />

              <UPageCard
                v-if="linkIdentityOpen && canManage"
                :title="t('starterDashboard.addIdentityProject')"
                variant="subtle"
              >
                <form
                  class="flex flex-col gap-3 sm:flex-row sm:items-end"
                  @submit.prevent="linkIdentityProject"
                >
                  <UFormField
                    :label="t('starterDashboard.selectIdentityProject')"
                    class="min-w-0 flex-1"
                  >
                    <USelect
                      v-model="selectedIdentityProjectId"
                      :items="identityItems"
                      :placeholder="t('starterDashboard.selectIdentityProject')"
                      class="w-full"
                    />
                  </UFormField>
                  <UButton
                    type="submit"
                    :loading="saving"
                    :disabled="!selectedIdentityProjectId"
                  >
                    {{ t('starterDashboard.linkProject') }}
                  </UButton>
                </form>
                <p
                  v-if="availableIdentityAvailability !== 'available'"
                  class="mt-3 text-sm text-muted"
                >
                  {{ availableIdentityAvailability === 'unavailable' ? t('starterDashboard.identityUnavailableDescription') : t('starterDashboard.identityAccessNeededDescription') }}
                </p>
                <p
                  v-else-if="availableIdentityProjects.length === 0"
                  class="mt-3 text-sm text-muted"
                >
                  {{ t('starterDashboard.noAvailableIdentityProjects') }}
                </p>
              </UPageCard>

              <div
                v-if="identityProjects.length"
                class="grid items-stretch gap-4 sm:grid-cols-2 xl:grid-cols-3"
              >
                <UPageCard
                  v-for="project in identityProjects"
                  :key="project.id"
                  variant="subtle"
                  class="h-full min-w-0"
                >
                  <div class="flex h-full min-h-48 flex-col">
                    <div class="flex items-start justify-between gap-3">
                      <div class="min-w-0">
                        <h3 class="truncate font-semibold text-highlighted">
                          {{ project.identity_project?.name || project.name || t('starterDashboard.identityProject') }}
                        </h3>
                        <p
                          v-if="project.availability === 'available'"
                          class="mt-1 truncate font-mono text-xs text-muted"
                        >
                          {{ project.identity_project?.slug || project.slug }}
                        </p>
                      </div>
                      <UBadge
                        v-if="project.availability === 'available'"
                        :label="project.identity_project?.status || project.status || 'unknown'"
                        :color="(project.identity_project?.status || project.status) === 'active' ? 'success' : 'neutral'"
                        variant="subtle"
                      />
                    </div>

                    <div
                      v-if="project.availability === 'available'"
                      class="mt-4 flex flex-1 flex-col gap-3"
                    >
                      <div class="flex flex-wrap gap-2 text-xs text-muted">
                        <UBadge
                          color="neutral"
                          variant="subtle"
                        >
                          {{ t('starterDashboard.identityClientsCount', project.clients?.length ?? 0) }}
                        </UBadge>
                        <UBadge
                          color="neutral"
                          variant="subtle"
                        >
                          {{ t('starterDashboard.identityApplicationsCount', project.applications?.length ?? 0) }}
                        </UBadge>
                      </div>
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
                    <UAlert
                      v-else
                      class="mt-4"
                      color="warning"
                      variant="subtle"
                      :title="project.availability === 'unavailable' ? t('starterDashboard.identityUnavailable') : t('starterDashboard.identityAccessNeeded')"
                      :description="project.availability === 'unavailable' ? t('starterDashboard.identityUnavailableDescription') : t('starterDashboard.identityAccessNeededDescription')"
                    />
                    <div class="mt-4 border-t border-default pt-3">
                      <UButton
                        v-if="canManage"
                        size="xs"
                        color="neutral"
                        variant="ghost"
                        :loading="saving"
                        @click="unlinkIdentityProject(project.id)"
                      >
                        {{ t('starterDashboard.unlinkIdentityProject') }}
                      </UButton>
                    </div>
                  </div>
                </UPageCard>
              </div>
              <div
                v-else-if="identityAvailability === 'available'"
                class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3"
              >
                <UPageCard
                  variant="subtle"
                  class="min-h-52 border-dashed"
                >
                  <div class="flex h-full min-h-44 flex-col items-start justify-center gap-3">
                    <span class="grid size-10 place-items-center rounded-xl bg-primary/10 text-primary ring-1 ring-inset ring-primary/10">
                      <UIcon
                        name="i-lucide-panels-top-left"
                        class="size-[18px]"
                      />
                    </span>
                    <div>
                      <h3 class="font-semibold text-highlighted">
                        {{ t('starterDashboard.noIdentityProjectsTitle') }}
                      </h3>
                      <p class="mt-1 text-sm text-muted">
                        {{ t('starterDashboard.noIdentityProjectsDescription') }}
                      </p>
                    </div>
                  </div>
                </UPageCard>
              </div>
            </section>
          </template>
          <WorkspaceCreateModal
            v-model:open="createWorkspaceOpen"
            @created="openCreatedWorkspace"
          />
        </div>
      </UContainer>
    </template>
  </UDashboardPanel>
</template>
