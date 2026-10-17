<script setup lang="ts">
import type { PlatformWorkspace } from '~~/shared/types/platform'

const props = defineProps<{
  workspace: PlatformWorkspace
}>()

const localePath = useLocalePath()
const { t } = useI18n()
const workspacePath = computed(() => localePath(`/dashboard/workspaces/${props.workspace.id}`))
const roleLabel = computed(() => ['owner', 'admin', 'member'].includes(props.workspace.role)
  ? t(`starterDashboard.workspaceRoleValue.${props.workspace.role}`)
  : props.workspace.role)
</script>

<template>
  <NuxtLink
    :to="workspacePath"
    class="group block h-full rounded-xl focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-primary"
    :aria-label="`${workspace.name}, ${workspace.slug}, ${workspace.is_default ? `${t('starterDashboard.defaultWorkspace')}, ` : ''}${workspace.status}, ${roleLabel}, ${t('starterDashboard.workspaceMemberCount', workspace.member_count)}`"
  >
    <UPageCard
      variant="subtle"
      class="h-full transition group-hover:border-primary/40 group-focus-visible:border-primary"
    >
      <div class="flex h-full min-h-44 flex-col gap-5">
        <div class="flex items-start justify-between gap-3">
          <span class="grid size-11 shrink-0 place-items-center rounded-xl bg-primary/10 text-primary ring-1 ring-inset ring-primary/10">
            <UIcon
              name="i-lucide-panels-top-left"
              class="size-5"
            />
          </span>
          <div class="flex flex-wrap justify-end gap-2">
            <UBadge
              v-if="workspace.is_default"
              color="primary"
              variant="subtle"
              size="xs"
            >
              {{ t('starterDashboard.defaultWorkspace') }}
            </UBadge>
            <UBadge
              :color="workspace.status === 'active' ? 'success' : 'neutral'"
              variant="subtle"
              size="xs"
            >
              {{ workspace.status }}
            </UBadge>
          </div>
        </div>

        <div class="min-w-0">
          <h3 class="truncate text-lg font-semibold text-highlighted">
            {{ workspace.name }}
          </h3>
          <p class="mt-1 truncate font-mono text-xs text-muted">
            {{ workspace.slug }}
          </p>
        </div>

        <div class="mt-auto flex items-center justify-between gap-3 border-t border-default pt-4">
          <div class="flex min-w-0 flex-wrap items-center gap-x-4 gap-y-2 text-xs text-muted">
            <span class="inline-flex items-center gap-1.5 capitalize">
              <UIcon
                name="i-lucide-shield-check"
                class="size-3.5 text-primary"
              />
              {{ roleLabel }}
            </span>
            <span class="inline-flex items-center gap-1.5">
              <UIcon
                name="i-lucide-users-round"
                class="size-3.5"
              />
              {{ t('starterDashboard.workspaceMemberCount', workspace.member_count) }}
            </span>
          </div>
          <UIcon
            name="i-lucide-arrow-up-right"
            class="size-4 shrink-0 text-dimmed transition-transform group-hover:-translate-y-0.5 group-hover:translate-x-0.5 group-hover:text-primary"
          />
        </div>
      </div>
    </UPageCard>
  </NuxtLink>
</template>
