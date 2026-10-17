<script setup lang="ts">
defineProps<{ items: { label: string, value: string | number, icon: string }[], loading?: boolean }>()
</script>

<template>
  <dl
    class="grid grid-cols-2 gap-3 sm:grid-cols-4"
    :aria-busy="loading"
  >
    <UCard
      v-for="item in items"
      :key="item.label"
      variant="subtle"
      :ui="{ body: 'p-3 sm:p-4' }"
    >
      <div class="flex items-center justify-between gap-2">
        <div class="min-w-0">
          <dt class="truncate text-xs font-medium text-muted sm:text-sm">
            {{ item.label }}
          </dt>
          <dd class="mt-1 text-xl font-semibold tabular-nums text-highlighted sm:text-2xl">
            <USkeleton
              v-if="loading"
              class="h-7 w-12"
            /><template v-else>
              {{ item.value }}
            </template>
          </dd>
        </div>
        <UIcon
          :name="item.icon"
          class="size-4 shrink-0 text-primary sm:size-5"
        />
      </div>
    </UCard>
  </dl>
</template>
