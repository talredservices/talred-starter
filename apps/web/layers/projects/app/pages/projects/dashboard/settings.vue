<script setup lang="ts">
import { identityUserInitials } from '~/utils/identity-user'

// Dashboard panels render fragments and cannot participate in page transitions.
definePageMeta({ layout: 'projects-dashboard', middleware: ['projects-auth'], pageTransition: false })

const { t } = useI18n()
const session = useProjectsIdentitySession()
const identityAccount = useTalredIdentity('projects')
const isTemporaryAccount = computed(() => Boolean(session.user.value?.isTemporary))

const user = computed(() => {
  const account = session.user.value as {
    name?: string
    username?: string
    email?: string
    avatar?: string
  } | null
  const name = account?.name || account?.username || t('userMenu.authenticatedUser')

  return {
    name,
    description: account?.email || '',
    avatar: { text: identityUserInitials(name), alt: name }
  }
})

useSeoMeta({
  title: () => `${t('projects.settings.title')} · ${t('projects.brand')}`,
  robots: 'noindex, nofollow'
})
</script>

<template>
  <UDashboardPanel
    id="projects-settings"
    class="min-w-0 flex-1"
  >
    <template #header>
      <DashboardPageNavbar :title="t('userMenu.settings')">
        <template #title>
          <span class="flex min-w-0 items-center gap-2 font-semibold text-highlighted">
            <UIcon
              name="i-lucide-settings-2"
              class="size-4 shrink-0 text-primary"
              aria-hidden="true"
            />
            <span class="truncate">{{ t('userMenu.settings') }}</span>
          </span>
        </template>
      </DashboardPageNavbar>
    </template>

    <template #body>
      <UContainer class="py-8 sm:py-10">
        <div class="w-full max-w-4xl space-y-6">
          <header>
            <h1 class="text-2xl font-semibold tracking-tight text-highlighted sm:text-3xl">
              {{ t('projects.settings.title') }}
            </h1>
            <p class="mt-2 max-w-2xl text-sm leading-6 text-muted">
              {{ t('projects.settings.description') }}
            </p>
          </header>

          <UPageCard
            :title="t('projects.settings.account')"
            :description="t('projects.settings.accountDescription')"
            icon="i-lucide-shield-user"
            variant="subtle"
          >
            <div class="flex flex-col gap-5 p-1 sm:flex-row sm:items-center sm:justify-between">
              <UUser
                v-bind="user"
                size="xl"
              />

              <UBadge
                v-if="isTemporaryAccount"
                :label="t('projects.settings.temporaryDemo')"
                icon="i-lucide-flask-conical"
                color="warning"
                variant="soft"
                class="self-start sm:self-center"
              />
              <UButton
                v-else
                :label="t('projects.settings.manageAccount')"
                icon="i-lucide-external-link"
                color="neutral"
                variant="soft"
                trailing
                class="self-start sm:self-center"
                @click="identityAccount.openAccountPortal('profile')"
              />
            </div>

            <UAlert
              v-if="isTemporaryAccount"
              :title="t('projects.settings.accountManagementUnavailableTitle')"
              :description="t('projects.settings.accountManagementUnavailableDescription')"
              icon="i-lucide-shield-alert"
              color="warning"
              variant="subtle"
              class="mt-4"
            />
          </UPageCard>
        </div>
      </UContainer>
    </template>
  </UDashboardPanel>
</template>
