<script setup lang="ts">
import type { PlatformWorkspace } from '~~/shared/types/platform'

definePageMeta({ layout: 'dashboard', middleware: ['auth'], pageTransition: false })

const { t } = useI18n()
const client = usePlatformClient()
const workspaces = ref<PlatformWorkspace[]>([])
const loading = ref(true)
const errorMessage = ref('')
const createOpen = ref(false)

async function loadWorkspaces() {
  loading.value = true
  errorMessage.value = ''
  try {
    let collection = await client.list()
    if (!collection.workspaces.some(workspace => workspace.is_default)) {
      await client.ensureDefault()
      collection = await client.list({ force: true })
    }
    workspaces.value = collection.workspaces
  } catch (error) {
    errorMessage.value = error instanceof Error ? error.message : t('starterDashboard.workspaceServiceUnavailable')
  } finally {
    loading.value = false
  }
}

function addWorkspace(workspace: PlatformWorkspace) {
  workspaces.value = [...workspaces.value, workspace]
}

onMounted(loadWorkspaces)

useSeoMeta({
  title: () => `${t('starterDashboard.workspaces')} · ${t('starterDashboard.brand')}`,
  robots: 'noindex, nofollow'
})
</script>

<template>
  <UDashboardPanel id="starter-workspaces">
    <template #header>
      <DashboardPageNavbar>
        <template #title>
          <div class="flex items-center gap-2 font-semibold text-highlighted">
            <UIcon
              name="i-lucide-panels-top-left"
              class="size-4 text-primary"
            />
            {{ t('starterDashboard.workspaces') }}
          </div>
        </template>
        <template #right>
          <UButton
            :label="t('starterDashboard.createWorkspace')"
            icon="i-lucide-plus"
            @click="createOpen = true"
          />
        </template>
      </DashboardPageNavbar>
    </template>

    <template #body>
      <UContainer class="w-full max-w-none px-4 py-6 sm:px-6 lg:px-8">
        <div class="mx-auto w-full max-w-6xl space-y-7">
          <header>
            <h1 class="text-3xl font-semibold tracking-tight text-highlighted">
              {{ t('starterDashboard.workspaceDirectoryTitle') }}
            </h1>
            <p class="mt-2 max-w-2xl text-sm leading-6 text-muted">
              {{ t('starterDashboard.workspaceDirectoryDescription') }}
            </p>
          </header>

          <UAlert
            v-if="errorMessage"
            color="error"
            variant="subtle"
            :title="t('starterDashboard.workspaceServiceUnavailable')"
            :description="errorMessage"
          />

          <div
            v-if="loading"
            class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3"
            aria-busy="true"
          >
            <USkeleton
              v-for="item in 3"
              :key="item"
              class="h-52 rounded-xl"
            />
          </div>
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
            v-else-if="!errorMessage"
            variant="subtle"
          >
            <div class="flex flex-col items-center gap-3 py-8 text-center">
              <span class="grid size-12 place-items-center rounded-xl bg-primary/10 text-primary">
                <UIcon
                  name="i-lucide-panels-top-left"
                  class="size-6"
                />
              </span>
              <div>
                <h2 class="font-semibold text-highlighted">
                  {{ t('starterDashboard.noWorkspacesTitle') }}
                </h2>
                <p class="mt-1 text-sm text-muted">
                  {{ t('starterDashboard.noWorkspacesDescription') }}
                </p>
              </div>
              <UButton
                :label="t('starterDashboard.createWorkspace')"
                icon="i-lucide-plus"
                @click="createOpen = true"
              />
            </div>
          </UPageCard>

          <WorkspaceCreateModal
            v-model:open="createOpen"
            @created="addWorkspace"
          />
        </div>
      </UContainer>
    </template>
  </UDashboardPanel>
</template>
