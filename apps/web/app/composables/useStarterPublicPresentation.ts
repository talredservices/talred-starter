const content = {
  en: {
    skip: 'Skip to content', openMenu: 'Open menu', closeMenu: 'Close menu',
    title: 'A clear pattern for clean architecture.',
    description: 'A practical clean-architecture reference built on Talred PHP, integrated with Laravel, Nuxt, and Talred Identity—clear for developers to learn and AI agents to follow.',
    explore: 'Explore the examples', docs: 'Documentation', source: 'Source on GitHub', ecosystem: 'Talred ecosystem', open: 'Open example',
    examples: 'What it includes', examplesIntro: 'Explore the reference through its working applications and shared platform.',
    workspace: 'Workspace', workspaceDescription: 'A Nuxt dashboard connected to Talred Identity for organizations, workspaces, membership, and application resources.',
    tasks: 'Talred Tasks', tasksDescription: 'Explore how Talred PHP brings clean architecture, domain-driven design, and CQRS to a Laravel API, then connects it to a Nuxt client. See how clear contracts make the API ready to integrate across services.',
    saas: 'SaaS', saasDescription: 'A Nuxt product experience that uses Talred Identity for hosted sign-in and a protected dashboard.',
    identity: 'Talred Identity', identityDescription: 'A shared platform for authentication, accounts, projects, roles, clients, and access policies across connected applications.',
    includes: 'What it brings', includesIntro: 'A working reference that makes clean architecture easier to understand, follow, and apply.',
    layers: [
      { title: 'Clear boundaries', description: 'See how domain rules, application use cases, APIs, integrations, and persistence fit together.' },
      { title: 'End-to-end examples', description: 'Trace real flows across Talred PHP, Laravel APIs, Nuxt clients, and Talred Identity.' },
      { title: 'Patterns you can reuse', description: 'Adapt proven structures and integration patterns to your own services.' },
      { title: 'Built for people and agents', description: 'Navigate consistent code and documentation that help developers and AI agents understand the system.' }
    ],
    aboutTitle: 'About Talred Starter', aboutIntro: 'A working reference for building Laravel APIs and Nuxt clients with clear boundaries, domain-driven design, and CQRS.',
    story: [
      'Talred Starter brings Talred PHP patterns to life in a connected application. The Laravel API separates domain rules, application use cases, transport contracts, and persistence, making each part easier to understand and evolve.',
      'A Nuxt client and its BFF consume the API through explicit contracts while keeping browser concerns and private credentials on the server. Talred Identity provides shared authentication and project access as an independent platform.',
      'Use the examples to trace a request across these boundaries, then adapt the patterns to your own services. The code and documentation aim to be clear for developers to learn from and AI agents to navigate.'
    ],
    scopeTitle: 'What the Starter includes', scopeIntro: 'A software reference and evaluation environment. Hosting and paid support are not sold here.',
    scopeNote: 'Before deploying your own application, configure its Identity clients, infrastructure, data policies, and operational controls.',
    tasksEyebrow: '', tasksAboutTitle: 'About Talred Tasks', tasksAboutIntro: 'See Talred PHP bring clean architecture, domain-driven design, and CQRS into a Laravel API—and connect it to a Nuxt client.',
    tasksStory: [
      'Talred Tasks demonstrates clean architecture by keeping domain rules, CQRS use cases, API contracts, and persistence in clear boundaries.',
      'Talred PHP brings domain-driven design and CQRS patterns into Laravel. The API exposes explicit contracts that can be consumed by the Nuxt client or integrated with other services; the Nuxt BFF keeps browser-facing behavior and private credentials on the server.',
      'Talred Identity provides shared sign-in and project access. Explore the working board, then follow the repository and integration guide to see how each boundary works.'
    ], tasksFeaturesTitle: 'Architecture patterns, demonstrated end to end', tasksFeatures: [
      { title: 'Clean architecture and DDD', description: 'Model domain rules explicitly and keep the domain independent from Laravel, transport, and persistence.', icon: 'i-lucide-layers-3' },
      { title: 'CQRS with clear API contracts', description: 'Trace commands and queries through application handlers to explicit Laravel API responses designed for service integration.', icon: 'i-lucide-route' },
      { title: 'Nuxt integration with shared Identity', description: 'Connect a Nuxt client and BFF to the API while Talred Identity handles shared sign-in and project access.', icon: 'i-lucide-shield-check' }
    ], tasksOpen: 'Open the task board', maintained: 'Maintained by Redouane Taleb', demoNotice: 'Evaluation environment · use sample data.'
  },
  fr: {
    skip: 'Aller au contenu', openMenu: 'Ouvrir le menu', closeMenu: 'Fermer le menu',
    title: 'Un modèle clair pour une architecture propre.',
    description: 'Une référence pratique d’architecture propre fondée sur Talred PHP et intégrée à Laravel, Nuxt et Talred Identity — claire pour les développeurs et facile à suivre par les agents IA.',
    explore: 'Explorer les exemples', docs: 'Documentation', source: 'Code sur GitHub', ecosystem: 'Écosystème Talred', open: 'Ouvrir l’exemple',
    examples: 'Ce que comprend Starter', examplesIntro: 'Découvrez la référence à travers ses applications fonctionnelles et sa plateforme partagée.',
    workspace: 'Workspace', workspaceDescription: 'Un tableau de bord Nuxt relié à Talred Identity pour les organisations, espaces, membres et ressources applicatives.',
    tasks: 'Talred Tasks', tasksDescription: 'Découvrez comment Talred PHP apporte l’architecture propre, le DDD et CQRS à une API Laravel, puis la relie à un client Nuxt. Voyez comment des contrats clairs facilitent l’intégration entre services.',
    saas: 'SaaS', saasDescription: 'Une expérience produit Nuxt qui utilise Talred Identity pour la connexion hébergée et un tableau de bord protégé.',
    identity: 'Talred Identity', identityDescription: 'Une plateforme partagée pour l’authentification, les comptes, projets, rôles, clients et règles d’accès entre applications.',
    includes: 'Ce que cela apporte', includesIntro: 'Une référence fonctionnelle qui rend l’architecture propre plus facile à comprendre, suivre et appliquer.',
    layers: [
      { title: 'Des frontières claires', description: 'Découvrez comment s’articulent les règles métier, cas d’usage, API, intégrations et persistance.' },
      { title: 'Des exemples de bout en bout', description: 'Suivez des flux concrets à travers Talred PHP, les API Laravel, les clients Nuxt et Talred Identity.' },
      { title: 'Des modèles réutilisables', description: 'Adaptez des structures et modèles d’intégration éprouvés à vos propres services.' },
      { title: 'Pensé pour les développeurs et les agents', description: 'Un code et une documentation cohérents pour aider les développeurs et agents IA à comprendre le système.' }
    ],
    aboutTitle: 'À propos de Talred Starter', aboutIntro: 'Une référence fonctionnelle pour créer des API Laravel et des clients Nuxt avec des frontières claires, le DDD et CQRS.',
    story: [
      'Talred Starter met en pratique les modèles Talred PHP dans une application connectée. L’API Laravel sépare les règles métier, cas d’usage, contrats de transport et persistance pour rendre chaque partie plus facile à comprendre et à faire évoluer.',
      'Un client Nuxt et son BFF consomment l’API au moyen de contrats explicites, tout en gardant les préoccupations du navigateur et les identifiants privés côté serveur. Talred Identity fournit l’authentification partagée et les accès aux projets comme plateforme indépendante.',
      'Suivez une requête à travers ces frontières dans les exemples, puis adaptez les modèles à vos services. Le code et la documentation sont conçus pour aider les développeurs à apprendre et les agents IA à s’y repérer.'
    ],
    scopeTitle: 'Ce que Starter inclut', scopeIntro: 'Une référence logicielle et un environnement d’évaluation. Aucun hébergement ou support payant n’est vendu ici.',
    scopeNote: 'Avant de déployer votre application, configurez ses clients Identity, son infrastructure, ses politiques de données et ses contrôles opérationnels.',
    tasksEyebrow: '', tasksAboutTitle: 'À propos de Talred Tasks', tasksAboutIntro: 'Découvrez comment Talred PHP apporte l’architecture propre, le DDD et CQRS à une API Laravel, reliée à un client Nuxt.',
    tasksStory: [
      'Talred Tasks démontre l’architecture propre en séparant clairement les règles métier, les cas d’usage CQRS, les contrats API et la persistance.',
      'Talred PHP apporte les modèles du DDD et de CQRS à Laravel. L’API expose des contrats explicites, utilisables par le client Nuxt ou intégrables à d’autres services ; le BFF Nuxt garde l’expérience navigateur et les identifiants privés côté serveur.',
      'Talred Identity fournit la connexion partagée et les accès aux projets. Explorez le tableau, puis consultez le dépôt et le guide d’intégration pour comprendre chaque frontière.'
    ], tasksFeaturesTitle: 'Les modèles d’architecture, illustrés de bout en bout', tasksFeatures: [
      { title: 'Architecture propre et DDD', description: 'Modélisez explicitement les règles métier et gardez le domaine indépendant de Laravel, du transport et de la persistance.', icon: 'i-lucide-layers-3' },
      { title: 'CQRS et contrats API clairs', description: 'Suivez commandes et requêtes dans les handlers applicatifs jusqu’aux réponses Laravel, conçues pour l’intégration entre services.', icon: 'i-lucide-route' },
      { title: 'Nuxt et Identity partagée', description: 'Reliez un client Nuxt et son BFF à l’API, tandis que Talred Identity gère la connexion et les accès aux projets.', icon: 'i-lucide-shield-check' }
    ], tasksOpen: 'Ouvrir le tableau de tâches', maintained: 'Maintenu par Redouane Taleb', demoNotice: 'Environnement d’évaluation · utilisez des données fictives.'
  }
} as const

export function useStarterPublicPresentation() {
  const { locale } = useI18n()
  return computed(() => locale.value === 'fr' ? content.fr : content.en)
}
