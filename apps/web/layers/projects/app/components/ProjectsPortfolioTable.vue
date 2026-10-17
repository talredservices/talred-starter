<script setup lang="ts">
import TasksTablePagination from './TasksTablePagination.vue'
import type { Project } from '../../shared/types/projects'

const props = defineProps<{ projects: Project[], loading: boolean }>()
const emit = defineEmits<{ delete: [project: Project] }>()
const copy = useTasksDashboardPresentation()
const { t } = useI18n()
const localePath = useLocalePath()
const search = ref('')
const status = ref('all')
const sort = ref('name')
const page = ref(1)
const pageSize = 8
const items = computed(() => props.projects
  .filter(project => (status.value === 'all' || project.status === status.value) && `${project.name} ${project.key} ${project.description || ''}`.toLocaleLowerCase().includes(search.value.toLocaleLowerCase().trim()))
  .sort((a, b) => sort.value === 'tasks' ? b.tasks_count - a.tasks_count || a.name.localeCompare(b.name) : a.name.localeCompare(b.name)))
const rows = computed(() => items.value.slice((page.value - 1) * pageSize, page.value * pageSize))
watch([search, status, sort, () => props.projects.length], () => {
  page.value = 1
})

const projectBoardUrl = (project: Project) => ({
  path: localePath('/projects/dashboard/tasks'),
  query: { project: project.id }
})
</script>

<template>
  <div class="space-y-4">
    <UCard variant="subtle">
      <div class="grid gap-3 sm:grid-cols-[minmax(0,1fr)_auto_auto]">
        <UInput
          v-model="search"
          icon="i-lucide-search"
          :placeholder="copy.search"
          :aria-label="copy.search"
          maxlength="120"
          class="min-w-0"
        />
        <USelect
          v-model="status"
          :aria-label="copy.status"
          :items="[{ label: copy.all, value: 'all' }, { label: t('projects.projectActive'), value: 'active' }, { label: t('projects.projectArchived'), value: 'archived' }]"
          class="sm:w-44"
        />
        <USelect
          v-model="sort"
          :aria-label="copy.sort"
          :items="[{ label: copy.nameSort, value: 'name' }, { label: copy.tasksSort, value: 'tasks' }]"
          class="sm:w-40"
        />
      </div>
    </UCard>

    <div
      v-if="loading"
      class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3"
      aria-busy="true"
      :aria-label="copy.loading"
    >
      <UCard
        v-for="index in 3"
        :key="index"
        variant="subtle"
        class="h-44 animate-pulse"
      />
    </div>
    <div
      v-else-if="rows.length"
      class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3"
    >
      <div
        v-for="project in rows"
        :key="project.id"
        class="group relative h-full rounded-xl"
      >
        <NuxtLink
          :to="projectBoardUrl(project)"
          class="absolute inset-0 z-10 rounded-xl focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-primary"
          :aria-label="`${copy.open}: ${project.name}`"
        />
        <UCard
          variant="outline"
          class="pointer-events-none relative h-full transition-colors group-hover:border-primary/50 group-focus-within:border-primary"
        >
          <div class="flex h-full flex-col">
            <div class="flex items-start justify-between gap-3">
              <div class="flex min-w-0 items-start gap-3">
                <span class="flex size-10 shrink-0 items-center justify-center rounded-lg bg-primary/10 text-primary">
                  <UIcon
                    name="i-lucide-folder-kanban"
                    class="size-5"
                    aria-hidden="true"
                  />
                </span>
                <div class="min-w-0">
                  <h3 class="break-words font-semibold text-highlighted group-hover:text-primary">
                    {{ project.name }}
                  </h3>
                  <p class="mt-1 break-all font-mono text-xs text-muted">
                    {{ project.key }}
                  </p>
                </div>
              </div>
              <UIcon
                name="i-lucide-arrow-up-right"
                class="mt-1 size-4 shrink-0 text-muted transition-colors group-hover:text-primary"
                aria-hidden="true"
              />
              <UButton
                :aria-label="`${t('projects.deleteProject')}: ${project.name}`"
                icon="i-lucide-trash-2"
                color="error"
                variant="ghost"
                class="pointer-events-auto relative z-20 -mt-2 -mr-2 shrink-0"
                @click.stop="emit('delete', project)"
              />
            </div>

            <p
              v-if="project.description"
              class="mt-4 flex-1 text-sm leading-6 text-muted"
            >
              {{ project.description }}
            </p>

            <div class="mt-5 flex flex-wrap items-center justify-between gap-3 border-t border-default pt-4">
              <UBadge
                :color="project.status === 'active' ? 'success' : 'neutral'"
                variant="subtle"
              >
                {{ t(project.status === 'active' ? 'projects.projectActive' : 'projects.projectArchived') }}
              </UBadge>
              <span class="text-sm text-muted">
                {{ t('projects.taskCountShort', { count: project.tasks_count || 0 }) }}
              </span>
            </div>
          </div>
        </UCard>
      </div>
    </div>
    <UCard
      v-else
      variant="outline"
    >
      <p class="text-sm leading-6 text-muted">
        {{ copy.noData }}
      </p>
    </UCard>

    <div class="flex flex-wrap items-center justify-between gap-3">
      <p class="text-sm text-muted">
        {{ (items.length === 1 ? copy.record : copy.records).replace('{count}', String(items.length)) }}
      </p>
      <TasksTablePagination
        v-model:page="page"
        :total="items.length"
        :page-size="pageSize"
      />
    </div>
  </div>
</template>
