<script setup lang="ts">
definePageMeta({ layout: 'fresh-saas' })

const { t } = useI18n()
const localePath = useLocalePath()
const identity = useSaasIdentitySession()
const registrationUrl = identity.authorize(localePath('/saas/dashboard'), 'register')

useSeoMeta({
  title: () => `${t('freshSaas.pricingPage.title')} · SaaS`,
  description: () => t('freshSaas.pricingPage.description'),
  robots: 'noindex, nofollow'
})
</script>

<template>
  <UContainer class="starter-public">
    <StarterPublicIntro
      :title="t('freshSaas.pricingPage.title')"
      :description="t('freshSaas.pricingPage.description')"
      :eyebrow="t('freshSaas.pricingPage.eyebrow')"
    />
    <section class="starter-public-section">
      <h2>{{ t('freshSaas.pricingPage.noticeTitle') }}</h2><p class="starter-public-summary">
        {{ t('freshSaas.pricingPage.noticeDescription') }}
      </p><div class="starter-public-actions">
        <UButton
          :to="localePath('/saas')"
          color="neutral"
          variant="outline"
        >
          {{ t('freshSaas.pricingPage.back') }}
        </UButton><UButton
          :to="registrationUrl"
          external
          trailing-icon="i-lucide-arrow-right"
        >
          {{ t('freshSaas.createAccount') }}
        </UButton>
      </div>
    </section>
  </UContainer>
</template>
