<script setup lang="ts">
import type { Project } from '../../shared/types/projects'

const open = defineModel<boolean>('open', { required: true })
const props = defineProps<{ project: Project | null }>()
const emit = defineEmits<{ deleted: [project: Project] }>()
const { t } = useI18n()
const deletion = useProjectDeletion()

watch(open, (isOpen) => {
  if (!isOpen) deletion.error.value = ''
})

const confirm = async () => {
  const project = props.project
  if (!project || deletion.pending.value) return

  if (await deletion.remove(project)) {
    open.value = false
    emit('deleted', project)
  }
}
</script>

<template>
  <UModal
    v-model:open="open"
    :title="t('projects.deleteProjectDialogTitle')"
    :description="project ? t('projects.deleteProjectDialogDescription', { name: project.name, count: project.tasks_count || 0 }) : ''"
    :dismissible="!deletion.pending.value"
  >
    <template #body>
      <UAlert
        v-if="deletion.error.value"
        color="error"
        variant="subtle"
        :title="deletion.error.value"
      />
      <div class="flex justify-end gap-2">
        <UButton
          :label="t('projects.cancel')"
          color="neutral"
          variant="ghost"
          :disabled="deletion.pending.value"
          @click="open = false"
        />
        <UButton
          :label="t('projects.deleteProject')"
          icon="i-lucide-trash-2"
          color="error"
          :loading="deletion.pending.value"
          :disabled="deletion.pending.value || !project"
          @click="confirm"
        />
      </div>
    </template>
  </UModal>
</template>
