const english = {
  brand: 'Talred Services.com Starter',
  navigation: {
    home: 'Home',
    about: 'About',
    login: 'Log in',
    github: 'GitHub',
    start: 'Create a demo account',
    dashboard: 'Dashboard'
  },
  footer: {
    description: 'Talred PHP foundations for clean architecture, integrated with Laravel APIs, Nuxt applications, and Talred Identity.',
    maintainer: 'Independent full-stack developer · Talred Services maintainer',
    product: 'Starter',
    resources: 'Resources',
    legal: 'Legal',
    overview: 'Home',
    about: 'About the Starter',
    support: 'Documentation',
    sourceCode: 'Source code',
    terms: 'Terms',
    privacy: 'Privacy'
  },
  about: {
    eyebrow: 'About the Starter',
    title: 'A foundation for services with clear boundaries',
    description: 'Talred Services.com Starter brings together a Nuxt client and BFF, Laravel API patterns, Identity integration, and a shared local development stack.',
    architectureTitle: 'Keep responsibilities in the right place',
    architectureDescription: 'The starter demonstrates how presentation, transport, use cases, domain rules, persistence, and shared identity fit together.',
    items: [
      { title: 'Nuxt client and BFF', description: 'Build the interface and browser-facing server operations while keeping private credentials on the server.', icon: 'i-lucide-layout-dashboard' },
      { title: 'Laravel API', description: 'Organize domain rules, application use cases, API contracts, and infrastructure adapters in explicit layers.', icon: 'i-lucide-folder-kanban' },
      { title: 'Talred Identity', description: 'Use the shared Identity platform for authentication, accounts, membership, and access policy.', icon: 'i-lucide-shield-check' },
      { title: 'Local development', description: 'Run the documented application and supporting services from the shared Compose workspace.', icon: 'i-simple-icons-docker' }
    ],
    examplesTitle: 'Included examples',
    examplesDescription: 'Identity demonstrates shared authentication. Tasks follows a domain-driven Laravel API through a Nuxt BFF, while SaaS shows public product pages and a protected dashboard.'
  },
  pricing: {
    eyebrow: 'Pricing',
    title: 'No commercial pricing is published for this foundation',
    description: 'Talred Services.com Starter is a development foundation and set of examples. This site does not offer paid plans, hosting, or support packages.',
    notice: 'Commercial plans have not been defined for this Starter demo. Any future hosting or support offer will need its own published scope and pricing.',
    foundationHeading: 'Explore the foundation',
    foundationDescription: 'Review the documentation and included demos to understand what is in the workspace before choosing how to adapt and deploy it.',
    features: ['Nuxt application and BFF examples', 'Laravel API architecture patterns', 'Talred Identity integration', 'SaaS public pages and dashboard example', 'Local development stack'],
    action: 'Read the documentation',
    faqTitle: 'Pricing questions',
    faq: [
      { label: 'Can I buy a plan here?', content: 'No. This Starter site is an exploration of a development foundation and does not currently sell a subscription.' },
      { label: 'Does this page include hosting or support?', content: 'No hosting or support service is offered here. Deployment and support choices belong to the application owner.' }
    ]
  },
  legal: {
    termsTitle: 'Terms of use',
    privacyTitle: 'Privacy information',
    updated: 'Draft for review',
    notice: 'This page is draft information for the Talred Services.com Starter demonstration, not a final legal policy. Confirm actual data handling, retention, contact details, and applicable terms before publishing a production service.',
    terms: [
      { title: 'Purpose of this site', body: 'This site presents a software product demonstration and related functionality. It is provided to help visitors evaluate the experience and understand how the demonstration operates.' },
      { title: 'Demo accounts and content', body: 'Use demonstration accounts only for evaluation. Do not submit confidential, sensitive, or business-critical information to a demo environment.' },
      { title: 'Availability and changes', body: 'The examples and local services may change as the project evolves. No service-level or commercial commitments are made by this demonstration.' },
      { title: 'Production use', body: 'A deployed application needs its own reviewed terms, service commitments, security controls, and operational policies before it is offered to users.' }
    ],
    privacy: [
      { title: 'Information processed', body: 'When you use a demonstration, the configured Identity and application services may process account details and information you submit while exploring the example.' },
      { title: 'Cookies and browser storage', body: 'First-party session cookies keep authenticated requests associated with your sign-in. The i18n_redirected cookie stores the selected language, and browser local storage stores the color-mode choice under nuxt-color-mode. These settings support language and appearance only. The current demonstration build does not configure advertising or analytics trackers. You can change the preferences with the visible language and appearance controls or clear them in your browser site-data settings; blocking the session cookie prevents sign-in.' },
      { title: 'Purpose and access', body: 'Information is processed to provide the requested demonstration and protect its operation. Access and data handling depend on the active local or hosted environment.' },
      { title: 'Retention and deletion', body: 'Retention and cleanup behavior must be confirmed for the environment in use. Do not rely on a demo account for long-term storage.' },
      { title: 'Production policy', body: 'A real deployment must publish an accurate policy describing its data, purposes, retention, user controls, contact channel, and regional requirements.' }
    ]
  }
} as const

const french = {
  brand: 'Talred Services.com Starter',
  navigation: {
    home: 'Accueil',
    about: 'À propos',
    login: 'Connexion',
    github: 'GitHub',
    start: 'Créer un compte de démonstration',
    dashboard: 'Tableau de bord'
  },
  footer: {
    description: 'Des fondations Talred PHP pour une architecture propre, intégrées aux API Laravel, aux applications Nuxt et à Talred Identity.',
    maintainer: 'Développeur full-stack indépendant · mainteneur de Talred Services',
    product: 'Starter',
    resources: 'Ressources',
    legal: 'Légal',
    overview: 'Accueil',
    about: 'À propos du Starter',
    support: 'Documentation',
    sourceCode: 'Code source',
    terms: 'Conditions',
    privacy: 'Confidentialité'
  },
  about: {
    eyebrow: 'À propos du Starter',
    title: 'Une base de services aux responsabilités claires',
    description: 'Talred Services.com Starter réunit un client Nuxt et son BFF, des modèles d’API Laravel, l’intégration à Identity et une stack de développement local partagée.',
    architectureTitle: 'Placer chaque responsabilité au bon endroit',
    architectureDescription: 'Le starter montre comment l’interface, le transport, les cas d’usage, les règles métier, la persistance et l’identité partagée s’articulent.',
    items: [
      { title: 'Client Nuxt et BFF', description: 'Créez l’interface et les opérations serveur du navigateur en gardant les identifiants privés côté serveur.', icon: 'i-lucide-layout-dashboard' },
      { title: 'API Laravel', description: 'Organisez les règles métier, les cas d’usage, les contrats API et les adaptateurs d’infrastructure en couches explicites.', icon: 'i-lucide-folder-kanban' },
      { title: 'Talred Identity', description: 'Utilisez la plateforme Identity pour l’authentification, les comptes, les membres et les règles d’accès.', icon: 'i-lucide-shield-check' },
      { title: 'Développement local', description: 'Lancez les applications et services associés documentés depuis l’espace Compose partagé.', icon: 'i-simple-icons-docker' }
    ],
    examplesTitle: 'Exemples inclus',
    examplesDescription: 'Identity illustre l’authentification partagée. Tasks suit une API Laravel guidée par le domaine à travers un BFF Nuxt, tandis que SaaS présente des pages produit publiques et un tableau de bord protégé.'
  },
  pricing: {
    eyebrow: 'Tarifs',
    title: 'Aucun tarif commercial n’est publié pour cette base',
    description: 'Talred Services.com Starter est une base de développement accompagnée d’exemples. Ce site ne propose pas d’offres payantes, d’hébergement ni de forfaits d’assistance.',
    notice: 'Aucune offre commerciale n’a été définie pour cette démonstration Starter. Tout futur service d’hébergement ou d’assistance devra avoir un périmètre et des tarifs publiés.',
    foundationHeading: 'Explorer la base',
    foundationDescription: 'Consultez la documentation et les démonstrations pour comprendre le contenu de l’espace avant de l’adapter et de le déployer.',
    features: ['Exemples d’application Nuxt et de BFF', 'Modèles d’architecture d’API Laravel', 'Intégration à Talred Identity', 'Pages publiques et tableau de bord SaaS', 'Stack de développement local'],
    action: 'Lire la documentation',
    faqTitle: 'Questions sur les tarifs',
    faq: [
      { label: 'Puis-je acheter une offre ici ?', content: 'Non. Ce site présente une base de développement et ne vend actuellement aucun abonnement.' },
      { label: 'Cette page comprend-elle un hébergement ou une assistance ?', content: 'Aucun service d’hébergement ni d’assistance n’est proposé ici. Le déploiement et l’assistance relèvent du propriétaire de l’application.' }
    ]
  },
  legal: {
    termsTitle: 'Conditions d’utilisation',
    privacyTitle: 'Informations sur la confidentialité',
    updated: 'Brouillon à valider',
    notice: 'Cette page est un brouillon d’information pour la démonstration Talred Services.com Starter, et non une politique juridique définitive. Vérifiez le traitement réel des données, leur conservation, les coordonnées et les conditions applicables avant toute publication en production.',
    terms: [
      { title: 'Objet de ce site', body: 'Ce site présente la démonstration d’un produit logiciel et ses fonctionnalités. Il permet aux visiteurs d’évaluer l’expérience et de comprendre le fonctionnement de la démonstration.' },
      { title: 'Comptes et contenu de démonstration', body: 'Utilisez les comptes de démonstration uniquement pour l’évaluation. Ne transmettez aucune information confidentielle, sensible ou essentielle à l’activité dans un environnement de démonstration.' },
      { title: 'Disponibilité et évolutions', body: 'Les exemples et services locaux peuvent évoluer avec le projet. Cette démonstration ne prend aucun engagement commercial ni de niveau de service.' },
      { title: 'Utilisation en production', body: 'Une application déployée doit disposer de ses propres conditions, engagements de service, contrôles de sécurité et règles opérationnelles validés avant sa mise à disposition.' }
    ],
    privacy: [
      { title: 'Informations traitées', body: 'Lors d’une démonstration, les services Identity et applicatifs configurés peuvent traiter les détails du compte et les informations transmises pendant l’exploration.' },
      { title: 'Cookies et stockage du navigateur', body: 'Les cookies de session internes associent les requêtes authentifiées à votre connexion. Le cookie i18n_redirected mémorise la langue choisie et le stockage local du navigateur conserve le thème sous la clé nuxt-color-mode. Ces réglages servent uniquement à la langue et à l’apparence. La version actuelle de la démonstration ne configure aucun traceur publicitaire ou analytique. Vous pouvez modifier vos choix avec les commandes de langue et d’apparence, ou les effacer dans les réglages de données du site de votre navigateur ; bloquer le cookie de session empêche la connexion.' },
      { title: 'Finalité et accès', body: 'Les informations sont traitées pour fournir la démonstration demandée et protéger son fonctionnement. Les accès et le traitement dépendent de l’environnement local ou hébergé.' },
      { title: 'Conservation et suppression', body: 'Les règles de conservation et de nettoyage doivent être confirmées pour l’environnement utilisé. Ne vous servez pas d’un compte de démonstration pour stocker des données à long terme.' },
      { title: 'Politique de production', body: 'Un déploiement réel doit publier une politique exacte décrivant les données, les finalités, la conservation, les contrôles utilisateur, les coordonnées et les exigences régionales.' }
    ]
  }
} as const

export function useSaasTemplatePresentation() {
  const { locale } = useI18n()

  return computed(() => locale.value === 'fr' ? french : english)
}
