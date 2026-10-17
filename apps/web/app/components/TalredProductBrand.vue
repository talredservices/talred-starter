<script setup lang="ts">
withDefaults(defineProps<{
  collapsed?: boolean
  compactOnMobile?: boolean
  product?: string
  variant?: 'starter' | 'saas' | 'identity' | 'tasks'
  markSize?: 'small' | 'medium' | 'header'
}>(), {
  collapsed: false,
  compactOnMobile: false,
  product: 'Starter',
  variant: 'starter',
  markSize: 'header'
})
</script>

<template>
  <div class="inline-flex items-center gap-2 font-semibold tracking-[-0.04em] text-highlighted">
    <TalredBrandMark
      :variant="variant"
      :size="collapsed || compactOnMobile ? 'medium' : markSize"
    />
    <span
      v-if="!collapsed"
      class="flex flex-col justify-center gap-0 leading-none"
      :class="compactOnMobile && 'hidden sm:flex'"
    >
      <span class="text-xl leading-none">Talred</span>
      <span class="translate-x-4 text-[0.625rem] leading-none font-medium tracking-[0.04em]">
        <span class="text-highlighted">Services</span><span class="text-primary">.com</span>
      </span>
    </span>
    <span
      v-if="!collapsed && product"
      class="mt-0.5 inline-flex whitespace-nowrap rounded-full border border-primary/25 bg-primary/10 px-1.5 py-0.5 text-[0.625rem] leading-none font-semibold tracking-wide text-primary"
      :class="compactOnMobile ? 'ml-1 sm:ml-4' : 'ml-3 sm:ml-4'"
    >
      {{ product }}
    </span>
  </div>
</template>
