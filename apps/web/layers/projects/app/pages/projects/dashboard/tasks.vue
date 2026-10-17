<script setup lang="ts">
import type { Project, ProjectTask } from '../../../../shared/types/projects'

definePageMeta({ layout: 'projects-dashboard', middleware: ['projects-auth', 'projects-task-context'], pageTransition: false })
const { t } = useI18n()
const localePath = useLocalePath()
const copy = useTasksDashboardPresentation()
const firstProject = useProjectsOverview()
const { creating: creatingProject, saveError: createProjectError } = firstProject
const { projectId, project, projectUnavailable, availableProjects, tasks, pending, title, description, priority, descriptionDrafts, filters, statusItems, priorityItems, load, addTask, update, removeTask, saveDescription, countByStatus, completedTasks, completion, activeFilterCount, boardGroups, clearFilters, priorityColor, view, busy, errorMessage } = useProjectsTasksPage()
const createProjectOpen = ref(false)
const taskToDelete = ref<ProjectTask | null>(null)
const deleteTaskOpen = ref(false)
const projectToDelete = ref<Project | null>(null)
const deleteProjectOpen = ref(false)
onMounted(() => load())

const createdProject = (created: Project) => {
  const taskBoardUrl = localePath({ path: '/projects/dashboard/tasks', query: { project: created.id } })
  void navigateTo(taskBoardUrl)
}

const requestDeleteTask = (task: ProjectTask) => {
  errorMessage.value = ''
  taskToDelete.value = task
  deleteTaskOpen.value = true
}

const requestDeleteProject = (target: Project) => {
  projectToDelete.value = target
  deleteProjectOpen.value = true
}

const projectDeleted = async () => {
  await navigateTo(localePath('/projects/dashboard'))
}

watch(deleteProjectOpen, (isOpen) => {
  if (!isOpen) projectToDelete.value = null
})

const confirmDeleteTask = async () => {
  if (!taskToDelete.value || busy.value) return
  if (await removeTask(taskToDelete.value)) deleteTaskOpen.value = false
}

watch(deleteTaskOpen, (isOpen) => {
  if (!isOpen) taskToDelete.value = null
})

const createFirstProject = async () => {
  await firstProject.submit()
  const project = firstProject.projects.value[0]
  if (project && !firstProject.saveError.value) {
    const taskBoardUrl = localePath({ path: '/projects/dashboard/tasks', query: { project: project.id } })
    await navigateTo(taskBoardUrl)
  }
}
</script>

<template>
  <div class="contents">
    <UDashboardPanel
      id="projects-tasks"
      class="min-w-0 flex-1"
    >
      <template #header>
        <DashboardPageNavbar :title="project?.name || t('projects.nav.tasks')">
          <template #title>
            <span class="flex min-w-0 items-center gap-2 font-semibold text-highlighted">
              <UIcon
                name="i-lucide-list-checks"
                class="size-4 shrink-0 text-primary"
                aria-hidden="true"
              />
              <span class="truncate">{{ project?.name || t('projects.nav.tasks') }}</span>
            </span>
          </template>
          <template #right>
            <UButton
              :label="t('projects.create')"
              icon="i-lucide-folder-plus"
              color="primary"
              variant="soft"
              @click="createProjectOpen = true"
            />
            <UButton
              v-if="project"
              :aria-label="`${t('projects.deleteProject')}: ${project.name}`"
              icon="i-lucide-trash-2"
              color="error"
              variant="ghost"
              @click="requestDeleteProject(project)"
            />
            <UButton
              :loading="pending"
              :aria-label="copy.refresh"
              icon="i-lucide-refresh-cw"
              color="neutral"
              variant="ghost"
              @click="load"
            />
            <UButton
              :to="localePath('/projects/dashboard')"
              icon="i-lucide-layout-dashboard"
              color="neutral"
              variant="ghost"
              class="hidden sm:inline-flex"
            >
              {{ t('projects.nav.overview') }}
            </UButton>
          </template>
        </DashboardPageNavbar>
      </template>

      <template #body>
        <div
          v-if="errorMessage && !deleteTaskOpen && projectId"
          class="p-4"
        >
          <UAlert
            color="error"
            :title="errorMessage"
            :actions="[{ label: copy.retry, onClick: load }]"
          />
        </div>
        <UContainer
          v-if="!projectId"
          class="w-full max-w-none px-4 py-10 sm:px-6 lg:px-8"
        >
          <div
            v-if="pending"
            class="mx-auto max-w-3xl space-y-4"
            aria-busy="true"
          >
            <UCard
              v-for="index in 3"
              :key="index"
              variant="subtle"
              class="h-24 animate-pulse"
            />
          </div>
          <div
            v-else-if="errorMessage"
            class="mx-auto max-w-xl"
          >
            <UAlert
              color="error"
              :title="errorMessage"
              :actions="[{ label: copy.retry, onClick: load }]"
            />
          </div>
          <div
            v-else-if="availableProjects.length === 0"
            class="mx-auto max-w-xl rounded-3xl border border-default bg-elevated/30 px-6 py-8 sm:px-8 sm:py-10"
          >
            <span
              class="mx-auto grid size-14 place-items-center rounded-2xl bg-primary/10 text-primary sm:mx-0"
            >
              <UIcon
                name="i-lucide-folder-plus"
                class="size-7"
              />
            </span>
            <h2 class="mt-5 text-2xl font-semibold tracking-tight text-highlighted">
              {{ t('projects.noProjectsTitle') }}
            </h2>
            <p class="mt-2 text-sm leading-6 text-muted sm:max-w-lg">
              {{ t('projects.noProjectsDescription') }}
            </p>
            <UForm
              :schema="firstProject.schema"
              :state="firstProject.form"
              class="mt-6 space-y-4 text-left"
              @submit="createFirstProject"
            >
              <UFormField
                :label="t('projects.name')"
                name="name"
                required
              >
                <UInput
                  v-model="firstProject.form.name"
                  :placeholder="t('projects.name')"
                  maxlength="120"
                  autocomplete="off"
                  class="w-full"
                />
              </UFormField>
              <UFormField
                :label="t('projects.projectKey')"
                name="key"
                :hint="t('projects.createProjectHint')"
                required
              >
                <UInput
                  v-model="firstProject.form.key"
                  :placeholder="t('projects.key')"
                  maxlength="32"
                  autocomplete="off"
                  class="w-full"
                />
              </UFormField>
              <UFormField
                :label="t('projects.descriptionLabel')"
                name="description"
              >
                <UTextarea
                  v-model="firstProject.form.description"
                  :placeholder="t('projects.descriptionLabel')"
                  maxlength="1000"
                  :rows="3"
                  class="w-full"
                />
              </UFormField>
              <UAlert
                v-if="createProjectError"
                color="error"
                variant="subtle"
                :title="createProjectError"
              />
              <UButton
                type="submit"
                icon="i-lucide-plus"
                :loading="creatingProject"
                :disabled="creatingProject"
                class="w-full justify-center sm:w-auto"
              >
                {{ t('projects.createFirstProject') }}
              </UButton>
            </UForm>
          </div>
          <div
            v-else
            class="mx-auto w-full max-w-4xl space-y-6"
          >
            <div>
              <p class="text-xs font-semibold uppercase tracking-[0.16em] text-primary">
                {{ t('projects.projectContext') }}
              </p>
              <h2 class="mt-2 text-3xl font-semibold tracking-tight text-highlighted">
                {{ t('projects.selectProject') }}
              </h2>
              <p class="mt-2 text-sm leading-6 text-muted">
                {{ t('projects.projectsDescription') }}
              </p>
            </div>
            <div class="grid gap-4 md:grid-cols-2">
              <UCard
                v-for="availableProject in availableProjects"
                :key="availableProject.id"
                variant="subtle"
                class="group transition hover:-translate-y-0.5 hover:border-primary/40"
              >
                <NuxtLink
                  :to="{ path: localePath('/projects/dashboard/tasks'), query: { project: availableProject.id } }"
                  class="block focus-visible:outline-2 focus-visible:outline-primary"
                >
                  <div class="flex items-start justify-between gap-4">
                    <span class="grid size-11 place-items-center rounded-2xl bg-primary/10 text-primary transition group-hover:bg-primary group-hover:text-white">
                      <UIcon
                        name="i-lucide-folder-kanban"
                        class="size-5"
                      />
                    </span>
                    <UIcon
                      name="i-lucide-arrow-up-right"
                      class="size-4 text-dimmed group-hover:text-primary"
                    />
                  </div>
                  <h2 class="mt-5 font-semibold text-highlighted">
                    {{ availableProject.name }}
                  </h2>
                  <p class="mt-1 truncate text-sm text-muted">
                    {{ availableProject.description || availableProject.key }}
                  </p>
                  <div class="mt-4 flex items-center justify-between border-t border-default pt-3 text-sm">
                    <span class="font-mono text-xs text-muted">{{ availableProject.key }}</span>
                    <span class="text-muted">{{ t('projects.taskCountShort', { count: availableProject.tasks_count || 0 }) }}</span>
                  </div>
                </NuxtLink>
              </UCard>
            </div>
          </div>
        </UContainer>

        <UContainer
          v-else-if="pending"
          class="w-full max-w-none px-4 py-10 sm:px-6 lg:px-8"
          aria-busy="true"
        >
          <div class="mx-auto max-w-5xl space-y-4">
            <UCard
              v-for="index in 3"
              :key="index"
              variant="subtle"
              class="h-28 animate-pulse"
            />
          </div>
        </UContainer>

        <UContainer
          v-else-if="projectId && projectUnavailable"
          class="w-full max-w-none px-4 py-10 sm:px-6 lg:px-8"
        >
          <UCard
            class="mx-auto max-w-xl text-center"
            variant="subtle"
          >
            <span class="mx-auto grid size-12 place-items-center rounded-2xl bg-warning/10 text-warning">
              <UIcon
                name="i-lucide-folder-search"
                class="size-6"
                aria-hidden="true"
              />
            </span>
            <h2 class="mt-4 text-xl font-semibold text-highlighted">
              {{ t('projects.projectUnavailableTitle') }}
            </h2>
            <p class="mt-2 text-sm leading-6 text-muted">
              {{ t('projects.projectUnavailableDescription') }}
            </p>
            <UButton
              :to="localePath('/projects/dashboard/tasks')"
              class="mt-5"
              icon="i-lucide-arrow-left"
              color="neutral"
              variant="outline"
            >
              {{ t('projects.selectProject') }}
            </UButton>
          </UCard>
        </UContainer>

        <UContainer
          v-else-if="project"
          class="w-full max-w-none px-4 py-6 sm:px-6 sm:py-8 lg:px-8"
        >
          <div class="mx-auto w-full max-w-[1500px] space-y-7">
            <section class="flex flex-col gap-4 border-b border-default pb-5 sm:flex-row sm:items-center sm:justify-between">
              <div class="min-w-0">
                <div class="flex flex-wrap items-center gap-2">
                  <h2 class="text-2xl font-semibold tracking-tight text-highlighted sm:text-3xl">
                    {{ project.name }}
                  </h2>
                  <UBadge
                    :label="project.status === 'active' ? t('projects.projectActive') : t('projects.projectArchived')"
                    :color="project.status === 'active' ? 'success' : 'neutral'"
                    variant="subtle"
                  />
                </div>
                <p class="mt-1 font-mono text-xs text-muted">
                  {{ project.key }}
                </p>
                <p
                  v-if="project.description"
                  class="mt-2 max-w-3xl text-sm leading-6 text-muted"
                >
                  {{ project.description }}
                </p>
              </div>
            </section>

            <TasksMetricCards
              :loading="pending"
              :items="[
                { label: t('projects.todoCount'), value: countByStatus('todo'), icon: 'i-lucide-circle' },
                { label: t('projects.inProgressCount'), value: countByStatus('in_progress'), icon: 'i-lucide-loader-circle' },
                { label: copy.completed, value: completedTasks, icon: 'i-lucide-check-circle-2' },
                { label: copy.completion, value: `${completion}%`, icon: 'i-lucide-chart-pie' }
              ]"
            />
            <div class="grid items-start gap-6 xl:grid-cols-[minmax(0,1fr)_minmax(18rem,0.38fr)]">
              <div class="min-w-0 space-y-4">
                <UCard variant="subtle">
                  <div class="flex flex-col gap-3 lg:flex-row lg:items-center">
                    <UInput
                      v-model="filters.search"
                      icon="i-lucide-search"
                      :placeholder="t('projects.searchTasks')"
                      :aria-label="t('projects.searchTasks')"
                      maxlength="120"
                      class="min-w-0 flex-1"
                    />
                    <USelect
                      v-model="filters.status"
                      :aria-label="copy.status"
                      :items="statusItems"
                      :placeholder="t('projects.filterStatus')"
                      class="w-full lg:w-48"
                    />
                    <USelect
                      v-model="filters.priority"
                      :aria-label="copy.priority"
                      :items="priorityItems"
                      :placeholder="t('projects.filterPriority')"
                      class="w-full lg:w-48"
                    />
                    <UButton
                      v-if="activeFilterCount"
                      color="neutral"
                      variant="ghost"
                      icon="i-lucide-x"
                      @click="clearFilters"
                    >
                      {{ t('projects.clearFilters') }}
                    </UButton>
                  </div>
                  <div
                    v-if="activeFilterCount"
                    class="mt-3 flex items-center gap-2 text-xs text-primary"
                  >
                    <span class="size-1.5 rounded-full bg-primary" />
                    {{ t('projects.filtersActive') }}
                  </div>
                </UCard>

                <div class="flex justify-end">
                  <USelect
                    v-model="view"
                    :aria-label="copy.view"
                    :items="[{ label: copy.table, value: 'table' }, { label: copy.board, value: 'board' }]"
                    class="w-40"
                  />
                </div>
                <ProjectTasksTable
                  v-if="view === 'table'"
                  :tasks="tasks"
                  :loading="pending"
                  :busy="busy"
                  :error-message="errorMessage"
                  @update="update"
                  @remove="requestDeleteTask"
                />
                <div
                  v-else-if="pending"
                  class="grid gap-4 2xl:grid-cols-3"
                  aria-busy="true"
                >
                  <UCard
                    v-for="group in boardGroups"
                    :key="group.value"
                    variant="subtle"
                    class="h-72 animate-pulse"
                  />
                </div>
                <div
                  v-else
                  class="grid items-start gap-4 2xl:grid-cols-3"
                >
                  <section
                    v-for="group in boardGroups"
                    :key="group.value"
                    class="min-w-0 rounded-2xl border border-default bg-elevated/25 p-3"
                  >
                    <header class="flex items-center justify-between gap-3 px-2 py-2">
                      <div class="flex items-center gap-2">
                        <UIcon
                          :name="group.icon"
                          class="size-4"
                          :class="group.value === 'done' ? 'text-success' : group.value === 'in_progress' ? 'text-info' : 'text-muted'"
                        />
                        <h2 class="text-sm font-semibold text-highlighted">
                          {{ group.label }}
                        </h2>
                      </div>
                      <UBadge
                        :label="`${group.tasks.length}`"
                        color="neutral"
                        variant="subtle"
                      />
                    </header>

                    <div class="mt-2 space-y-3">
                      <UCard
                        v-for="task in group.tasks"
                        :key="task.id"
                        variant="subtle"
                        class="group/task border-default/80 transition hover:border-primary/40"
                      >
                        <div class="flex items-start justify-between gap-3">
                          <div class="min-w-0">
                            <h3 class="font-medium leading-5 text-highlighted">
                              {{ task.title }}
                            </h3>
                            <p
                              v-if="task.description"
                              class="mt-2 line-clamp-3 text-sm leading-5 text-muted"
                            >
                              {{ task.description }}
                            </p>
                          </div>
                          <UBadge
                            :label="t(`projects.priority.${task.priority}`)"
                            :color="priorityColor(task.priority)"
                            variant="subtle"
                            class="shrink-0"
                          />
                        </div>
                        <div class="mt-4 flex flex-wrap items-center gap-2 border-t border-default pt-3">
                          <USelect
                            :model-value="task.status"
                            :disabled="busy"
                            :aria-label="`${copy.status}: ${task.title}`"
                            :items="statusItems.slice(1)"
                            size="sm"
                            class="min-w-32"
                            @update:model-value="update(task, { status: $event })"
                          />
                          <USelect
                            :model-value="task.priority"
                            :disabled="busy"
                            :aria-label="`${copy.priority}: ${task.title}`"
                            :items="priorityItems.slice(1)"
                            size="sm"
                            class="min-w-28"
                            @update:model-value="update(task, { priority: $event })"
                          />
                          <UButton
                            type="button"
                            color="error"
                            variant="ghost"
                            size="sm"
                            class="ml-auto"
                            icon="i-lucide-trash-2"
                            :disabled="busy"
                            :aria-label="`${t('projects.deleteTask')}: ${task.title}`"
                            @click="requestDeleteTask(task)"
                          />
                        </div>
                        <UTextarea
                          v-model="descriptionDrafts[task.id]"
                          :aria-label="t('projects.taskDescriptionPlaceholder')"
                          :placeholder="t('projects.taskDescriptionPlaceholder')"
                          :rows="2"
                          maxlength="2000"
                          class="mt-3"
                          @blur="saveDescription(task)"
                        />
                      </UCard>

                      <div
                        v-if="group.tasks.length === 0"
                        class="rounded-xl border border-dashed border-default px-4 py-8 text-center"
                      >
                        <UIcon
                          :name="group.icon"
                          class="size-5 text-dimmed"
                        />
                        <p class="mt-2 text-sm font-medium text-muted">
                          {{ t('projects.boardEmpty') }}
                        </p>
                        <p class="mt-1 text-xs leading-5 text-dimmed">
                          {{ t('projects.boardEmptyDescription') }}
                        </p>
                      </div>
                    </div>
                  </section>
                </div>
              </div>
              <UCard
                variant="subtle"
                class="min-w-0 xl:sticky xl:top-20"
              >
                <div class="space-y-4">
                  <div>
                    <div class="flex items-center gap-2">
                      <span class="grid size-9 place-items-center rounded-xl bg-primary/10 text-primary">
                        <UIcon
                          name="i-lucide-plus"
                          class="size-4"
                          aria-hidden="true"
                        />
                      </span>
                      <h2 class="text-lg font-semibold text-highlighted">
                        {{ t('projects.addTaskTitle') }}
                      </h2>
                    </div>
                    <p class="mt-2 text-sm leading-6 text-muted">
                      {{ t('projects.addTaskDescription') }}
                    </p>
                  </div>
                  <form
                    class="space-y-3"
                    @submit.prevent="addTask"
                  >
                    <UInput
                      v-model="title"
                      class="w-full"
                      :aria-label="t('projects.taskPlaceholder')"
                      :placeholder="t('projects.taskPlaceholder')"
                      required
                      maxlength="160"
                    />
                    <UTextarea
                      v-model="description"
                      :aria-label="t('projects.taskDescriptionPlaceholder')"
                      :placeholder="t('projects.taskDescriptionPlaceholder')"
                      :rows="3"
                      maxlength="2000"
                      class="w-full"
                    />
                    <USelect
                      v-model="priority"
                      :aria-label="copy.priority"
                      :items="priorityItems.slice(1)"
                      class="w-full"
                    />
                    <UButton
                      type="submit"
                      icon="i-lucide-plus"
                      class="w-full justify-center"
                      :disabled="!title.trim() || busy"
                      :loading="busy"
                    >
                      {{ t('projects.addTask') }}
                    </UButton>
                  </form>
                </div>
              </UCard>
            </div>
          </div>
        </UContainer>
      </template>
    </UDashboardPanel>
    <ProjectsCreateModal
      v-model:open="createProjectOpen"
      @created="createdProject"
    />
    <ProjectsDeleteModal
      v-model:open="deleteProjectOpen"
      :project="projectToDelete"
      @deleted="projectDeleted"
    />
    <UModal
      v-model:open="deleteTaskOpen"
      :title="t('projects.deleteTaskDialogTitle')"
      :description="taskToDelete ? t('projects.deleteTaskDialogDescription', { title: taskToDelete.title }) : ''"
      :dismissible="!busy"
    >
      <template #body>
        <UAlert
          v-if="errorMessage"
          color="error"
          variant="subtle"
          :title="errorMessage"
        />
        <div class="flex justify-end gap-2">
          <UButton
            :label="t('projects.cancel')"
            color="neutral"
            variant="ghost"
            :disabled="busy"
            @click="deleteTaskOpen = false"
          />
          <UButton
            :label="t('projects.deleteTask')"
            icon="i-lucide-trash-2"
            color="error"
            :loading="busy"
            :disabled="busy || !taskToDelete"
            @click="confirmDeleteTask"
          />
        </div>
      </template>
    </UModal>
  </div>
</template>
