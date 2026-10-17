<script setup lang="ts">
const props = withDefaults(defineProps<{
  collapsed?: boolean
  homeTo?: string
  settingsTo?: string
}>(), {
  collapsed: false,
  homeTo: '/dashboard',
  settingsTo: '/dashboard/settings'
})

const session = useIdentitySession()
</script>

<template>
  <IdentityUserMenu
    :session="session"
    :collapsed="props.collapsed"
    :home-to="props.homeTo"
    :settings-to="props.settingsTo"
  />
</template>
