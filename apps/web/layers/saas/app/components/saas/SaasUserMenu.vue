<script setup lang="ts">
const props = withDefaults(defineProps<{
  collapsed?: boolean
  homeTo?: string
  settingsTo?: string
}>(), {
  collapsed: false,
  homeTo: '/saas/dashboard',
  settingsTo: '/saas/dashboard/settings'
})

const session = useSaasIdentitySession()
</script>

<template>
  <IdentityUserMenu
    :session="session"
    :collapsed="props.collapsed"
    :home-to="props.homeTo"
    :settings-to="props.settingsTo"
  />
</template>
