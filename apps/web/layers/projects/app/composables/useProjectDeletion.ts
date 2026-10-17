import type { Project } from '../../shared/types/projects'
import { isIdentityUnauthorized } from '~~/shared/utils/is-identity-unauthorized'

export function useProjectDeletion() {
  const api = useProjectsClient()
  const toast = useToast()
  const { t } = useI18n()
  const pending = ref(false)
  const error = ref('')

  const remove = async (project: Project) => {
    if (pending.value) return false

    pending.value = true
    error.value = ''
    try {
      const result = await api.deleteProject(project.id)
      if (!result.deleted || result.project_id !== project.id) {
        throw new Error('The project deletion response did not match the requested project.')
      }

      toast.add({ title: t('projects.projectDeleted'), color: 'success' })
      return true
    } catch (cause) {
      if (!isIdentityUnauthorized(cause)) error.value = t('projects.deleteProjectFailed')
      return false
    } finally {
      pending.value = false
    }
  }

  return { pending, error, remove }
}
