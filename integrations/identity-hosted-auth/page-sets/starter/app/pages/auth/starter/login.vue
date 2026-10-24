<script setup lang="ts">
import IdentityHostedLoginPage from '#layers/auth/default-pages/app/components/IdentityHostedLoginPage.vue'

definePageMeta({ layout: 'identity-auth', middleware: 'identity-live-auth' })
</script>

<template>
  <StarterAuthShell :page="IdentityHostedLoginPage" />
</template>
