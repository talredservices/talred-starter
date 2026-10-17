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
        <div class="inline-flex items-center gap-2 font-semibold tracking-[-0.04em] text-highlighted">
          <IdentityAuthBrandMark
            :logo-url="brand?.presentation.logoUrl"
            :accent-color="brand?.presentation.accentColor"
            size="header"
          />
          <span class="flex flex-col justify-center gap-0 leading-none">
            <span class="text-xl leading-none">Talred</span>
            <span class="translate-x-4 text-[0.625rem] leading-none font-medium tracking-[0.04em]">
              <span class="text-highlighted">Services</span><span class="text-primary">.com</span>
            </span>
          </span>
          <span class="mt-0.5 ml-4 inline-flex rounded-full border border-primary/25 bg-primary/10 px-1.5 py-0.5 text-[0.625rem] leading-none font-semibold tracking-wide text-primary">
            {{ brand?.presentation.productName ?? 'Tasks' }}
          </span>
        </div>
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
