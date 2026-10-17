<script setup lang="ts">
const technologies = [
  { name: 'PHP', icon: 'i-simple-icons-php', color: '#777BB4' },
  { name: 'Laravel', icon: 'i-simple-icons-laravel', color: '#FF2D20' },
  { name: 'Nuxt', icon: 'i-simple-icons-nuxt', color: '#00DC82' },
  { name: 'TypeScript', icon: 'i-simple-icons-typescript', color: '#3178C6' },
  { name: 'Talred Identity', icon: 'i-lucide-shield-check', color: 'var(--ui-primary)' },
  { name: 'MySQL', icon: 'i-simple-icons-mysql', color: '#4479A1' },
  { name: 'Docker Compose', icon: 'i-simple-icons-docker', color: '#2496ED' }
] as const
</script>

<template>
  <ul
    class="flex flex-wrap items-center justify-center gap-x-8 gap-y-4 border-y border-default py-5 text-sm font-medium text-toned"
    aria-label="Technology stack"
  >
    <li
      v-for="technology in technologies"
      :key="technology.name"
      class="inline-flex items-center gap-2 whitespace-nowrap"
    >
      <UIcon
        :name="technology.icon"
        class="size-5 shrink-0"
        :style="{ color: technology.color }"
        aria-hidden="true"
      />
      {{ technology.name }}
    </li>
  </ul>
</template>
