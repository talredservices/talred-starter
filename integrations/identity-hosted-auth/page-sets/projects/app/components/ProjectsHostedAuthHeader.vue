<script setup lang="ts">
import type { DropdownMenuItem } from '@nuxt/ui'

type HostedProjectsBrand = {
  returnUrl: string
  presentation: {
    productName: string
    logoUrl: string | null
    accentColor: string | null
  }
}

const props = defineProps<{
  brand?: HostedProjectsBrand | null
}>()

const route = useRoute()
const router = useRouter()
const localePath = useLocalePath()
const switchLocalePath = useSwitchLocalePath()
const { locale } = useI18n()

const supportedLocales = [
  { code: 'en', name: 'English' },
  { code: 'fr', name: 'Français' }
] as const

type SupportedLocale = (typeof supportedLocales)[number]['code']

function switchLocale(code: SupportedLocale) {
  const path = switchLocalePath(code)
  if (!path) return

  const localizedRoute = router.resolve(path)
  return navigateTo({
    path: localizedRoute.path,
    query: route.query,
    hash: route.hash
  })
}

const localeItems = computed<DropdownMenuItem[][]>(() => [
  supportedLocales.map(entry => ({
    label: entry.name,
    type: 'checkbox',
    checked: locale.value === entry.code,
    onUpdateChecked(checked: boolean) {
      if (checked) return switchLocale(entry.code)
    }
  }))
])

const applicationOrigin = computed(() => {
  try {
    return new URL(props.brand?.returnUrl ?? '').origin
  } catch {
    return null
  }
})

function applicationPath(): string {
  const localizedPath = localePath('/projects')
  return applicationOrigin.value
    ? new URL(localizedPath, applicationOrigin.value).toString()
    : localizedPath
}
</script>

<template>
  <UHeader :ui="{ container: 'max-w-7xl' }">
    <template #left>
      <NuxtLink
        :to="applicationPath()"
        external
        class="rounded-lg focus-visible:outline-2 focus-visible:outline-primary"
      >
        <TalredProductBrand product="Tasks" variant="tasks" />
      </NuxtLink>
    </template>

    <template #right>
      <UDropdownMenu
        :items="localeItems"
        :content="{ align: 'end', collisionPadding: 8 }"
        :ui="{ content: 'min-w-40' }"
      >
        <UButton
          :label="locale.toUpperCase()"
          trailing-icon="i-lucide-chevron-down"
          size="sm"
          variant="ghost"
          color="neutral"
          class="rounded-full"
        />
      </UDropdownMenu>
      <UColorModeButton
        color="neutral"
        variant="ghost"
      />
    </template>
  </UHeader>
</template>
