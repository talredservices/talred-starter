<script setup lang="ts">
defineProps<{ total: number, pageSize: number }>()
const page = defineModel<number>('page', { required: true })
const copy = useTasksDashboardPresentation()
</script>

<template>
  <UPagination
    v-model:page="page"
    :total="total"
    :items-per-page="pageSize"
  >
    <template #first>
      <UButton
        :aria-label="copy.firstPage"
        icon="i-lucide-chevrons-left"
        color="neutral"
        variant="ghost"
      />
    </template>
    <template #prev>
      <UButton
        :aria-label="copy.prevPage"
        icon="i-lucide-chevron-left"
        color="neutral"
        variant="ghost"
      />
    </template>
    <template #item="{ item }">
      <UButton
        :aria-label="`${copy.pageLabel} ${item.value}`"
        :label="String(item.value)"
        :color="item.value === page ? 'primary' : 'neutral'"
        :variant="item.value === page ? 'solid' : 'ghost'"
      />
    </template>
    <template #next>
      <UButton
        :aria-label="copy.nextPage"
        icon="i-lucide-chevron-right"
        color="neutral"
        variant="ghost"
      />
    </template>
    <template #last>
      <UButton
        :aria-label="copy.lastPage"
        icon="i-lucide-chevrons-right"
        color="neutral"
        variant="ghost"
      />
    </template>
  </UPagination>
</template>
