<script setup lang="ts">
import { STARTER_REPOSITORY_URL } from '~/utils/starter-links'

definePageMeta({ layout: 'projects-landing' })
const copy = useStarterPublicPresentation()
const config = useRuntimeConfig()
const localePath = useLocalePath()
useSeoMeta({ title: () => copy.value.tasksAboutTitle, description: () => copy.value.tasksAboutIntro })
</script>

<template>
  <UContainer class="starter-public starter-public-narrative">
    <StarterPublicIntro
      :title="copy.tasksAboutTitle"
      :description="copy.tasksAboutIntro"
    />
    <div class="starter-public-story">
      <p
        v-for="paragraph in copy.tasksStory"
        :key="paragraph"
      >
        {{ paragraph }}
      </p>
    </div>
    <div class="starter-public-section starter-public-actions">
      <UButton
        :to="localePath('/projects')"
        trailing-icon="i-lucide-arrow-right"
      >
        {{ copy.tasksOpen }}
      </UButton>
      <UButton
        :to="config.public.docsUrl"
        external
        color="neutral"
        variant="outline"
      >
        {{ copy.docs }}
      </UButton>
      <UButton
        :to="STARTER_REPOSITORY_URL"
        external
        color="neutral"
        variant="link"
        icon="i-simple-icons-github"
      >
        {{ copy.source }}
      </UButton>
    </div>
  </UContainer>
</template>
