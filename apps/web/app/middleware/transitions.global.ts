export default defineNuxtRouteMiddleware((to, from) => {
  if (import.meta.server) return

  // Dashboard redirects can start a second navigation before the first browser
  // view transition finishes, leaving its DOM update pending until timeout.
  if (to.meta.layout === 'dashboard' || (to.params.id && from.params.id)) {
    to.meta.viewTransition = false
  }
})
