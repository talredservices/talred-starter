<script setup lang="ts">
import type { NuxtError } from '#app'

const props = defineProps<{
  error: NuxtError
}>()

const { locale, t } = useI18n()
const localePath = useLocalePath()
const statusCode = computed(() => props.error.statusCode || 500)
const isNotFound = computed(() => statusCode.value === 404)
const title = computed(() => isNotFound.value
  ? t('error.notFoundTitle')
  : t('error.genericTitle'))
const description = computed(() => isNotFound.value
  ? t('error.notFoundDescription')
  : t('error.genericDescription'))

useHead({
  htmlAttrs: { lang: locale }
})

useSeoMeta({
  title: () => `${title.value} · Talred Starter`,
  description
})
</script>

<template>
  <UApp>
    <main class="min-h-[calc(100vh-4rem)] flex items-center justify-center px-6 py-16">
      <UPageCard
        variant="subtle"
        class="w-full max-w-xl"
      >
        <div class="flex flex-col items-center text-center gap-6 py-6">
          <div class="space-y-3">
            <p class="text-sm font-semibold text-primary">
              {{ statusCode }}
            </p>
            <h1 class="text-2xl sm:text-3xl font-semibold text-highlighted">
              {{ title }}
            </h1>
            <p class="text-muted max-w-md">
              {{ description }}
            </p>
          </div>

          <UButton
            icon="i-lucide-house"
            :label="t('error.home')"
            :to="localePath('/')"
          />
        </div>
      </UPageCard>
    </main>
  </UApp>
</template>
