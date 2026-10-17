<script setup lang="ts">
import { STARTER_REPOSITORY_URL } from '~/utils/starter-links'

definePageMeta({ layout: 'projects-landing' })
const copy = useProjectsHomePresentation()
const publicCopy = useStarterPublicPresentation()
const localePath = useLocalePath()
const config = useRuntimeConfig()
const session = useProjectsIdentitySession()
const dashboardPath = localePath('/projects/dashboard')
const dashboardUrl = computed(() => session.loggedIn.value ? dashboardPath : session.authorize(dashboardPath))
useSeoMeta({ title: () => `${publicCopy.value.tasks} · Talred Starter`, description: () => publicCopy.value.tasksDescription })
</script>

<template>
  <UContainer class="starter-public">
    <StarterPublicIntro
      :title="publicCopy.tasks"
      :description="publicCopy.tasksDescription"
      :eyebrow="publicCopy.tasksEyebrow"
    >
      <UButton
        :to="dashboardUrl"
        :external="!session.loggedIn.value"
        size="lg"
        trailing-icon="i-lucide-arrow-right"
      >
        {{ publicCopy.tasksOpen }}
      </UButton>
      <UButton
        :to="config.public.docsUrl"
        external
        color="neutral"
        variant="outline"
        size="lg"
      >
        {{ publicCopy.docs }}
      </UButton>
      <UButton
        :to="STARTER_REPOSITORY_URL"
        external
        color="neutral"
        variant="link"
        icon="i-simple-icons-github"
      >
        GitHub
      </UButton>
    </StarterPublicIntro>
    <section
      class="starter-public-section"
      aria-labelledby="tasks-features-title"
    >
      <h2 id="tasks-features-title">
        {{ publicCopy.tasksFeaturesTitle }}
      </h2>
      <div class="starter-public-grid projects-home-features">
        <article
          v-for="feature in publicCopy.tasksFeatures"
          :key="feature.title"
          class="starter-public-choice"
        >
          <UIcon
            :name="feature.icon"
            class="size-5 text-primary"
            aria-hidden="true"
          />
          <h3>{{ feature.title }}</h3>
          <p>{{ feature.description }}</p>
        </article>
      </div>
      <div class="starter-public-actions">
        <UButton
          :to="localePath('/projects/about')"
          color="neutral"
          variant="link"
          class="px-0"
          trailing-icon="i-lucide-arrow-right"
        >
          {{ copy.navigation.about }}
        </UButton>
        <UButton
          :to="localePath('/')"
          color="neutral"
          variant="link"
        >
          Talred Starter
        </UButton>
      </div>
    </section>
  </UContainer>
</template>
