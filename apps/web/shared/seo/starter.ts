export function shouldNoIndexStarterPath(pathname: string) {
  return pathname.split('/').some(segment => segment === 'auth' || segment === 'dashboard')
}
