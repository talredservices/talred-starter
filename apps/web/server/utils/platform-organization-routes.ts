const id = '[0-9a-fA-F]{8}-(?:[0-9a-fA-F]{4}-){3}[0-9a-fA-F]{12}'

const routeRules: Array<{ pattern: RegExp, methods: string[] }> = [
  { pattern: new RegExp(`^workspaces/${id}/overview$`), methods: ['GET'] },
  { pattern: new RegExp(`^workspaces/${id}$`), methods: ['GET', 'PATCH'] },
  { pattern: new RegExp(`^workspaces/${id}/members$`), methods: ['GET'] },
  { pattern: new RegExp(`^workspaces/${id}/members/${id}$`), methods: ['PATCH', 'DELETE'] },
  { pattern: new RegExp(`^workspaces/${id}/ownership$`), methods: ['POST'] },
  { pattern: new RegExp(`^workspaces/${id}/invitations$`), methods: ['GET', 'POST'] },
  { pattern: new RegExp(`^workspaces/${id}/invitations/${id}$`), methods: ['DELETE'] },
  { pattern: /^invitations\/accept$/, methods: ['POST'] },
  { pattern: new RegExp(`^workspaces/${id}/resources/identity-projects$`), methods: ['GET', 'POST'] },
  { pattern: new RegExp(`^workspaces/${id}/resources/identity-projects/available$`), methods: ['GET'] },
  { pattern: new RegExp(`^workspaces/${id}/resources/identity-projects/${id}$`), methods: ['DELETE'] }
]

export function isPlatformOrganizationRouteAllowed(path: string, method: string): boolean {
  const normalizedMethod = method.toUpperCase()
  return routeRules.some(rule => rule.pattern.test(path) && rule.methods.includes(normalizedMethod))
}
