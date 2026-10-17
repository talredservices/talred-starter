<script setup lang="ts">
import { useStarterEcosystemUrl } from '~/composables/useStarterEcosystemUrl'
import { STARTER_REPOSITORY_URL } from '~/utils/starter-links'

const copy = useSaasTemplatePresentation()
const localePath = useLocalePath()
const runtimeConfig = useRuntimeConfig()
const ecosystemUrl = useStarterEcosystemUrl()

const columns = computed<Array<{ label: string, children: Array<{ label: string, to: string, target?: string, rel?: string }> }>>(() => [
  {
    label: copy.value.footer.product,
    children: [
      { label: copy.value.footer.overview, to: localePath('/') },
      { label: copy.value.footer.about, to: localePath('/about') }
    ]
  },
  {
    label: copy.value.footer.resources,
    children: [
      { label: copy.value.footer.support, to: runtimeConfig.public.docsUrl },
      { label: 'Talred', to: ecosystemUrl.value },
      { label: copy.value.footer.sourceCode, to: STARTER_REPOSITORY_URL, target: '_blank', rel: 'noopener noreferrer' }
    ]
  },
  {
    label: copy.value.footer.legal,
    children: [
      { label: copy.value.footer.terms, to: localePath('/terms') },
      { label: copy.value.footer.privacy, to: localePath('/privacy') }
    ]
  }
])
</script>

<template>
  <StarterPublicFooter
    :columns="columns"
    :description="copy.footer.description"
    :maintainer="copy.footer.maintainer"
  >
    <template #brand>
      <StarterBrand />
    </template>
    <template #actions>
      <LanguageSwitcher />
      <UColorModeButton
        color="neutral"
        variant="ghost"
      />
    </template>
  </StarterPublicFooter>
</template>
