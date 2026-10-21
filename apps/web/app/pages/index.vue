<script setup lang="ts">
import { useStarterEcosystemUrl } from '~/composables/useStarterEcosystemUrl'
import { STARTER_REPOSITORY_URL } from '~/utils/starter-links'

definePageMeta({ layout: 'landing' })
const copy = useStarterPublicPresentation()
const localePath = useLocalePath()
const config = useRuntimeConfig()
const ecosystemUrl = useStarterEcosystemUrl()
const demos = computed(() => [
  { title: copy.value.starterHome, description: copy.value.starterHomeDescription, to: localePath('/dashboard'), external: false, icon: 'i-lucide-panels-top-left' },
  { title: copy.value.tasks, description: copy.value.tasksDescription, to: localePath('/projects'), external: false, icon: 'i-lucide-list-checks' },
  { title: copy.value.saas, description: copy.value.saasDescription, to: localePath('/saas'), external: false, icon: 'i-lucide-layout-dashboard' }
])
useSeoMeta({ title: () => `${copy.value.title} · Talred Starter`, description: () => copy.value.description, ogTitle: () => copy.value.title, ogDescription: () => copy.value.description })
</script>

<template>
  <UContainer class="starter-public">
    <StarterPublicIntro
      :title="copy.title"
      :description="copy.description"
    >
      <UButton
        :to="localePath('/#demos')"
        size="lg"
        trailing-icon="i-lucide-arrow-down"
      >
        {{ copy.explore }}
      </UButton>
      <UButton
        :to="config.public.docsUrl"
        external
        size="lg"
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
        GitHub
      </UButton>
    </StarterPublicIntro>
    <section
      id="demos"
      class="starter-public-section"
      aria-labelledby="starter-demos-title"
    >
      <h2 id="starter-demos-title">
        {{ copy.examples }}
      </h2><p class="starter-public-summary">
        {{ copy.examplesIntro }}
      </p>
      <div class="starter-public-grid">
        <article
          v-for="demo in demos"
          :key="demo.title"
          class="starter-public-choice"
        >
          <UIcon
            :name="demo.icon"
            class="size-5 text-primary"
            aria-hidden="true"
          />
          <h3>{{ demo.title }}</h3><p>{{ demo.description }}</p>
          <UButton
            :to="demo.to"
            :external="demo.external"
            color="neutral"
            variant="link"
            trailing-icon="i-lucide-arrow-right"
            class="mt-4 px-0"
          >
            {{ copy.open }}<span class="sr-only">: {{ demo.title }}</span>
          </UButton>
        </article>
      </div>
      <p class="mt-8 text-xs text-muted">
        {{ copy.demoNotice }}
      </p>
      <article class="starter-public-identity">
        <div class="starter-public-identity-copy">
          <p class="starter-public-identity-kicker">
            {{ copy.identity }}
          </p>
          <h3>{{ copy.identityShowcaseTitle }}</h3>
          <p class="starter-public-identity-description">
            {{ copy.identityShowcaseDescription }}
          </p>
          <ul class="starter-public-identity-features">
            <li
              v-for="feature in copy.identityFeatures"
              :key="feature"
            >
              <UIcon
                name="i-lucide-check"
                class="size-4 shrink-0 text-teal-300"
                aria-hidden="true"
              />
              <span>{{ feature }}</span>
            </li>
          </ul>
        </div>
        <div class="starter-public-identity-art">
          <img
            src="/images/talred-identity-laptop-zoom.png"
            :alt="copy.identityImageAlt"
            width="1774"
            height="887"
            loading="lazy"
          >
        </div>
      </article>
    </section>
    <section
      class="starter-public-section"
      aria-labelledby="starter-includes-title"
    >
      <h2 id="starter-includes-title">
        {{ copy.includes }}
      </h2><p class="starter-public-summary">
        {{ copy.includesIntro }}
      </p>
      <dl class="starter-public-list">
        <div
          v-for="layer in copy.layers"
          :key="layer.title"
        >
          <dt>{{ layer.title }}</dt><dd>{{ layer.description }}</dd>
        </div>
      </dl>
      <div class="starter-public-actions">
        <UButton
          :to="STARTER_REPOSITORY_URL"
          external
          color="neutral"
          variant="outline"
          icon="i-simple-icons-github"
        >
          {{ copy.source }}
        </UButton>
        <UButton
          :to="ecosystemUrl"
          external
          color="neutral"
          variant="link"
          trailing-icon="i-lucide-arrow-up-right"
        >
          {{ copy.ecosystem }}
        </UButton>
      </div>
    </section>
  </UContainer>
</template>
