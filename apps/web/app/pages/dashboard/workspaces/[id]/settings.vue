<script setup lang="ts">
import type { PlatformWorkspace, PlatformWorkspaceInvitation, PlatformWorkspaceMember } from '~~/shared/types/platform'

definePageMeta({ layout: 'dashboard', middleware: ['auth'], pageTransition: false })

const route = useRoute()
const localePath = useLocalePath()
const { t } = useI18n()
const client = usePlatformClient()
const workspace = ref<PlatformWorkspace | null>(null)
const members = ref<PlatformWorkspaceMember[]>([])
const invitations = ref<PlatformWorkspaceInvitation[]>([])
const pending = ref(true)
const saving = ref(false)
const errorMessage = ref('')
const workspaceName = ref('')
const inviteEmail = ref('')
const inviteRole = ref<'admin' | 'member'>('member')
const transferAccountId = ref('')
const invitationUrl = ref('')
const invitationCopied = ref(false)
const workspaceId = computed(() => String(route.params.id || ''))
const canAdminister = computed(() => workspace.value?.role === 'owner' || workspace.value?.role === 'admin')
const transferItems = computed(() => members.value.filter(member => member.role !== 'owner').map(member => ({ label: member.account_id, value: member.account_id })))

const load = async () => {
  pending.value = true
  errorMessage.value = ''
  try {
    const [workspaceResponse, memberCollection] = await Promise.all([
      client.getWorkspace(workspaceId.value),
      client.listWorkspaceMembers(workspaceId.value)
    ])
    workspace.value = workspaceResponse.workspace
    workspaceName.value = workspaceResponse.workspace.name
    members.value = memberCollection.members
    invitations.value = canAdminister.value ? (await client.listWorkspaceInvitations(workspaceId.value)).invitations : []
  } catch (error) {
    errorMessage.value = error instanceof Error ? error.message : t('starterDashboard.workspaceServiceUnavailable')
  } finally {
    pending.value = false
  }
}

const saveWorkspace = async () => {
  if (!workspaceName.value.trim()) return
  saving.value = true
  errorMessage.value = ''
  try {
    const result = await client.updateWorkspace(workspaceId.value, { name: workspaceName.value.trim() })
    workspace.value = { ...workspace.value!, ...result.workspace }
  } catch (error) {
    errorMessage.value = error instanceof Error ? error.message : t('starterDashboard.workspaceUpdateFailed')
  } finally {
    saving.value = false
  }
}

const invite = async () => {
  if (!inviteEmail.value.trim()) return
  saving.value = true
  errorMessage.value = ''
  invitationCopied.value = false
  try {
    const created = await client.createWorkspaceInvitation(workspaceId.value, { email: inviteEmail.value.trim(), role: inviteRole.value })
    const acceptPath = localePath(`/dashboard/invitations/${created.invite_token}`)
    const authPath = localePath(created.identity_invitation_token ? '/auth/signup' : '/auth/login')
    const url = new URL(authPath, window.location.origin)
    url.searchParams.set('redirect', acceptPath)
    if (created.identity_invitation_token) url.searchParams.set('token', created.identity_invitation_token)
    invitationUrl.value = url.toString()
    inviteEmail.value = ''
    invitations.value = (await client.listWorkspaceInvitations(workspaceId.value)).invitations
  } catch (error) {
    errorMessage.value = error instanceof Error ? error.message : t('starterDashboard.invitationCreateFailed')
  } finally {
    saving.value = false
  }
}

const copyInvitation = async () => {
  if (!invitationUrl.value) return
  await navigator.clipboard.writeText(invitationUrl.value)
  invitationCopied.value = true
}

const changeMemberRole = async (member: PlatformWorkspaceMember, role: 'admin' | 'member') => {
  saving.value = true
  errorMessage.value = ''
  try {
    await client.updateWorkspaceMember(workspaceId.value, member.account_id, role)
    await load()
  } catch (error) {
    errorMessage.value = error instanceof Error ? error.message : t('starterDashboard.workspaceMemberUpdateFailed')
  } finally {
    saving.value = false
  }
}

const handleMemberRoleChange = (member: PlatformWorkspaceMember, role: string) => {
  if (role === 'admin' || role === 'member') void changeMemberRole(member, role)
}

const removeMember = async (member: PlatformWorkspaceMember) => {
  saving.value = true
  errorMessage.value = ''
  try {
    await client.removeWorkspaceMember(workspaceId.value, member.account_id)
    await load()
  } catch (error) {
    errorMessage.value = error instanceof Error ? error.message : t('starterDashboard.workspaceMemberRemoveFailed')
  } finally {
    saving.value = false
  }
}

const revokeInvitation = async (invitation: PlatformWorkspaceInvitation) => {
  saving.value = true
  errorMessage.value = ''
  try {
    const result = await client.revokeWorkspaceInvitation(workspaceId.value, invitation.id)
    await load()
    if (!result.identity_login_invitation_revoked) errorMessage.value = t('starterDashboard.loginInviteRevokePending')
  } catch (error) {
    errorMessage.value = error instanceof Error ? error.message : t('starterDashboard.invitationRevokeFailed')
  } finally {
    saving.value = false
  }
}

const transferOwnership = async () => {
  if (!transferAccountId.value) return
  saving.value = true
  errorMessage.value = ''
  try {
    await client.transferWorkspaceOwnership(workspaceId.value, transferAccountId.value)
    transferAccountId.value = ''
    await load()
  } catch (error) {
    errorMessage.value = error instanceof Error ? error.message : t('starterDashboard.workspaceOwnershipTransferFailed')
  } finally {
    saving.value = false
  }
}

onMounted(load)
watch(workspaceId, () => void load())
useSeoMeta({ title: () => `${workspace.value?.name || t('starterDashboard.workspaceSettings')} · ${t('starterDashboard.brand')}`, robots: 'noindex, nofollow' })
</script>

<template>
  <UDashboardPanel
    id="starter-workspace-settings"
    class="min-w-0 flex-1"
  >
    <template #header>
      <DashboardPageNavbar>
        <template #title>
          <div class="flex items-center gap-2 font-semibold text-highlighted">
            <UIcon
              name="i-lucide-settings-2"
              class="size-4 text-primary"
            />
            {{ workspace?.name || t('starterDashboard.workspaceSettings') }}
          </div>
        </template>
      </DashboardPageNavbar>
    </template>
    <template #body>
      <UContainer class="w-full max-w-none px-4 py-8 sm:px-6 sm:py-10 lg:px-8">
        <div class="mx-auto w-full max-w-4xl space-y-6">
          <UButton
            :to="localePath(`/dashboard/workspaces/${workspaceId}`)"
            icon="i-lucide-arrow-left"
            color="neutral"
            variant="ghost"
          >
            {{ t('starterDashboard.backToWorkspace') }}
          </UButton>
          <div
            v-if="pending"
            class="space-y-4"
            aria-busy="true"
          >
            <USkeleton class="h-9 w-64" />
            <USkeleton class="h-48 rounded-xl" />
          </div>
          <UAlert
            v-else-if="errorMessage && !workspace"
            color="error"
            variant="subtle"
            :title="t('starterDashboard.workspaceServiceUnavailable')"
            :description="errorMessage"
          />
          <template v-else-if="workspace">
            <header>
              <h1 class="text-3xl font-semibold tracking-tight text-highlighted">
                {{ t('starterDashboard.workspaceSettings') }}
              </h1>
              <p class="mt-2 text-sm text-muted">
                {{ workspace.name }}
              </p>
            </header>

            <UAlert
              v-if="errorMessage"
              color="error"
              variant="subtle"
              :title="t('starterDashboard.workspaceSettings')"
              :description="errorMessage"
            />

            <UPageCard
              :title="t('starterDashboard.workspaceSettings')"
              variant="subtle"
            >
              <form
                class="flex flex-col gap-3 sm:flex-row sm:items-end"
                @submit.prevent="saveWorkspace"
              >
                <UFormField
                  :label="t('starterDashboard.workspaceName')"
                  class="min-w-0 flex-1"
                >
                  <UInput
                    v-model="workspaceName"
                    class="w-full"
                    name="workspaceName"
                    autocomplete="organization"
                    :disabled="!canAdminister"
                    required
                    maxlength="120"
                  />
                </UFormField>
                <UButton
                  v-if="canAdminister"
                  type="submit"
                  :loading="saving"
                  :disabled="!workspaceName.trim() || workspaceName.trim() === workspace.name"
                >
                  {{ t('starterDashboard.save') }}
                </UButton>
              </form>
            </UPageCard>

            <UPageCard
              :title="t('starterDashboard.workspaceMembers')"
              :description="t('starterDashboard.workspaceMembersDescription')"
              variant="subtle"
            >
              <div class="divide-y divide-default">
                <div
                  v-for="member in members"
                  :key="member.account_id"
                  class="flex flex-wrap items-center justify-between gap-3 py-3 first:pt-0 last:pb-0"
                >
                  <div class="min-w-0">
                    <p class="truncate font-mono text-xs text-highlighted">
                      {{ member.account_id }}
                    </p>
                    <p class="mt-1 text-xs text-muted">
                      {{ member.joined_at }}
                    </p>
                  </div>
                  <div class="flex items-center gap-2">
                    <UBadge
                      :label="t(`starterDashboard.workspaceRoleValue.${member.role}`)"
                      color="neutral"
                      variant="subtle"
                    />
                    <USelect
                      v-if="canAdminister && member.role !== 'owner'"
                      :model-value="member.role"
                      :items="[{ label: t('starterDashboard.workspaceRoleValue.admin'), value: 'admin' }, { label: t('starterDashboard.workspaceRoleValue.member'), value: 'member' }]"
                      class="w-36"
                      :aria-label="t('starterDashboard.changeMemberRole')"
                      @update:model-value="handleMemberRoleChange(member, $event)"
                    />
                    <UButton
                      v-if="canAdminister && member.role !== 'owner'"
                      size="xs"
                      color="error"
                      variant="ghost"
                      :loading="saving"
                      @click="removeMember(member)"
                    >
                      {{ t('starterDashboard.removeMember') }}
                    </UButton>
                  </div>
                </div>
              </div>
              <div
                v-if="workspace.role === 'owner' && transferItems.length"
                class="mt-5 flex flex-col gap-3 border-t border-default pt-4 sm:flex-row sm:items-end"
              >
                <UFormField
                  :label="t('starterDashboard.newWorkspaceOwner')"
                  class="min-w-0 flex-1"
                >
                  <USelect
                    v-model="transferAccountId"
                    :items="transferItems"
                    :placeholder="t('starterDashboard.newWorkspaceOwner')"
                    class="w-full"
                  />
                </UFormField>
                <UButton
                  color="neutral"
                  variant="outline"
                  :loading="saving"
                  :disabled="!transferAccountId"
                  @click="transferOwnership"
                >
                  {{ t('starterDashboard.transferOwnership') }}
                </UButton>
              </div>
            </UPageCard>

            <UPageCard
              v-if="canAdminister"
              :title="t('starterDashboard.workspaceInvitations')"
              :description="t('starterDashboard.workspaceInvitationsDescription')"
              variant="subtle"
            >
              <form
                class="grid gap-3 sm:grid-cols-[1fr_10rem_auto] sm:items-end"
                @submit.prevent="invite"
              >
                <UFormField :label="t('starterDashboard.inviteEmail')">
                  <UInput
                    v-model="inviteEmail"
                    type="email"
                    name="email"
                    autocomplete="email"
                    spellcheck="false"
                    required
                    maxlength="254"
                    :placeholder="t('starterDashboard.inviteEmail')"
                    class="w-full"
                  />
                </UFormField>
                <UFormField :label="t('starterDashboard.invitationRole')">
                  <USelect
                    v-model="inviteRole"
                    :items="[{ label: t('starterDashboard.workspaceRoleValue.admin'), value: 'admin' }, { label: t('starterDashboard.workspaceRoleValue.member'), value: 'member' }]"
                    class="w-full"
                  />
                </UFormField>
                <UButton
                  type="submit"
                  :loading="saving"
                  :disabled="!inviteEmail.trim()"
                >
                  {{ t('starterDashboard.sendInvitation') }}
                </UButton>
              </form>

              <div
                v-if="invitationUrl"
                class="mt-4 space-y-2 rounded-lg border border-primary/20 bg-primary/5 p-3"
              >
                <p class="text-sm font-medium text-highlighted">
                  {{ t('starterDashboard.invitationLinkCreated') }}
                </p>
                <div class="flex flex-col gap-2 sm:flex-row sm:items-end">
                  <UFormField
                    :label="t('starterDashboard.invitationLink')"
                    class="min-w-0 flex-1"
                  >
                    <UInput
                      :model-value="invitationUrl"
                      readonly
                      class="w-full"
                    />
                  </UFormField>
                  <UButton
                    color="neutral"
                    variant="outline"
                    icon="i-lucide-copy"
                    @click="copyInvitation"
                  >
                    {{ invitationCopied ? t('starterDashboard.copied') : t('starterDashboard.copyLink') }}
                  </UButton>
                </div>
                <p class="text-xs text-muted">
                  {{ t('starterDashboard.invitationLinkHint') }}
                </p>
              </div>

              <div
                v-if="invitations.length"
                class="mt-5 divide-y divide-default border-t border-default"
              >
                <div
                  v-for="invitation in invitations"
                  :key="invitation.id"
                  class="flex flex-wrap items-center justify-between gap-3 py-3"
                >
                  <div class="min-w-0">
                    <p class="truncate text-sm font-medium text-highlighted">
                      {{ invitation.email }}
                    </p>
                    <p class="mt-1 text-xs text-muted">
                      {{ t(`starterDashboard.invitationStatus.${invitation.status}`) }} · {{ invitation.expires_at }}
                    </p>
                  </div>
                  <UButton
                    v-if="invitation.status === 'pending'"
                    size="xs"
                    color="error"
                    variant="ghost"
                    :loading="saving"
                    @click="revokeInvitation(invitation)"
                  >
                    {{ t('starterDashboard.revokeInvitation') }}
                  </UButton>
                </div>
              </div>
            </UPageCard>
          </template>
        </div>
      </UContainer>
    </template>
  </UDashboardPanel>
</template>
