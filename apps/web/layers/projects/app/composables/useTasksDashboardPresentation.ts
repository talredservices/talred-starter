const dashboardCopy = {
  en: {
    dashboardWelcome: 'Welcome, {name}',
    dashboardProductsTitle: 'Explore Talred products and services',
    dashboardProductsDescription: 'Open a demo, visit the Identity console, or browse the documentation.',
    dashboardExploreProduct: 'Explore',
    dashboardProductStarter: 'Talred Starter',
    dashboardProductStarterDescription: 'Talred PHP foundations for clean architecture, integrated with Laravel APIs, Nuxt applications, and Talred Identity.',
    dashboardProductTasks: 'Talred Tasks',
    dashboardProductTasksDescription: 'A full-stack task example connecting Nuxt, Laravel, and project-scoped access.',
    dashboardProductSaas: 'SaaS',
    dashboardProductSaasDescription: 'A SaaS product site with Identity-hosted sign-in and a protected dashboard.',
    dashboardProductIdentity: 'Talred Identity',
    dashboardProductIdentityDescription: 'Shared authentication, accounts, projects, roles, and access policies.',
    dashboardProductDocs: 'Documentation',
    dashboardProductDocsDescription: 'Guides and references for Talred packages and integrations.',
    firstPage: 'First page', lastPage: 'Last page', prevPage: 'Previous page', nextPage: 'Next page', pageLabel: 'Page', saved: 'Changes saved', removed: 'Task deleted', sortTasks: 'Sort tasks', prioritySort: 'Highest priority', overviewTitle: 'Your project and task data', overviewDescription: 'Explore the project records returned by the Tasks API, compare task volumes, and open a project to query and manage its tasks.', apiEyebrow: 'Talred Tasks API', workflowTitle: 'What the API lets you do', workflowDescription: 'Read project data, shape task views, and carry work through its lifecycle.', apiListProjects: 'Read accessible projects with status, key, description, and task counts.', apiCreateProject: 'Create a project with a name, unique key, and optional description.', apiQueryTasks: 'Search tasks and filter by status or priority within a project; render results as a table or status board.', apiCreateTask: 'Add a task with a title, description, and priority.', apiUpdateTask: 'Change a task title, description, status, or priority.', apiDeleteTask: 'Remove a task when it is no longer needed.', apiSecurity: 'Requests run through the authenticated Nuxt BFF; Identity and project ownership protect access.',
    projects: 'Projects', active: 'Active projects', archived: 'Archived projects', tasks: 'Tracked tasks',
    search: 'Search projects', all: 'All projects', name: 'Project', status: 'Status', taskCount: 'Tasks', open: 'Open tasks', loading: 'Loading…',
    distribution: 'Work distribution', distributionDescription: 'Task counts by project, including completed tasks.', noData: 'No matching projects',
    clear: 'Clear filters', retry: 'Try again', failed: 'Could not load this workspace. Please try again.', saveFailed: 'Could not save your changes. Please try again.',
    refresh: 'Refresh', table: 'Table', board: 'Board', view: 'Task view', filtered: 'Summary of matching tasks',
    details: 'Task details', priority: 'Priority', title: 'Task', description: 'Description', close: 'Close', save: 'Save description',
    page: 'Page {page} of {pages}', record: '{count} record', records: '{count} records', sort: 'Sort projects', nameSort: 'Name', tasksSort: 'Most tasks',
    createHint: 'Give this project a short name and a unique key.', completion: 'Completion', completed: 'Completed',
    noTasks: 'No matching tasks', scope: 'Current API records; refresh to retrieve the latest changes.'
  },
  fr: {
    dashboardWelcome: 'Bienvenue, {name}',
    dashboardProductsTitle: 'Explorer les produits et services Talred',
    dashboardProductsDescription: 'Ouvrez une démonstration, consultez la console Identity ou parcourez la documentation.',
    dashboardExploreProduct: 'Explorer',
    dashboardProductStarter: 'Talred Starter',
    dashboardProductStarterDescription: 'Des fondations Talred PHP pour une architecture propre, intégrées aux API Laravel, aux applications Nuxt et à Talred Identity.',
    dashboardProductTasks: 'Talred Tasks',
    dashboardProductTasksDescription: 'Un exemple full-stack de gestion des tâches reliant Nuxt, Laravel et les accès par projet.',
    dashboardProductSaas: 'SaaS',
    dashboardProductSaasDescription: 'Un site produit SaaS avec une connexion hébergée par Identity et un tableau de bord protégé.',
    dashboardProductIdentity: 'Talred Identity',
    dashboardProductIdentityDescription: 'Authentification partagée, comptes, projets, rôles et règles d’accès.',
    dashboardProductDocs: 'Documentation',
    dashboardProductDocsDescription: 'Guides et références pour les packages et intégrations Talred.',
    firstPage: 'Première page', lastPage: 'Dernière page', prevPage: 'Page précédente', nextPage: 'Page suivante', pageLabel: 'Page', saved: 'Modifications enregistrées', removed: 'Tâche supprimée', sortTasks: 'Trier les tâches', prioritySort: 'Priorité élevée', overviewTitle: 'Vos projets et tâches', overviewDescription: 'Explorez les projets renvoyés par l’API Tasks, comparez leur volume de tâches et ouvrez un projet pour les consulter et les gérer.', apiEyebrow: 'API Talred Tasks', workflowTitle: 'Fonctionnalités de l’API', workflowDescription: 'Consultez les projets, présentez les tâches dans différentes vues et suivez leur cycle de vie.', apiListProjects: 'Consulter les projets accessibles avec leur statut, clé, description et nombre de tâches.', apiCreateProject: 'Créer un projet avec un nom, une clé unique et une description facultative.', apiQueryTasks: 'Rechercher des tâches et filtrer par statut ou priorité dans un projet ; afficher les résultats en tableau ou par statut.', apiCreateTask: 'Ajouter une tâche avec un titre, une description et une priorité.', apiUpdateTask: 'Modifier le titre, la description, le statut ou la priorité d’une tâche.', apiDeleteTask: 'Supprimer une tâche qui n’est plus nécessaire.', apiSecurity: 'Les requêtes passent par le BFF Nuxt authentifié ; Identity et la propriété du projet protègent l’accès.',
    projects: 'Projets', active: 'Projets actifs', archived: 'Projets archivés', tasks: 'Tâches suivies',
    search: 'Rechercher des projets', all: 'Tous les projets', name: 'Projet', status: 'Statut', taskCount: 'Tâches', open: 'Ouvrir les tâches', loading: 'Chargement…',
    distribution: 'Répartition du travail', distributionDescription: 'Nombre de tâches par projet, y compris les tâches terminées.', noData: 'Aucun projet correspondant',
    clear: 'Effacer les filtres', retry: 'Réessayer', failed: 'Impossible de charger cet espace. Veuillez réessayer.', saveFailed: 'Impossible d’enregistrer les modifications. Veuillez réessayer.',
    refresh: 'Actualiser', table: 'Tableau', board: 'Colonnes', view: 'Vue des tâches', filtered: 'Résumé des tâches correspondantes',
    details: 'Détails de la tâche', priority: 'Priorité', title: 'Tâche', description: 'Description', close: 'Fermer', save: 'Enregistrer la description',
    page: 'Page {page} sur {pages}', record: '{count} élément', records: '{count} éléments', sort: 'Trier les projets', nameSort: 'Nom', tasksSort: 'Plus de tâches',
    createHint: 'Donnez au projet un nom court et une clé unique.', completion: 'Avancement', completed: 'Terminées',
    noTasks: 'Aucune tâche correspondante', scope: 'Données actuelles de l’API ; actualisez pour récupérer les dernières modifications.'
  }
}
export function useTasksDashboardPresentation() {
  const { locale } = useI18n()
  return computed(() => dashboardCopy[locale.value === 'fr' ? 'fr' : 'en'])
}
