<script setup lang="ts">
import type { PlatformWorkspace } from '~~/shared/types/platform'

const open = defineModel<boolean>('open', { required: true })
const emit = defineEmits<{ created: [workspace: PlatformWorkspace] }>()

const client = usePlatformClient()
const { t } = useI18n()
const submitting = ref(false)
const errorMessage = ref('')
const form = reactive({ name: '', slug: '' })

const resetForm = () => {
  form.name = ''
  form.slug = ''
  errorMessage.value = ''
}

watch(open, (isOpen) => {
  if (!isOpen) resetForm()
})

const createWorkspace = async () => {
  if (!form.name.trim()) return

  submitting.value = true
  errorMessage.value = ''

  try {
    const response = await client.create({
      name: form.name.trim(),
      slug: form.slug.trim() || undefined
    })

    emit('created', response.workspace)
    open.value = false
  } catch (error) {
    errorMessage.value = error instanceof Error ? error.message : t('starterDashboard.workspaceCreateFailed')
  } finally {
    submitting.value = false
  }
}
</script>

<template>
  <UModal
    v-model:open="open"
    :title="t('starterDashboard.createWorkspace')"
    :description="t('starterDashboard.createWorkspaceDescription')"
  >
    <template #body>
      <form
        class="space-y-4"
        @submit.prevent="createWorkspace"
      >
        <UAlert
          v-if="errorMessage"
          color="error"
          variant="subtle"
          :title="t('starterDashboard.workspaceCreateFailed')"
          :description="errorMessage"
        />

        <UFormField
          :label="t('starterDashboard.workspaceName')"
          required
        >
          <UInput
            v-model="form.name"
            autofocus
            class="w-full"
            name="name"
            autocomplete="organization"
            :placeholder="t('starterDashboard.workspaceNamePlaceholder')"
            required
            maxlength="120"
          />
        </UFormField>

        <UFormField
          :label="t('starterDashboard.workspaceSlug')"
          :hint="t('starterDashboard.optional')"
        >
          <UInput
            v-model="form.slug"
            class="w-full"
            name="slug"
            autocomplete="off"
            autocapitalize="off"
            spellcheck="false"
            :placeholder="t('starterDashboard.workspaceSlugPlaceholder')"
            maxlength="64"
            pattern="[a-z0-9]+(?:-[a-z0-9]+)*"
          />
        </UFormField>

        <div class="flex justify-end gap-2">
          <UButton
            type="button"
            :label="t('starterDashboard.cancel')"
            color="neutral"
            variant="ghost"
            @click="open = false"
          />
          <UButton
            type="submit"
            :label="t('starterDashboard.createWorkspace')"
            :loading="submitting"
            :disabled="!form.name.trim()"
          />
        </div>
      </form>
    </template>
  </UModal>
</template>
