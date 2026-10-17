<script setup lang="ts">
defineProps<{ title: string, description: string, eyebrow?: string }>()
const route = useRoute()
const localePath = useLocalePath()
const config = useRuntimeConfig()
useHead({
  link: computed(() => [{
    rel: 'canonical',
    href: new URL(localePath(route.path.replace(/^\/(?:en|fr)(?=\/|$)/, '') || '/'), String(config.public.siteUrl)).toString()
  }])
})
</script>

<template>
  <header class="starter-public-intro">
    <p
      v-if="eyebrow"
      class="starter-public-kicker"
    >
      {{ eyebrow }}
    </p>
    <h1>{{ title }}</h1>
    <p class="starter-public-lead">
      {{ description }}
    </p>
    <div
      v-if="$slots.default"
      class="starter-public-actions"
    >
      <slot />
    </div>
  </header>
</template>
