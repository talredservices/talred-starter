export function isIdentityUnauthorized(error: unknown): boolean {
  if (!error || typeof error !== 'object') return false

  const candidate = error as {
    status?: unknown
    statusCode?: unknown
    response?: { status?: unknown }
    data?: { statusCode?: unknown }
  }

  return [candidate.status, candidate.statusCode, candidate.response?.status, candidate.data?.statusCode]
    .includes(401)
}
