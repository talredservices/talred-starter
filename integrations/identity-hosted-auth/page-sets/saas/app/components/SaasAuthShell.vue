<script setup lang="ts">
import type { Component } from 'vue'
import SaasHostedAuthHeader from './SaasHostedAuthHeader.vue'

const { page } = defineProps<{
  page: Component
}>()

provide('identity-auth-page-set', 'saas')
provide('identity-auth-show-login-brand-mark', true)
</script>

<template>
  <IdentityHostedAuthPageSetShell
    :page="page"
    :header="SaasHostedAuthHeader"
  />
</template>
