<script setup lang="ts">
import { STARTER_REPOSITORY_URL } from '~/utils/starter-links'

definePageMeta({ layout: 'landing' })
const copy = useStarterPublicPresentation()
const config = useRuntimeConfig()
const localePath = useLocalePath()
useSeoMeta({ title: () => copy.value.aboutTitle, description: () => copy.value.aboutIntro })
</script>

<template>
  <UContainer class="starter-public">
    <StarterPublicIntro
      :title="copy.aboutTitle"
      :description="copy.aboutIntro"
    />
    <div class="starter-public-story">
      <p
        v-for="paragraph in copy.story"
        :key="paragraph"
      >
        {{ paragraph }}
      </p>
    </div>
    <section class="starter-public-section">
      <h2>{{ copy.scopeTitle }}</h2>
      <p class="starter-public-summary">
        {{ copy.scopeIntro }}
      </p>
      <dl class="starter-public-list">
        <div
          v-for="layer in copy.layers"
          :key="layer.title"
        >
          <dt>{{ layer.title }}</dt><dd>{{ layer.description }}</dd>
        </div>
      </dl>
      <p class="starter-public-summary">
        {{ copy.scopeNote }}
      </p>
    </section>
    <div class="starter-public-section starter-public-actions">
      <UButton
        :to="localePath('/#demos')"
        trailing-icon="i-lucide-arrow-right"
      >
        {{ copy.explore }}
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
        variant="outline"
        icon="i-simple-icons-github"
      >
        {{ copy.source }}
      </UButton>
    </div>
  </UContainer>
</template>
