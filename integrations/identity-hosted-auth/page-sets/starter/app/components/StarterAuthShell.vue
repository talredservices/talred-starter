<script setup lang="ts">
import type { Component } from 'vue'
import StarterHostedAuthHeader from './StarterHostedAuthHeader.vue'

const { page } = defineProps<{
  page: Component
}>()

provide('identity-auth-page-set', 'starter')
provide('identity-auth-show-login-brand-mark', true)
</script>

<template>
  <IdentityHostedAuthPageSetShell
    :page="page"
    :header="StarterHostedAuthHeader"
  />
</template>
