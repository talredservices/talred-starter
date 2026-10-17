<script setup lang="ts">
withDefaults(defineProps<{
  collapsed?: boolean
  brand?: string
  markSize?: 'small' | 'medium'
  variant?: 'starter' | 'saas' | 'identity' | 'tasks'
}>(), {
  collapsed: false,
  brand: 'Starter',
  markSize: 'small',
  variant: 'starter'
})
</script>

<template>
  <TalredProductBrand
    :collapsed="collapsed"
    :product="brand"
    :variant="variant"
    :mark-size="markSize"
  />
</template>
