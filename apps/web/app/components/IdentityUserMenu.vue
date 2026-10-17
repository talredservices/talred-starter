<script setup lang="ts">
import type { DropdownMenuItem } from '@nuxt/ui'
import type { ComputedRef, Ref } from 'vue'
import { identityUserInitials } from '~/utils/identity-user'

type IdentityMenuUser = {
  name?: string
  username?: string
  email?: string
}

type IdentityMenuSession = {
  user: Ref<IdentityMenuUser | null> | ComputedRef<IdentityMenuUser | null>
  ready: Ref<boolean> | ComputedRef<boolean>
  fetch: () => Promise<unknown>
  clear: () => Promise<unknown>
}

const props = withDefaults(defineProps<{
  session: IdentityMenuSession
  collapsed?: boolean
  homeTo?: string
  settingsTo?: string
  homeLabel?: string
  logoutLabel?: string
}>(), {
  collapsed: false,
  homeTo: '/dashboard',
  settingsTo: '/dashboard/settings',
  homeLabel: undefined,
  logoutLabel: undefined
})

const colorMode = useColorMode()
const route = useRoute()
const localePath = useLocalePath()
const switchLocalePath = useSwitchLocalePath()
const { locale, t } = useI18n()

const supportedLocales = [
  { code: 'en', labelKey: 'userMenu.locales.en' },
  { code: 'fr', labelKey: 'userMenu.locales.fr' }
] as const
type SupportedLocale = (typeof supportedLocales)[number]['code']

const user = computed(() => {
  const sessionUser = props.session.user.value
  const name = sessionUser?.name || sessionUser?.username || t('userMenu.authenticatedUser')

  return {
    name,
    email: sessionUser?.email || '',
    avatar: { text: identityUserInitials(name), alt: name }
  }
})

const localizedHomeTo = computed(() => localePath(props.homeTo))
const localizedSettingsTo = computed(() => localePath(props.settingsTo))
const showHomeItem = computed(() => route.path !== localizedHomeTo.value)

const items = computed<DropdownMenuItem[][]>(() => [
  [
    {
      type: 'label',
      label: user.value.name,
      description: user.value.email || undefined,
      avatar: user.value.avatar,
      ui: { itemDescription: 'text-muted text-xs' }
    }
  ],
  [
    ...(showHomeItem.value
      ? [{
          label: props.homeLabel || t('userMenu.home'),
          icon: 'i-lucide-house',
          to: localizedHomeTo.value
        }]
      : []),
    {
      label: t('userMenu.settings'),
      icon: 'i-lucide-settings',
      to: localizedSettingsTo.value
    }
  ],
  [
    {
      label: t('userMenu.language'),
      icon: 'i-lucide-languages',
      children: supportedLocales.map(entry => ({
        label: t(entry.labelKey),
        type: 'checkbox' as const,
        checked: locale.value === entry.code,
        onUpdateChecked(checked: boolean) {
          if (!checked) return

          const path = switchLocalePath(entry.code as SupportedLocale)
          if (path && path !== route.fullPath) return navigateTo(path)
        }
      }))
    },
    {
      label: t('userMenu.appearance'),
      icon: 'i-lucide-sun-moon',
      children: [
        {
          label: t('userMenu.light'),
          icon: 'i-lucide-sun',
          type: 'checkbox' as const,
          checked: colorMode.value === 'light',
          onSelect(event: Event) {
            event.preventDefault()
            colorMode.preference = 'light'
          }
        },
        {
          label: t('userMenu.dark'),
          icon: 'i-lucide-moon',
          type: 'checkbox' as const,
          checked: colorMode.value === 'dark',
          onSelect(event: Event) {
            event.preventDefault()
            colorMode.preference = 'dark'
          }
        }
      ]
    }
  ],
  [
    {
      label: props.logoutLabel || t('userMenu.logout'),
      icon: 'i-lucide-log-out',
      async onSelect(event?: Event) {
        event?.preventDefault()
        await props.session.clear()
      }
    }
  ]
])

onMounted(async () => {
  if (!props.session.ready.value) await props.session.fetch()
})
</script>

<template>
  <UDropdownMenu
    :items="items"
    :content="{ align: 'center', collisionPadding: 12 }"
    :ui="{ content: props.collapsed ? 'w-48' : 'w-(--reka-dropdown-menu-trigger-width)' }"
  >
    <UButton
      v-bind="{
        ...user,
        label: props.collapsed ? undefined : t('userMenu.settings'),
        trailingIcon: props.collapsed ? undefined : 'i-lucide-chevrons-up-down'
      }"
      color="neutral"
      variant="ghost"
      block
      :square="props.collapsed"
      class="data-[state=open]:bg-elevated"
      :ui="{ trailingIcon: 'text-dimmed' }"
    />
  </UDropdownMenu>
</template>
