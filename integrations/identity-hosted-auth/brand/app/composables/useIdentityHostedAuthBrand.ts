type ReactiveValue<T> = {
  readonly value: T
}

export type IdentityHostedAuthBrand = {
  returnUrl: string
  presentation: {
    productName: string
    logoUrl: string | null
    accentColor: string | null
  }
}

export function useIdentityHostedAuthBrand() {
  return inject<ReactiveValue<IdentityHostedAuthBrand | null>>(
    'identity-auth-brand',
    computed(() => null)
  )
}
