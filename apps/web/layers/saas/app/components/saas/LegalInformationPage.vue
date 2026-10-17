<script setup lang="ts">
const props = defineProps<{
  kind: 'terms' | 'privacy'
  productName?: string
}>()

const copy = useSaasTemplatePresentation()
const policy = computed(() => props.kind === 'terms'
  ? { title: copy.value.legal.termsTitle, sections: copy.value.legal.terms }
  : { title: copy.value.legal.privacyTitle, sections: copy.value.legal.privacy })

useSeoMeta({
  title: () => `${policy.value.title} · ${props.productName ?? copy.value.brand}`,
  robots: 'noindex, nofollow'
})

const notice = computed(() => props.productName
  ? copy.value.legal.notice.replace('Talred Services.com Starter', props.productName)
  : copy.value.legal.notice)
</script>

<template>
  <UContainer class="starter-public starter-public-narrative">
    <StarterPublicIntro
      :title="policy.title"
      :description="notice"
      :eyebrow="copy.legal.updated"
    />
    <div class="starter-public-section space-y-8">
      <section
        v-for="section in policy.sections"
        :key="section.title"
      >
        <h2 class="text-xl font-semibold">
          {{ section.title }}
        </h2><p class="mt-3 text-sm leading-7 text-muted">
          {{ section.body }}
        </p>
      </section>
    </div>
  </UContainer>
</template>
