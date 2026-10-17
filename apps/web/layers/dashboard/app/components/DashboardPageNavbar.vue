<script setup lang="ts">
defineOptions({ inheritAttrs: false })

const props = defineProps<{
  title?: string
}>()
</script>

<template>
  <UDashboardNavbar
    v-bind="$attrs"
    :title="props.title"
  >
    <template #leading>
      <UDashboardSidebarCollapse size="sm" />
      <span
        aria-hidden="true"
        class="mx-1 h-5 w-px bg-default"
      />
      <slot name="leading" />
    </template>

    <template #title>
      <slot name="title">
        {{ props.title }}
      </slot>
    </template>

    <template #right>
      <slot name="right" />
    </template>
  </UDashboardNavbar>
</template>
