<script setup lang="ts">
withDefaults(defineProps<{
  collapsed?: boolean
  compactOnMobile?: boolean
}>(), {
  collapsed: false,
  compactOnMobile: false
})
</script>

<template>
  <TalredProductBrand
    :collapsed="collapsed"
    :compact-on-mobile="compactOnMobile"
    product="SaaS"
    variant="saas"
  />
</template>
