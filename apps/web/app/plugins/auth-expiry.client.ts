import { defineNuxtPlugin } from '#app'

export default defineNuxtPlugin(() => {
  const session = useIdentitySession()
  const localePath = useLocalePath()
  let expiryTimer: ReturnType<typeof setTimeout> | undefined

  const clearExpiryTimer = () => {
    if (expiryTimer) clearTimeout(expiryTimer)
    expiryTimer = undefined
  }

  watch(
    () => (session.user.value as { expiresAt?: string | null } | null)?.expiresAt,
    (expiresAt) => {
      clearExpiryTimer()
      if (!expiresAt) return

      const delay = Date.parse(expiresAt) - Date.now()
      if (!Number.isFinite(delay)) return

      const expire = async () => {
        await session.clear(`${localePath('/auth/login')}?expired=1`)
      }

      if (delay <= 0) {
        void expire()
        return
      }

      expiryTimer = setTimeout(expire, Math.min(Math.max(0, delay - 2_000), 2_147_483_647))
    },
    { immediate: true }
  )
})
