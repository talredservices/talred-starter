const hostedApplicationPaths: Record<string, string> = {
  'zoltasoft-saas': '/saas',
  'tasks-demo': '/projects'
}

export function identityApplicationLaunchUrl(application: { key: string, application_url: string | null }): string | null {
  if (!application.application_url) return null

  try {
    const baseUrl = new URL(application.application_url)
    const path = hostedApplicationPaths[application.key]

    return path ? new URL(path, baseUrl).toString() : baseUrl.toString()
  } catch {
    return null
  }
}
