<script setup lang="ts">
const { locale } = useI18n()

const description = computed(() => locale.value === 'fr'
  ? 'Talred Starter est en version bêta. Le projet et sa documentation continuent d’être améliorés.'
  : 'Talred Starter is in beta. The project and its documentation are still being improved.')
</script>

<template>
  <div class="border-b border-default">
    <UAlert
      color="warning"
      variant="subtle"
      class="app-container border-0 bg-transparent py-3"
    >
      <template #leading>
        <div class="flex size-8 items-center justify-center">
          <UIcon
            name="i-lucide-flask-conical"
            class="size-5"
            aria-hidden="true"
          />
        </div>
      </template>
      <template #title>
        Beta
      </template>
      <template #description>
        {{ description }}
      </template>
    </UAlert>
  </div>
</template>
