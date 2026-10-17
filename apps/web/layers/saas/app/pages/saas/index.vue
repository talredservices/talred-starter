<script setup lang="ts">
definePageMeta({ layout: 'fresh-saas' })

const { t } = useI18n()
const localePath = useLocalePath()
const identity = useSaasIdentitySession()
const dashboardPath = localePath('/saas/dashboard')
const signInUrl = identity.authorize(dashboardPath, 'login')
const registrationUrl = identity.authorize(dashboardPath, 'register')

useSeoMeta({
  title: () => t('freshSaas.title'),
  description: () => t('freshSaas.description'),
  robots: 'noindex, nofollow'
})
</script>

<template>
  <UContainer class="starter-public">
    <StarterPublicIntro
      :title="t('freshSaas.title')"
      :description="t('freshSaas.description')"
      :eyebrow="t('freshSaas.eyebrow')"
    >
      <UButton
        :to="registrationUrl"
        external
        size="lg"
        trailing-icon="i-lucide-arrow-right"
      >
        {{ t('freshSaas.createAccount') }}
      </UButton>
      <UButton
        :to="signInUrl"
        external
        size="lg"
        color="neutral"
        variant="outline"
      >
        {{ t('freshSaas.signIn') }}
      </UButton>
    </StarterPublicIntro>
    <section
      id="experiences"
      class="starter-public-section"
      :aria-label="t('freshSaas.experiences')"
    >
      <h2>{{ t('freshSaas.experiences') }}</h2>
      <dl class="starter-public-list">
        <div
          v-for="experience in [
            { title: t('freshSaas.publicPagesTitle'), description: t('freshSaas.publicPagesDescription') },
            { title: t('freshSaas.authenticationTitle'), description: t('freshSaas.authenticationDescription') },
            { title: t('freshSaas.dashboardTitle'), description: t('freshSaas.dashboardDescription') }
          ]"
          :key="experience.title"
        >
          <dt>{{ experience.title }}</dt><dd>{{ experience.description }}</dd>
        </div>
      </dl>
      <div class="starter-public-actions">
        <UButton
          :to="localePath('/')"
          color="neutral"
          variant="outline"
        >
          {{ t('freshSaas.backToStarter') }}
        </UButton><UButton
          :to="localePath('/saas/pricing')"
          color="neutral"
          variant="link"
        >
          {{ t('freshSaas.nav.pricing') }}
        </UButton>
      </div>
    </section>
  </UContainer>
</template>
