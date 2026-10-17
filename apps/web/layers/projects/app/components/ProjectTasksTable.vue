<script setup lang="ts">
import TasksTablePagination from './TasksTablePagination.vue'
import type { TableColumn } from '@nuxt/ui'
import type { ProjectTask, TaskStatus, TaskPriority } from '../../shared/types/projects'

const props = defineProps<{ tasks: ProjectTask[], loading: boolean, busy: boolean, errorMessage: string }>()
const emit = defineEmits<{ update: [task: ProjectTask, patch: Partial<Pick<ProjectTask, 'status' | 'priority' | 'title' | 'description'>>], remove: [task: ProjectTask] }>()
const copy = useTasksDashboardPresentation()
const { t } = useI18n()
const page = ref(1)
const sort = ref('title')
const sorted = computed(() => [...props.tasks].sort((a, b) => sort.value === 'priority' ? ({ high: 3, medium: 2, low: 1 }[b.priority] - { high: 3, medium: 2, low: 1 }[a.priority]) || a.title.localeCompare(b.title) : a.title.localeCompare(b.title)))
const selected = ref<ProjectTask | null>(null)
const open = ref(false)
const titleDraft = ref('')
const description = ref('')
const rows = computed(() => sorted.value.slice((page.value - 1) * 10, page.value * 10))
watch([() => props.tasks, sort], () => {
  page.value = 1
})
const columns = computed<TableColumn<ProjectTask>[]>(() => [{ accessorKey: 'title', header: copy.value.title }, { accessorKey: 'status', header: copy.value.status }, { accessorKey: 'priority', header: copy.value.priority }, { id: 'actions', header: '' }])
const statuses = computed(() => (['todo', 'in_progress', 'done'] as TaskStatus[]).map(value => ({ value, label: t(`projects.status.${value}`) })))
const priorities = computed(() => (['low', 'medium', 'high'] as TaskPriority[]).map(value => ({ value, label: t(`projects.priority.${value}`) })))
const inspect = (task: ProjectTask) => {
  selected.value = task
  titleDraft.value = task.title
  description.value = task.description || ''
  open.value = true
}
</script>

<template>
  <UCard :ui="{ body: 'p-0 sm:p-0' }">
    <div class="flex justify-end p-4">
      <USelect
        v-model="sort"
        :aria-label="copy.sortTasks"
        :items="[{ label: copy.nameSort, value: 'title' }, { label: copy.prioritySort, value: 'priority' }]"
        class="w-44"
      />
    </div>
    <div class="hidden md:block">
      <UTable
        :columns="columns"
        :data="rows"
        :loading="loading"
        :empty="copy.noTasks"
        :caption="t('projects.nav.tasks')"
        class="max-w-full"
      >
        <template #title-cell="{ row }">
          <button
            class="block max-w-72 truncate text-left font-medium text-primary hover:underline"
            @click="inspect(row.original)"
          >
            {{ row.original.title }}
          </button><p class="max-w-72 truncate text-xs text-muted">
            {{ row.original.description }}
          </p>
        </template>
        <template #status-cell="{ row }">
          <USelect
            :model-value="row.original.status"
            :items="statuses"
            :disabled="busy"
            :aria-label="`${copy.status}: ${row.original.title}`"
            class="w-36"
            @update:model-value="emit('update', row.original, { status: $event })"
          />
        </template>
        <template #priority-cell="{ row }">
          <USelect
            :model-value="row.original.priority"
            :items="priorities"
            :disabled="busy"
            :aria-label="`${copy.priority}: ${row.original.title}`"
            class="w-28"
            @update:model-value="emit('update', row.original, { priority: $event })"
          />
        </template>
        <template #actions-cell="{ row }">
          <UButton
            :disabled="busy"
            icon="i-lucide-trash-2"
            color="error"
            variant="ghost"
            :aria-label="`${t('projects.deleteTask')}: ${row.original.title}`"
            @click="emit('remove', row.original)"
          />
        </template>
      </UTable>
    </div>
    <div class="divide-y divide-default md:hidden">
      <article
        v-for="task in rows"
        :key="task.id"
        class="min-w-0 space-y-3 p-4"
      >
        <div class="flex items-start justify-between gap-2">
          <button
            class="min-w-0 break-words text-left font-medium text-primary hover:underline"
            @click="inspect(task)"
          >
            {{ task.title }}
          </button>
          <UButton
            :disabled="busy"
            icon="i-lucide-trash-2"
            color="error"
            variant="ghost"
            :aria-label="`${t('projects.deleteTask')}: ${task.title}`"
            class="shrink-0"
            @click="emit('remove', task)"
          />
        </div>
        <p
          v-if="task.description"
          class="break-words text-sm text-muted"
        >
          {{ task.description }}
        </p>
        <div class="grid min-w-0 grid-cols-2 gap-3">
          <UFormField :label="copy.status">
            <USelect
              :model-value="task.status"
              :items="statuses"
              :disabled="busy"
              :aria-label="`${copy.status}: ${task.title}`"
              class="w-full"
              @update:model-value="emit('update', task, { status: $event })"
            />
          </UFormField>
          <UFormField :label="copy.priority">
            <USelect
              :model-value="task.priority"
              :items="priorities"
              :disabled="busy"
              :aria-label="`${copy.priority}: ${task.title}`"
              class="w-full"
              @update:model-value="emit('update', task, { priority: $event })"
            />
          </UFormField>
        </div>
      </article>
      <div
        v-if="loading"
        class="p-4 text-sm text-muted"
        role="status"
      >
        {{ copy.loading }}
      </div>
      <p
        v-else-if="!rows.length"
        class="p-4 text-sm text-muted"
      >
        {{ copy.noTasks }}
      </p>
    </div>
    <div class="flex flex-wrap justify-between gap-3 border-t border-default p-4">
      <p class="text-sm text-muted">
        {{ (tasks.length === 1 ? copy.record : copy.records).replace('{count}', String(tasks.length)) }}
      </p><TasksTablePagination
        v-model:page="page"
        :total="tasks.length"
        :page-size="10"
      />
    </div>
  </UCard>
  <USlideover
    v-model:open="open"
    :title="copy.details"
    :description="selected?.title"
    :dismissible="!busy"
  >
    <template #body>
      <div
        v-if="selected"
        class="space-y-5"
      >
        <UAlert
          v-if="errorMessage"
          color="error"
          :title="errorMessage"
        />
        <h2 class="text-xl font-semibold break-words">
          {{ selected.title }}
        </h2><div class="flex flex-wrap gap-2">
          <UBadge>{{ t(`projects.status.${selected.status}`) }}</UBadge><UBadge color="neutral">
            {{ t(`projects.priority.${selected.priority}`) }}
          </UBadge>
        </div><UFormField :label="copy.title">
          <UInput
            v-model="titleDraft"
            maxlength="160"
            class="w-full"
          />
        </UFormField><UFormField :label="copy.description">
          <UTextarea
            v-model="description"
            maxlength="2000"
            :rows="8"
            class="w-full"
          />
        </UFormField><UButton
          :loading="busy"
          :disabled="busy || !titleDraft.trim()"
          @click="emit('update', selected, { title: titleDraft.trim(), description })"
        >
          {{ copy.save }}
        </UButton>
      </div>
    </template>
  </USlideover>
</template>
