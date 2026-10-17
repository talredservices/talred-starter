<script setup lang="ts">
defineProps<{
  collapsed?: boolean
  compactOnMobile?: boolean
}>()
</script>

<template>
  <TalredProductBrand
    :collapsed="collapsed"
    :compact-on-mobile="compactOnMobile"
    product="Tasks"
    variant="tasks"
  />
</template>
