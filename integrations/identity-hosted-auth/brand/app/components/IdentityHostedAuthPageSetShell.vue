<script setup lang="ts">
import type { Component } from 'vue'

defineProps<{
  page: Component
  header: Component
}>()

const brand = useIdentityHostedAuthBrand()
</script>

<template>
  <div class="identity-hosted-auth-page-set-shell">
    <component
      :is="header"
      :brand="brand"
    />
    <div class="identity-hosted-auth-page-set-content">
      <component :is="page" />
    </div>
  </div>
</template>

<style scoped>
.identity-hosted-auth-page-set-shell {
  display: flex;
  flex: 1;
  flex-direction: column;
  min-height: 0;
  width: 100%;
}

.identity-hosted-auth-page-set-content {
  align-items: center;
  display: flex;
  flex: 1;
  justify-content: center;
  padding: 2rem 1rem;
}
</style>
