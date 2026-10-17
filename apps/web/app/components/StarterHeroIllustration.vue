<template>
  <div
    class="starter-hero-visual z-0"
    role="img"
    :aria-label="label"
  >
    <div class="starter-hero-orbit" />
    <div class="starter-hero-orbit starter-hero-orbit--inner" />

    <div class="starter-hero-node starter-hero-node--nuxt">
      <UIcon
        name="i-simple-icons-nuxt"
        aria-hidden="true"
      />
    </div>
    <div class="starter-hero-node starter-hero-node--laravel">
      <UIcon
        name="i-simple-icons-laravel"
        aria-hidden="true"
      />
    </div>
    <div class="starter-hero-node starter-hero-node--identity">
      <UIcon
        name="i-lucide-shield-check"
        aria-hidden="true"
      />
    </div>

    <div class="starter-hero-center">
      <TalredBrandMark
        variant="starter"
        size="large"
      />
    </div>

    <span class="starter-hero-label starter-hero-label--nuxt">Nuxt BFF</span>
    <span class="starter-hero-label starter-hero-label--laravel">Laravel API</span>
    <span class="starter-hero-label starter-hero-label--identity">Identity</span>
  </div>
</template>

<script setup lang="ts">
defineProps<{
  label: string
}>()
</script>
