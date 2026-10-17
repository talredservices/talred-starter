import type { Project, ProjectTask, TaskFilters, TaskPriority, TaskStatus } from '../../shared/types/projects'
import { isIdentityUnauthorized } from '~~/shared/utils/is-identity-unauthorized'

export function useProjectsTasksPage() {
  const route = useRoute()
  const localePath = useLocalePath()
  const nuxtApp = useNuxtApp()
  const { t } = useI18n()
  const copy = useTasksDashboardPresentation()
  const view = ref('table')
  const busy = ref(false)
  const errorMessage = ref('')
  const projectUnavailable = ref(false)
  let requestId = 0
  const session = useProjectsIdentitySession()
  const tasksLoginUrl = session.authorize(route.fullPath || localePath('/projects/dashboard/tasks'))
  const api = useProjectsClient()
  const toast = useToast()
  const projectId = computed(() => String(route.query.project || ''))
  const project = ref<Project | null>(null)
  const availableProjects = ref<Project[]>([])
  const tasks = ref<ProjectTask[]>([])
  const pending = ref(true)
  const title = ref('')
  const description = ref('')
  const priority = ref<TaskPriority>('medium')
  const descriptionDrafts = reactive<Record<string, string>>({})
  const filters = reactive<TaskFilters>({ search: undefined, status: undefined, priority: undefined })

  const statusItems = computed(() => [
    { label: t('projects.status.all'), value: undefined },
    { label: t('projects.status.todo'), value: 'todo' as const },
    { label: t('projects.status.in_progress'), value: 'in_progress' as const },
    { label: t('projects.status.done'), value: 'done' as const }
  ])

  const priorityItems = computed(() => [
    { label: t('projects.priority.all'), value: undefined },
    { label: t('projects.priority.low'), value: 'low' as const },
    { label: t('projects.priority.medium'), value: 'medium' as const },
    { label: t('projects.priority.high'), value: 'high' as const }
  ])

  const load = async () => {
    const currentRequest = ++requestId
    errorMessage.value = ''
    projectUnavailable.value = false
    if (!projectId.value) {
      pending.value = true
      project.value = null
      tasks.value = []
      try {
        const result = await api.list()
        availableProjects.value = result.projects
      } catch (error) {
        if (!isIdentityUnauthorized(error)) errorMessage.value = copy.value.failed
        if (isIdentityUnauthorized(error) && import.meta.server) {
          await nuxtApp.runWithContext(() => navigateTo(tasksLoginUrl, { external: true }))
        }
      } finally {
        if (currentRequest === requestId) pending.value = false
      }
      return
    }

    if (project.value?.id !== projectId.value) {
      project.value = null
      tasks.value = []
    }
    pending.value = true
    try {
      const result = await api.tasks(projectId.value, filters)
      if (currentRequest !== requestId) return
      project.value = result.project
      tasks.value = result.tasks
      for (const task of tasks.value) descriptionDrafts[task.id] = task.description || ''
    } catch (error) {
      if (currentRequest !== requestId) return
      if (!isIdentityUnauthorized(error)) {
        const cause = error as { status?: number, statusCode?: number, data?: { status?: number, statusCode?: number } }
        const status = cause.statusCode ?? cause.status ?? cause.data?.statusCode ?? cause.data?.status
        if ([403, 404].includes(Number(status))) projectUnavailable.value = true
        else errorMessage.value = copy.value.failed
      }
      if (isIdentityUnauthorized(error) && import.meta.server) {
        await nuxtApp.runWithContext(() => navigateTo(tasksLoginUrl, { external: true }))
      }
    } finally {
      if (currentRequest === requestId) pending.value = false
    }
  }

  watch(projectId, () => load())
  const debouncedSearch = refDebounced(toRef(filters, 'search'), 250)
  watch(() => [debouncedSearch.value, filters.status, filters.priority], () => load())

  const addTask = async () => {
    if (!title.value.trim() || !projectId.value || busy.value) return
    busy.value = true
    errorMessage.value = ''
    try {
      const result = await api.createTask(projectId.value, {
        title: title.value.trim(),
        description: description.value.trim() || undefined,
        priority: priority.value
      })
      tasks.value.unshift(result.task)
      descriptionDrafts[result.task.id] = result.task.description || ''
      title.value = ''
      description.value = ''
      priority.value = 'medium'
      await load()
      toast.add({ title: copy.value.saved, color: 'success' })
    } catch (error) {
      if (!isIdentityUnauthorized(error)) errorMessage.value = copy.value.saveFailed
    } finally {
      busy.value = false
    }
  }

  const update = async (task: ProjectTask, patch: Partial<Pick<ProjectTask, 'status' | 'priority' | 'title' | 'description'>>) => {
    if (busy.value) return
    busy.value = true
    errorMessage.value = ''
    try {
      const result = await api.updateTask(task.id, patch)
      Object.assign(task, result.task)
      descriptionDrafts[task.id] = task.description || ''
      await load()
      toast.add({ title: copy.value.saved, color: 'success' })
    } catch (error) {
      if (!isIdentityUnauthorized(error)) errorMessage.value = copy.value.saveFailed
    } finally {
      busy.value = false
    }
  }

  const removeTask = async (task: ProjectTask): Promise<boolean> => {
    if (busy.value) return false
    busy.value = true
    errorMessage.value = ''
    try {
      await api.deleteTask(task.id)
      tasks.value = tasks.value.filter(item => item.id !== task.id)
      Reflect.deleteProperty(descriptionDrafts, task.id)
      toast.add({ title: copy.value.removed, color: 'success' })
      return true
    } catch (error) {
      if (!isIdentityUnauthorized(error)) errorMessage.value = copy.value.saveFailed
      return false
    } finally {
      busy.value = false
    }
  }

  const saveDescription = async (task: ProjectTask) => {
    const nextDescription = descriptionDrafts[task.id] || ''
    if (nextDescription === (task.description || '')) return
    await update(task, { description: nextDescription })
  }

  const countByStatus = (status: TaskStatus) => tasks.value.filter(task => task.status === status).length
  const totalTasks = computed(() => tasks.value.length)
  const completedTasks = computed(() => countByStatus('done'))
  const completion = computed(() => totalTasks.value ? Math.round((completedTasks.value / totalTasks.value) * 100) : 0)
  const activeFilterCount = computed(() => [filters.search, filters.status, filters.priority].filter(Boolean).length)

  const boardGroups = computed(() => [
    { value: 'todo' as const, label: t('projects.status.todo'), icon: 'i-lucide-circle', color: 'neutral', tasks: tasks.value.filter(task => task.status === 'todo') },
    { value: 'in_progress' as const, label: t('projects.status.in_progress'), icon: 'i-lucide-loader-circle', color: 'info', tasks: tasks.value.filter(task => task.status === 'in_progress') },
    { value: 'done' as const, label: t('projects.status.done'), icon: 'i-lucide-check-circle-2', color: 'success', tasks: tasks.value.filter(task => task.status === 'done') }
  ])

  const clearFilters = () => {
    filters.search = undefined
    filters.status = undefined
    filters.priority = undefined
  }

  const priorityColor = (value: TaskPriority) => value === 'high' ? 'error' : value === 'medium' ? 'warning' : 'neutral'

  useSeoMeta({ title: () => `${project.value?.name || t('projects.nav.tasks')} · ${t('projects.brand')}` })

  return { projectId, project, projectUnavailable, availableProjects, tasks, pending, title, description, priority, descriptionDrafts, filters, statusItems, priorityItems, load, addTask, update, removeTask, saveDescription, countByStatus, completedTasks, completion, activeFilterCount, boardGroups, clearFilters, priorityColor, view, busy, errorMessage }
}
