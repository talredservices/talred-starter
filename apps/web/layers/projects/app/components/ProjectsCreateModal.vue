<script setup lang="ts">
import type { Project } from '../../shared/types/projects'

const open = defineModel<boolean>('open', { required: true })
const emit = defineEmits<{ created: [project: Project] }>()
const overview = useProjectsOverview()
const { creating, saveError } = overview
const { t } = useI18n()

watch(open, (isOpen) => {
  if (!isOpen) {
    Object.assign(overview.form, { name: '', key: '', description: '' })
    overview.saveError.value = ''
  }
})

const submit = async () => {
  const existingIds = new Set(overview.projects.value.map(project => project.id))
  await overview.submit()

  const created = overview.projects.value.find(project => !existingIds.has(project.id))
  if (!created || overview.saveError.value) return

  emit('created', created)
  open.value = false
}
</script>

<template>
  <UModal
    v-model:open="open"
    :title="t('projects.createTitle')"
    :description="t('projects.createProjectHint')"
    :dismissible="!creating"
  >
    <template #body>
      <UForm
        :schema="overview.schema"
        :state="overview.form"
        class="space-y-4"
        @submit="submit"
      >
        <UFormField
          :label="t('projects.name')"
          name="name"
          required
        >
          <UInput
            v-model="overview.form.name"
            :placeholder="t('projects.name')"
            autocomplete="off"
            maxlength="120"
            class="w-full"
            autofocus
          />
        </UFormField>
        <UFormField
          :label="t('projects.projectKey')"
          name="key"
          required
        >
          <UInput
            v-model="overview.form.key"
            :placeholder="t('projects.key')"
            autocomplete="off"
            autocapitalize="off"
            spellcheck="false"
            maxlength="32"
            pattern="[A-Za-z0-9_-]+"
            class="w-full"
          />
        </UFormField>
        <UFormField
          :label="t('projects.descriptionLabel')"
          name="description"
        >
          <UTextarea
            v-model="overview.form.description"
            :placeholder="t('projects.descriptionLabel')"
            maxlength="1000"
            :rows="3"
            class="w-full"
          />
        </UFormField>
        <UAlert
          v-if="saveError"
          color="error"
          variant="subtle"
          :title="saveError"
        />
        <div class="flex justify-end gap-2">
          <UButton
            type="button"
            :label="t('projects.cancel')"
            color="neutral"
            variant="ghost"
            :disabled="creating"
            @click="open = false"
          />
          <UButton
            type="submit"
            :label="t('projects.create')"
            icon="i-lucide-folder-plus"
            :loading="creating"
            :disabled="creating"
          />
        </div>
      </UForm>
    </template>
  </UModal>
</template>
