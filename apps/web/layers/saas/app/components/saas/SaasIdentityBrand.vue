<script setup lang="ts">
defineProps<{
  compactOnMobile?: boolean
}>()
</script>

<template>
  <TalredProductBrand
    :compact-on-mobile="compactOnMobile"
    product="SaaS"
    variant="saas"
  />
</template>
