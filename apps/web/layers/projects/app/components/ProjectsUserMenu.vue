<script setup lang="ts">
const props = withDefaults(defineProps<{ collapsed?: boolean }>(), { collapsed: false })
const session = useProjectsIdentitySession()
const { t } = useI18n()
</script>

<template>
  <IdentityUserMenu
    :session="session"
    :collapsed="props.collapsed"
    home-to="/projects"
    settings-to="/projects/dashboard/settings"
    :home-label="t('projects.nav.home')"
    :logout-label="t('projects.nav.logout')"
  />
</template>
