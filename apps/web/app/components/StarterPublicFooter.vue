<script setup lang="ts">
type FooterLink = {
  label: string
  to: string
  target?: string
  rel?: string
}

type FooterColumn = {
  label: string
  children: FooterLink[]
}

defineProps<{
  columns: FooterColumn[]
  description: string
  maintainer: string
}>()
</script>

<template>
  <UFooter :ui="{ top: 'border-b border-default', container: 'max-w-6xl' }">
    <template #top>
      <div class="mx-auto w-full max-w-6xl px-4 sm:px-6 lg:px-8">
        <UFooterColumns :columns="columns">
          <template #right>
            <div class="w-full min-w-0 max-w-sm">
              <slot name="brand" />
              <p class="mt-4 text-sm leading-6 text-muted">
                {{ description }}
              </p>
            </div>
          </template>
        </UFooterColumns>
      </div>
    </template>
    <template #left>
      <div class="min-w-0 max-w-full space-y-1">
        <p class="break-words font-medium text-highlighted">
          Redouane Taleb
        </p>
        <p class="max-w-full break-words text-xs leading-5 text-muted">
          {{ maintainer }}
        </p>
        <p class="break-words pt-1 text-xs text-muted">
          &copy; {{ new Date().getFullYear() }} Talred Services
        </p>
      </div>
    </template>
    <template #right>
      <div class="flex w-full max-w-full flex-wrap items-center justify-center gap-2 sm:w-auto sm:justify-end">
        <slot name="actions" />
      </div>
    </template>
  </UFooter>
</template>
