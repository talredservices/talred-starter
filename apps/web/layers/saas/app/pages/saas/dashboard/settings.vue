<script setup lang="ts">
import { identityUserInitials } from '~/utils/identity-user'

// Dashboard panels render fragments and cannot participate in page transitions.
definePageMeta({ layout: 'saas-dashboard', middleware: ['saas-dashboard-auth'], pageTransition: false })

const { t } = useI18n()
const session = useSaasIdentitySession()
const identityAccount = useTalredIdentity('saas')
const isTemporaryAccount = computed(() => Boolean(session.user.value?.isTemporary))

const user = computed(() => {
  const account = session.user.value
  const name = account?.name || account?.username || t('userMenu.authenticatedUser')

  return {
    name,
    description: account?.email || '',
    avatar: { text: identityUserInitials(name), alt: name }
  }
})

useSeoMeta({
  title: () => `${t('dashboard.settingsTitle')} · ${t('dashboard.brand')}`,
  robots: 'noindex, nofollow'
})
</script>

<template>
  <UDashboardPanel
    id="saas-settings"
    class="min-w-0 flex-1"
  >
    <template #header>
      <DashboardPageNavbar :title="t('dashboard.settingsTitle')" />
    </template>

    <template #body>
      <UContainer class="w-full py-8 sm:py-10">
        <div class="mx-auto w-full max-w-4xl space-y-6">
          <header>
            <h1 class="text-2xl font-semibold tracking-tight text-highlighted sm:text-3xl">
              {{ t('dashboard.settingsTitle') }}
            </h1>
            <p class="mt-2 max-w-2xl text-sm leading-6 text-muted">
              {{ t('dashboard.settingsDescription') }}
            </p>
          </header>

          <UPageCard
            :title="t('dashboard.account')"
            :description="t('dashboard.accountDescription')"
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
                :label="t('dashboard.temporaryDemo')"
                icon="i-lucide-flask-conical"
                color="warning"
                variant="soft"
                class="self-start sm:self-center"
              />
              <UButton
                v-else
                :label="t('dashboard.manageAccount')"
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
              :title="t('dashboard.accountManagementUnavailableTitle')"
              :description="t('dashboard.accountManagementUnavailableDescription')"
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
