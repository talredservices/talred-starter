<script setup lang="ts">
import type { PlatformWorkspace } from '~~/shared/types/platform'

definePageMeta({ layout: 'dashboard', middleware: ['auth'], pageTransition: false })

const route = useRoute()
const localePath = useLocalePath()
const { t } = useI18n()
const client = usePlatformClient()
const workspace = ref<PlatformWorkspace | null>(null)
const pending = ref(true)
const errorMessage = ref('')

onMounted(async () => {
  try {
    const response = await client.acceptWorkspaceInvitation(String(route.params.token || ''))
    workspace.value = response.workspace
  } catch (error) {
    errorMessage.value = error instanceof Error ? error.message : t('starterDashboard.invitationAcceptFailed')
  } finally {
    pending.value = false
  }
})

useSeoMeta({ title: () => t('starterDashboard.acceptInvitation'), robots: 'noindex, nofollow' })
</script>

<template>
  <UDashboardPanel
    id="starter-workspace-invitation"
    class="min-w-0 flex-1"
  >
    <template #header>
      <DashboardPageNavbar>
        <template #title>
          <div class="flex items-center gap-2 font-semibold text-highlighted">
            <UIcon
              name="i-lucide-mail-check"
              class="size-4 text-primary"
            />
            {{ t('starterDashboard.acceptInvitation') }}
          </div>
        </template>
      </DashboardPageNavbar>
    </template>
    <template #body>
      <UContainer class="w-full max-w-none px-4 py-10 sm:px-6 lg:px-8">
        <div class="mx-auto w-full max-w-xl">
          <UPageCard
            v-if="pending"
            variant="subtle"
            aria-busy="true"
          >
            <USkeleton class="h-8 w-56" />
            <USkeleton class="mt-4 h-4 w-full" />
          </UPageCard>
          <UAlert
            v-else-if="errorMessage"
            color="error"
            variant="subtle"
            :title="t('starterDashboard.invitationInvalid')"
            :description="errorMessage"
          />
          <UPageCard
            v-else-if="workspace"
            :title="t('starterDashboard.invitationAcceptedTitle')"
            :description="t('starterDashboard.invitationAcceptedDescription', { workspace: workspace.name })"
            icon="i-lucide-check-circle-2"
            variant="subtle"
          >
            <UButton
              class="mt-4"
              :to="localePath(`/dashboard/workspaces/${workspace.id}`)"
              icon="i-lucide-panels-top-left"
            >
              {{ t('starterDashboard.openWorkspace') }}
            </UButton>
          </UPageCard>
        </div>
      </UContainer>
    </template>
  </UDashboardPanel>
</template>
