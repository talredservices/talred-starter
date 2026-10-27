<script setup lang="ts">
import type { Component } from 'vue'
import ProjectsHostedAuthHeader from './ProjectsHostedAuthHeader.vue'

const { page } = defineProps<{
  page: Component
}>()

provide('identity-auth-page-set', 'projects')
provide('identity-auth-show-login-brand-mark', true)
</script>

<template>
  <IdentityHostedAuthPageSetShell
    :page="page"
    :header="ProjectsHostedAuthHeader"
  />
</template>
