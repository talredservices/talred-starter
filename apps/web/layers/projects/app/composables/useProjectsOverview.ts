import { z } from 'zod/v4'
import type { Project } from '../../shared/types/projects'
import { isIdentityUnauthorized } from '~~/shared/utils/is-identity-unauthorized'

export function useProjectsOverview() {
  const api = useProjectsClient()
  const toast = useToast()
  const { t } = useI18n()
  const nuxtApp = useNuxtApp()
  const localePath = useLocalePath()
  const session = useProjectsIdentitySession()
  const copy = useTasksDashboardPresentation()
  const projects = ref<Project[]>([])
  const pending = ref(false)
  const creating = ref(false)
  const error = ref<'forbidden' | 'failed' | ''>('')
  const saveError = ref('')
  const showCreate = ref(false)
  const form = reactive({ name: '', key: '', description: '' })
  const schema = z.object({ name: z.string().trim().min(1).max(120), key: z.string().trim().min(1).max(32).regex(/^[A-Za-z0-9_-]+$/), description: z.string().trim().max(1000) })
  const load = async () => {
    if (pending.value) return
    pending.value = true
    error.value = ''
    try {
      projects.value = (await api.list()).projects
    } catch (cause) {
      if (!isIdentityUnauthorized(cause)) {
        const response = cause && typeof cause === 'object'
          ? cause as { status?: unknown, statusCode?: unknown, response?: { status?: unknown }, data?: { statusCode?: unknown } }
          : null
        const status = response?.statusCode ?? response?.status ?? response?.response?.status ?? response?.data?.statusCode
        error.value = status === 403 ? 'forbidden' : 'failed'
      } else if (import.meta.server) {
        await nuxtApp.runWithContext(() => navigateTo(session.authorize(localePath('/projects/dashboard')), { external: true }))
      }
    } finally {
      pending.value = false
    }
  }
  const submit = async () => {
    if (creating.value) return
    const parsed = schema.safeParse(form)
    if (!parsed.success) return
    creating.value = true
    saveError.value = ''
    try {
      const result = await api.create(parsed.data)
      projects.value.unshift(result.project)
      Object.assign(form, { name: '', key: '', description: '' })
      showCreate.value = false
      toast.add({ title: t('projects.projectCreated'), color: 'success' })
    } catch (cause) {
      if (!isIdentityUnauthorized(cause)) saveError.value = copy.value.saveFailed
    } finally {
      creating.value = false
    }
  }
  return { projects, pending, creating, error, saveError, showCreate, form, schema, load, submit }
}
