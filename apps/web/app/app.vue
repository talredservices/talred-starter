<script setup lang="ts">
import { en, fr } from '@nuxt/ui/locale'

const colorMode = useColorMode()
const { locale } = useI18n()
const uiLocale = computed(() => locale.value === 'fr' ? fr : en)
const color = computed(() => colorMode.value === 'dark' ? '#171717' : '#ffffff')
const title = 'Talred Services.com Starter'
const description = 'Talred PHP foundations for clean architecture, integrated with Laravel APIs, Nuxt applications, and the Talred Identity platform.'

useHead({
  meta: [
    { charset: 'utf-8' },
    { name: 'viewport', content: 'width=device-width, initial-scale=1' },
    { key: 'theme-color', name: 'theme-color', content: color }
  ],
  htmlAttrs: { lang: locale }
})

useSeoMeta({
  title,
  description,
  applicationName: title,
  ogTitle: title,
  ogDescription: description,
  ogSiteName: title,
  twitterCard: 'summary',
  twitterTitle: title,
  twitterDescription: description
})
</script>

<template>
  <UApp :locale="uiLocale">
    <NuxtLoadingIndicator />
    <NuxtLayout>
      <NuxtPage />
    </NuxtLayout>
  </UApp>
</template>
