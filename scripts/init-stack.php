<?php

declare(strict_types=1);

/** Initialize the local Starter, Fresh SaaS, and Tasks Identity projects. */

$arguments = array_slice($argv, 1);
if (in_array('--help', $arguments, true) || in_array('-h', $arguments, true)) {
    fwrite(STDOUT, <<<'HELP'
Usage: ./scripts/init-stack [--host]

Reconcile local Identity projects, clients, permissions, roles, hosted
applications, and local-only application environment files.

Options:
  --host    Use the public Identity source checkout and its local Laravel
            server instead of the shared Docker/DevOps Identity runtime.
            By default, the source checkout is ../identity. Override it with
            ZOLTA_IDENTITY_SOURCE_ROOT when it is in another location.
  -h, --help  Show this help without changing local files or contacting Identity.

Run this command interactively for first-time setup. See docs/developer-setup.md
for prerequisites and the host-mode setup steps.
HELP
    );
    fwrite(STDOUT, PHP_EOL);
    exit(0);
}

$root = dirname(__DIR__);
$templatePath = $root . '/scripts/identity-bootstrap.template.json';
$template = json_decode((string) file_get_contents($templatePath), true, 512, JSON_THROW_ON_ERROR);
if (!is_array($template) || ($template['version'] ?? null) !== 2 || !is_array($template['projects'] ?? null)) {
    throw new RuntimeException('Zoltasoft Identity bootstrap template is invalid or unsupported.');
}
$projectConfigurations = $template['projects'];
$hostedApplicationConfigPath = $root . '/integrations/identity-hosted-auth/hosted-applications.json';
$hostedApplicationConfig = json_decode((string) file_get_contents($hostedApplicationConfigPath), true, 512, JSON_THROW_ON_ERROR);
if (!is_array($hostedApplicationConfig) || ($hostedApplicationConfig['version'] ?? null) !== 1 || !is_array($hostedApplicationConfig['applications'] ?? null)) {
    throw new RuntimeException('The hosted-auth integration configuration is invalid or unsupported.');
}
foreach (['starter', 'saas', 'tasks'] as $projectKey) {
    if (!is_array($projectConfigurations[$projectKey] ?? null)) {
        throw new RuntimeException('The Identity bootstrap template must define the starter, saas, and tasks projects.');
    }
}

$identityConsole = $root . '/runtime/identity/console';
$identityServer = $root . '/runtime/identity/server';
$identityServerEnv = $identityServer . '/.env.starter';
$identityConsoleEnv = $identityConsole . '/.env.starter';
$apiEnv = $root . '/apps/api/.env';
$platformApiEnv = $root . '/apps/platform-api/.env';
$webEnv = $root . '/apps/web/.env';

$env = static function (string $key, ?string $fallback = null): ?string {
    $value = getenv($key);
    return $value === false || $value === '' ? $fallback : $value;
};
$existingSecret = static function (?string $value): ?string {
    if ($value === null || trim($value) === '' || str_contains($value, 'replace-with') || preg_match('/^<[^>]+>$/', trim($value)) === 1) {
        return null;
    }
    return $value;
};
$hostMode = in_array('--host', $argv ?? [], true);
$standaloneDocker = $env('ZOLTA_IDENTITY_DOCKER_SETUP') === '1';
if ($hostMode) {
    $identityServerEnv = $identityServer . '/.env.host';
    $identityConsoleEnv = $identityConsole . '/.env.host';
}
$identitySourceRoot = rtrim($env('ZOLTA_IDENTITY_SOURCE_ROOT', $root . '/../identity'), '/');
$identityServerRoot = $identitySourceRoot . '/apps/identity-server';
$identityServerAutoload = $identityServerRoot . '/vendor/autoload.php';
$identityServerBootstrap = $identityServerRoot . '/bootstrap/app.php';
$identityConsoleRootEnv = $identitySourceRoot . '/.env';
$nativeIdentityReady = is_file($identityServerAutoload) && is_file($identityServerBootstrap);
if ($hostMode && (!$nativeIdentityReady || !is_file($identityServerRoot . '/.env'))) {
    throw new RuntimeException('Host mode needs the public Identity source checkout with Composer dependencies and apps/identity-server/.env. See docs/developer-setup.md.');
}
$configuredOpsEnv = $env('ZOLTA_IDENTITY_LOCAL_DEV_OPS_ENV');
$defaultOpsEnv = $root . '/../local-dev-ops/local/.env';
$opsEnv = $standaloneDocker || $hostMode
    ? null
    : ($configuredOpsEnv ?? (is_file($defaultOpsEnv) ? $defaultOpsEnv : null));
$composeEnvPath = $env('ZOLTA_IDENTITY_COMPOSE_ENV_FILE');
$readEnv = static function (string $path): array {
    if (!is_file($path)) return [];
    $values = [];
    foreach (file($path, FILE_IGNORE_NEW_LINES) ?: [] as $line) {
        if (preg_match('/^\s*([A-Za-z_][A-Za-z0-9_]*)\s*=\s*(.*)\s*$/', $line, $m) !== 1) continue;
        $value = trim($m[2]);
        if (strlen($value) >= 2 && $value[0] === '"' && $value[-1] === '"') $value = stripcslashes(substr($value, 1, -1));
        if (strlen($value) >= 2 && $value[0] === "'" && $value[-1] === "'") $value = substr($value, 1, -1);
        $values[$m[1]] = $value;
    }
    return $values;
};
$writeEnv = static function (string $path, array $updates) use ($readEnv): void {
    $lines = is_file($path) ? (file($path, FILE_IGNORE_NEW_LINES) ?: []) : [];
    $seen = [];
    foreach ($lines as &$line) {
        if (preg_match('/^\s*([A-Za-z_][A-Za-z0-9_]*)\s*=/', $line, $m) !== 1 || !array_key_exists($m[1], $updates)) continue;
        $key = $m[1];
        $line = $key . '=' . (preg_match('/^[A-Za-z0-9_\.\/:@%+,-]+$/', (string) $updates[$key]) === 1 ? $updates[$key] : '"' . addcslashes((string) $updates[$key], "\\\"") . '"');
        $seen[$key] = true;
    }
    unset($line);
    foreach ($updates as $key => $value) if (!isset($seen[$key])) $lines[] = $key . '=' . (preg_match('/^[A-Za-z0-9_\.\/:@%+,-]+$/', (string) $value) === 1 ? $value : '"' . addcslashes((string) $value, "\\\"") . '"');
    if (!is_dir(dirname($path))) mkdir(dirname($path), 0775, true);
    $content = implode(PHP_EOL, $lines) . PHP_EOL;
    if (!is_file($path) || file_get_contents($path) !== $content) {
        file_put_contents($path, $content, LOCK_EX);
    }
    chmod($path, 0600);
};
$removeEnvKeys = static function (?string $path, array $keys): void {
    if ($path === null || !is_file($path)) return;
    $lines = file($path, FILE_IGNORE_NEW_LINES) ?: [];
    $filtered = array_values(array_filter($lines, static function (string $line) use ($keys): bool {
        foreach ($keys as $key) {
            if (preg_match('/^\\s*' . preg_quote($key, '/') . '\\s*=/', $line) === 1) return false;
        }
        return true;
    }));
    if (count($filtered) === count($lines)) return;
    file_put_contents($path, implode(PHP_EOL, $filtered) . PHP_EOL, LOCK_EX);
    chmod($path, 0600);
};
$removeEnvKeys($identityServerEnv, ['ZOLTA_AGENT_EMAIL', 'ZOLTA_AGENT_PASSWORD']);
$removeEnvKeys($opsEnv, ['STARTER_IDENTITY_AGENT_EMAIL', 'STARTER_IDENTITY_AGENT_PASSWORD']);
$removeEnvKeys($composeEnvPath, ['STARTER_IDENTITY_AGENT_EMAIL', 'STARTER_IDENTITY_AGENT_PASSWORD']);

$identityUrl = rtrim($env('ZOLTA_IDENTITY_API_URL', $hostMode ? 'http://127.0.0.1:8100' : 'http://127.0.0.1:8201'), '/');
$identityPublicApiUrl = rtrim($env('ZOLTA_IDENTITY_PUBLIC_API_URL', $identityUrl), '/');
$hostedAuthUrl = rtrim($env('ZOLTA_IDENTITY_CONSOLE_URL', $hostMode ? 'http://127.0.0.1:3100' : 'http://127.0.0.1:3201'), '/');
$appUrl = rtrim($env('ZOLTA_STARTER_SITE_URL', 'http://localhost:3300'), '/');
$starterApiUrl = rtrim($env('ZOLTA_STARTER_API_URL', $hostMode ? 'http://127.0.0.1:8300' : 'http://starter-api:8000'), '/');
$platformApiUrl = rtrim($env('ZOLTA_PLATFORM_API_URL', $hostMode ? 'http://127.0.0.1:8400' : 'http://platform-api:8000'), '/');
$ownerEmail = $env('ZOLTA_IDENTITY_OWNER_EMAIL', 'owner@example.com');
$ownerName = $env('ZOLTA_IDENTITY_OWNER_NAME', 'Local Installation Owner');
$opsValues = $opsEnv !== null ? $readEnv($opsEnv) : [];
$existingApiEnv = $readEnv($apiEnv);
$existingPlatformApiEnv = $readEnv($platformApiEnv);
$existingWebEnv = $readEnv($webEnv);
$storedHostedToken = $existingSecret($opsValues['STARTER_IDENTITY_HOSTED_APPLICATIONS_TOKEN'] ?? ($readEnv($identityServerEnv)['IDENTITY_HOSTED_APPLICATIONS_TOKEN'] ?? null));
$hostedToken = $env('ZOLTA_IDENTITY_HOSTED_APPLICATIONS_TOKEN', $storedHostedToken ?? base64_encode(random_bytes(32)));

foreach ($projectConfigurations as $projectKey => $configuration) {
    if (!isset($configuration['name'], $configuration['slug'], $configuration['clients'], $configuration['registration'], $configuration['permissions'], $configuration['roles'], $configuration['hostedApplication'])
        || !is_array($configuration['clients']) || !is_array($configuration['registration']) || !is_array($configuration['permissions'])
        || !is_array($configuration['roles']) || !is_array($configuration['hostedApplication'])) {
        throw new RuntimeException('Identity project configuration is incomplete: ' . $projectKey);
    }
    foreach (['bff'] as $clientKey) {
        if (!is_string($configuration['clients'][$clientKey] ?? null) || trim($configuration['clients'][$clientKey]) === '') {
            throw new RuntimeException('Identity project client configuration is missing: ' . $projectKey . '.' . $clientKey);
        }
    }
    $permissionKeys = [];
    foreach ($configuration['permissions'] as $permission) {
        if (!is_array($permission) || !isset($permission['key'], $permission['name'])) throw new RuntimeException('Invalid permission in Identity project: ' . $projectKey);
        $permissionKeys[$permission['key']] = true;
    }
    $roleSlugs = [];
    foreach ($configuration['roles'] as $role) {
        if (!is_array($role) || !isset($role['name'], $role['slug']) || !is_array($role['permissions'] ?? null)) throw new RuntimeException('Invalid role in Identity project: ' . $projectKey);
        $roleSlugs[$role['slug']] = true;
        foreach ($role['permissions'] as $permissionKey) if (!isset($permissionKeys[$permissionKey])) throw new RuntimeException('Role refers to an undefined permission: ' . $projectKey . '.' . $permissionKey);
    }
    if (!in_array($configuration['registration']['mode'] ?? null, ['invite_only', 'public'], true) || !is_bool($configuration['registration']['emailVerificationRequired'] ?? null) || !isset($roleSlugs[$configuration['registration']['defaultRole'] ?? ''])) {
        throw new RuntimeException('Invalid registration policy in Identity project: ' . $projectKey);
    }
    $application = $configuration['hostedApplication'];
    if (($application['primaryClient'] ?? null) !== 'bff'
        || !preg_match('/^\/[A-Za-z0-9_\/-]+$/', (string) ($application['callbackPath'] ?? ''))
        || !is_array($application['authentication'] ?? null)) {
        throw new RuntimeException('Invalid application integration in Identity project: ' . $projectKey);
    }
}
if (preg_match('/^(https?:\/\/)(localhost|127\.0\.0\.1)(:\d+)?$/', $identityUrl) !== 1
    && !($standaloneDocker && $identityUrl === 'http://identity-nginx')) {
    fwrite(STDERR, "Refusing to initialize a non-local Identity URL. Set ZOLTA_IDENTITY_API_URL to localhost for local setup.\n");
    exit(1);
}

$request = static function (string $method, string $path, ?array $payload = null, ?string $token = null) use ($identityUrl): array {
    $headers = ['Accept: application/json', 'Content-Type: application/json'];
    if ($token !== null) $headers[] = 'Authorization: Bearer ' . $token;
    $context = stream_context_create(['http' => ['method' => $method, 'header' => implode("\n", $headers), 'content' => $payload === null ? '' : json_encode($payload, JSON_THROW_ON_ERROR), 'ignore_errors' => true, 'timeout' => 15]]);
    $body = @file_get_contents($identityUrl . '/api/' . ltrim($path, '/'), false, $context);
    $status = 0;
    foreach ($http_response_header ?? [] as $header) if (preg_match('/^HTTP\/\S+\s+(\d+)/', $header, $m)) $status = (int) $m[1];
    $json = json_decode($body ?: '', true);
    if ($status < 200 || $status >= 300 || !is_array($json)) {
        $detail = is_array($json) ? json_encode(['errors' => $json['errors'] ?? null, 'message' => $json['message'] ?? null], JSON_UNESCAPED_SLASHES) : 'invalid JSON';
        throw new RuntimeException("Identity request failed: {$method} {$path} (HTTP {$status}): {$detail}");
    }
    return $json;
};
if (!is_file($identityServerEnv)) {
    $writeEnv($identityServerEnv, ['APP_URL' => $identityPublicApiUrl, 'IDENTITY_CONSOLE_URL' => $hostedAuthUrl]);
}

$console = $readEnv($identityConsoleEnv);
$server = $readEnv($identityServerEnv);
$identityConsoleRootValues = $hostMode ? $readEnv($identityConsoleRootEnv) : [];
$consoleClientId = $env('ZOLTA_IDENTITY_CONSOLE_CLIENT_ID') ?? $console['IDENTITY_CLIENT_ID'] ?? $server['IDENTITY_CLIENT_ID'] ?? ($identityConsoleRootValues['IDENTITY_CLIENT_ID'] ?? null) ?? ($opsValues['STARTER_IDENTITY_CONSOLE_CLIENT_ID'] ?? null);
$consoleClientSecret = $env('ZOLTA_IDENTITY_CONSOLE_CLIENT_SECRET') ?? $console['IDENTITY_CLIENT_SECRET'] ?? $server['IDENTITY_CLIENT_SECRET'] ?? ($identityConsoleRootValues['IDENTITY_CLIENT_SECRET'] ?? null) ?? ($opsValues['STARTER_IDENTITY_CONSOLE_CLIENT_SECRET'] ?? null);
$ownerEmail = $env('ZOLTA_IDENTITY_OWNER_EMAIL') ?? $server['ZOLTA_BOOTSTRAP_OWNER_EMAIL'] ?? $ownerEmail;
$ownerPassword = $env('ZOLTA_IDENTITY_OWNER_PASSWORD') ?? ($server['ZOLTA_BOOTSTRAP_OWNER_PASSWORD'] ?? null);
if (!$consoleClientId || !$consoleClientSecret) {
    if ($standaloneDocker) {
        throw new RuntimeException('Standalone Docker setup requires the generated Identity Console client credentials. Rerun ./docker/up.sh.');
    }
    if (!$ownerPassword) {
        if (!defined('STDIN') || !stream_isatty(STDIN)) throw new RuntimeException('First Identity initialization must be run interactively or supplied with ZOLTA_IDENTITY_OWNER_PASSWORD.');
        fwrite(STDERR, 'Owner name: ');
        $ownerName = trim((string) fgets(STDIN));
        fwrite(STDERR, 'Owner email: ');
        $ownerEmail = trim((string) fgets(STDIN));
        fwrite(STDERR, 'Owner password (input hidden): ');
        $stty = @shell_exec('stty -g');
        @system('stty -echo');
        $ownerPassword = trim((string) fgets(STDIN));
        @system('stty ' . trim((string) $stty));
        fwrite(STDERR, PHP_EOL);
    }
    $uuidBytes = random_bytes(16);
    $uuidBytes[6] = chr((ord($uuidBytes[6]) & 0x0f) | 0x40);
    $uuidBytes[8] = chr((ord($uuidBytes[8]) & 0x3f) | 0x80);
    $uuidHex = bin2hex($uuidBytes);
    $consoleClientId = sprintf('%s-%s-%s-%s-%s', substr($uuidHex, 0, 8), substr($uuidHex, 8, 4), substr($uuidHex, 12, 4), substr($uuidHex, 16, 4), substr($uuidHex, 20, 12));
    $clientSecret = bin2hex(random_bytes(32));
    if ($hostMode) {
        require_once $identityServerAutoload;
        $identityApp = require $identityServerBootstrap;
        $identityKernel = $identityApp->make(\Illuminate\Contracts\Console\Kernel::class);
        $clientName = 'Talred Starter Console BFF (' . substr($uuidHex, 0, 8) . ')';
        $code = $identityKernel->call('identity:bootstrap', [
            'email' => $ownerEmail,
            '--name' => $ownerName,
            '--password' => $ownerPassword,
            '--project' => 'Identity Console',
            '--client' => $clientName,
            '--client-id' => $consoleClientId,
            '--client-secret' => $clientSecret,
            '--if-needed' => true,
            '--hide-client-secret' => true,
        ]);
        if ($code !== 0) throw new RuntimeException('Identity bootstrap failed. Check the local Identity API database and setup instructions.');
    } else {
        $composeFile = $root . '/../local-dev-ops/local/compose.yaml';
        $composeEnv = $root . '/../local-dev-ops/local/.env';
        if (!is_file($composeFile)) {
            throw new RuntimeException('No local Identity bootstrap runtime was found. Use ./scripts/init-stack --host with the public Identity source checkout, or start the shared workspace Compose stack.');
        }
        $composeOptions = 'docker compose --env-file ' . escapeshellarg($composeEnv);
        $releaseEnv = $root . '/runtime/identity/.env.release';
        if (is_file($releaseEnv)) {
            $composeOptions .= ' --env-file ' . escapeshellarg($releaseEnv);
        }
        $composeOptions .= ' -f ' . escapeshellarg($composeFile);
        if (is_file($releaseEnv)) {
            $composeOptions .= ' -f ' . escapeshellarg(dirname($composeFile) . '/compose.identity-release.yaml');
        }
        $command = $composeOptions . ' run --rm --no-deps starter-identity-api php artisan identity:bootstrap ' . escapeshellarg($ownerEmail) . ' --name=' . escapeshellarg($ownerName) . ' --password=' . escapeshellarg($ownerPassword) . ' --client-id=' . escapeshellarg($consoleClientId) . ' --client-secret=' . escapeshellarg($clientSecret) . ' --if-needed';
        exec($command . ' 2>&1', $output, $code);
        if ($code !== 0) throw new RuntimeException('Identity bootstrap failed. Check the Identity server output and try again.');
    }
    $consoleClientSecret = $clientSecret;
}
if (!$ownerPassword) {
    if (!defined('STDIN') || !stream_isatty(STDIN)) {
        throw new RuntimeException('Identity owner password is required. Run this initializer interactively or supply ZOLTA_IDENTITY_OWNER_PASSWORD.');
    }
    fwrite(STDERR, 'Owner password (input hidden): ');
    $stty = @shell_exec('stty -g');
    @system('stty -echo');
    $ownerPassword = trim((string) fgets(STDIN));
    @system('stty ' . trim((string) $stty));
    fwrite(STDERR, PHP_EOL);
    if ($ownerPassword === '') throw new RuntimeException('Identity owner password cannot be empty.');
}
// Artisan serve reloads after generated .env changes. Wait using a read-only
// health request rather than retrying a potentially completed mutation.
$healthDeadline = microtime(true) + 30;
do {
    $healthContext = stream_context_create(['http' => ['timeout' => 1, 'ignore_errors' => true]]);
    $healthResponse = @file_get_contents($identityUrl . '/up', false, $healthContext);
    $healthReady = $healthResponse !== false
        && preg_match('/^HTTP\/\S+\s+200\b/', $http_response_header[0] ?? '') === 1;
    if ($healthReady) break;
    usleep(250000);
} while (microtime(true) < $healthDeadline);
if (!$healthReady) {
    throw new RuntimeException('The local Identity API did not become healthy within 30 seconds. Start its server and rerun the initializer.');
}
$loginDeadline = microtime(true) + 30;
do {
    try {
        $login = $request('POST', 'v1/identity/auth/login', ['client_id' => $consoleClientId, 'client_secret' => $consoleClientSecret, 'project' => 'identity-console', 'email' => $ownerEmail, 'password' => $ownerPassword]);
        break;
    } catch (RuntimeException $exception) {
        // A health check can race the development server's environment reload.
        // Only authentication is retried, never provisioning writes or 401/422.
        if (preg_match('/\(HTTP (0|502|503|504)\)/', $exception->getMessage()) !== 1 || microtime(true) >= $loginDeadline) {
            throw $exception;
        }
        usleep(250000);
    }
} while (true);
$token = $login['data']['access_token'] ?? null;
if (!$token) throw new RuntimeException('Identity login succeeded without an access token.');

$findByField = static function (array $value, string $field, string $match) use (&$findByField): ?array {
    if (isset($value[$field], $value['id']) && $value[$field] === $match) return $value;
    foreach ($value as $child) if (is_array($child)) { $found = $findByField($child, $field, $match); if ($found !== null) return $found; }
    return null;
};
$allProjects = $request('GET', 'v1/identity/projects', null, $token);
$projectRecords = $allProjects['data'] ?? $allProjects;
if (is_array($projectRecords) && isset($projectRecords['projects']) && is_array($projectRecords['projects'])) {
    $projectRecords = $projectRecords['projects'];
}
$projectRecords = is_array($projectRecords)
    ? array_values(array_filter($projectRecords, static fn (mixed $candidate): bool => is_array($candidate) && isset($candidate['id'], $candidate['slug'])) )
    : [];
$provisioned = [];
foreach ($projectConfigurations as $projectKey => $configuration) {
    $project = $findByField($allProjects, 'slug', $configuration['slug']) ?? $findByField($allProjects, 'name', $configuration['name']);
    if ($project === null) {
        $created = $request('POST', 'v1/identity/projects', ['name' => $configuration['name'], 'slug' => $configuration['slug'], 'description' => $configuration['description']], $token);
        $project = $created['data']['project'] ?? $created['data'] ?? null;
    }
    if (!is_array($project) || !isset($project['id'])) throw new RuntimeException('Identity project provisioning returned no project: ' . $projectKey);
    $projectId = $project['id'];
    $details = $request('GET', 'v1/identity/projects/' . $projectId, null, $token)['data'] ?? [];
    $clients = [];
    foreach ($configuration['clients'] as $clientKey => $clientName) {
        $client = $findByField((array) ($details['clients'] ?? []), 'name', $clientName);
        if ($client === null) {
            $created = $request('POST', 'v1/identity/projects/' . $projectId . '/clients', ['name' => $clientName], $token);
            $client = $created['data']['client'] ?? $created['data'] ?? null;
        } elseif (!isset($client['client_secret'])) {
            $localClient = match ($projectKey . '.' . $clientKey) {
                'starter.api' => ['id' => $existingPlatformApiEnv['IDENTITY_CLIENT_ID'] ?? null, 'secret' => $existingPlatformApiEnv['IDENTITY_CLIENT_SECRET'] ?? null],
                'tasks.api' => ['id' => $existingApiEnv['IDENTITY_CLIENT_ID'] ?? null, 'secret' => $existingApiEnv['IDENTITY_CLIENT_SECRET'] ?? null],
                default => ['id' => $existingWebEnv['NUXT_TALRED_IDENTITY_APPLICATIONS_' . strtoupper($projectKey === 'tasks' ? 'projects' : $projectKey) . '_CLIENT_ID'] ?? null, 'secret' => $existingWebEnv['NUXT_TALRED_IDENTITY_APPLICATIONS_' . strtoupper($projectKey === 'tasks' ? 'projects' : $projectKey) . '_CLIENT_SECRET'] ?? null],
            };
            // Management responses deliberately omit secrets. Reuse only the
            // local secret belonging to this exact client; rotate if missing.
            $savedSecret = $existingSecret($localClient['secret']);
            if ($localClient['id'] === $client['id'] && $savedSecret !== null) {
                $client['client_secret'] = $savedSecret;
            } else {
                $rotated = $request('POST', 'v1/identity/projects/' . $projectId . '/clients/' . $client['id'] . '/rotate-secret', null, $token);
                $client = $rotated['data']['client'] ?? $rotated['data'] ?? $client;
            }
        }
        if (!is_array($client) || !isset($client['id'])) throw new RuntimeException('Identity client provisioning returned no client: ' . $projectKey . '.' . $clientKey);
        $clients[$clientKey] = $client;
    }
    $permissionClient = $clients['api'] ?? $clients['bff'];
    $request('PUT', 'v1/identity/projects/' . $projectId . '/clients/' . $permissionClient['id'] . '/permission-manifest', ['permissions' => $configuration['permissions']], $token);
    $details = $request('GET', 'v1/identity/projects/' . $projectId, null, $token)['data'] ?? [];
    $permissionIds = [];
    foreach ((array) ($details['permissions'] ?? []) as $permission) if (isset($permission['key'], $permission['id']) && ($permission['status'] ?? 'active') === 'active') $permissionIds[$permission['key']] = $permission['id'];
    $roleIds = [];
    foreach ($configuration['roles'] as $roleConfiguration) {
        $role = $findByField((array) ($details['roles'] ?? []), 'slug', $roleConfiguration['slug']);
        if ($role === null) {
            $created = $request('POST', 'v1/identity/projects/' . $projectId . '/roles', ['name' => $roleConfiguration['name'], 'slug' => $roleConfiguration['slug'], 'description' => $roleConfiguration['description']], $token);
            $role = $created['data']['role'] ?? $created['data'] ?? null;
        }
        if (!is_array($role) || !isset($role['id'])) throw new RuntimeException('Identity role provisioning returned no role: ' . $projectKey);
        $permissionIdsForRole = [];
        foreach ($roleConfiguration['permissions'] as $permissionKey) if (isset($permissionIds[$permissionKey])) $permissionIdsForRole[] = $permissionIds[$permissionKey]; else throw new RuntimeException('Configured role permission is missing: ' . $projectKey . '.' . $permissionKey);
        $request('PUT', 'v1/identity/projects/' . $projectId . '/roles/' . $role['id'] . '/permissions', ['permission_ids' => $permissionIdsForRole], $token);
        $roleIds[$roleConfiguration['slug']] = $role['id'];
    }
    $request('PATCH', 'v1/identity/projects/' . $projectId . '/registration', ['registration_mode' => $configuration['registration']['mode'], 'registration_role_id' => $roleIds[$configuration['registration']['defaultRole']], 'email_verification_required' => $configuration['registration']['emailVerificationRequired']], $token);
    $application = $configuration['hostedApplication'];
    $details = $request('GET', 'v1/identity/projects/' . $projectId, null, $token)['data'] ?? [];
    $hostedPayload = ['name' => $application['name'], 'key' => $application['key'], 'primary_client_id' => $clients['bff']['id'], 'application_url' => $appUrl, 'callback_url' => $appUrl . $application['callbackPath'], 'authentication_mode' => 'hosted', 'authentication' => $application['authentication']];
    $hostedPresentation = $hostedApplicationConfig['applications'][$application['key']] ?? null;
    if (is_array($hostedPresentation)) {
        if (isset($hostedPresentation['page_set'])) {
            $hostedPayload['auth_page_set'] = $hostedPresentation['page_set'];
        }
        if (is_array($hostedPresentation['appearance'] ?? null)) {
            $hostedPayload['appearance'] = $hostedPresentation['appearance'];
        }
    }
    $hosted = $findByField((array) ($details['hosted_applications'] ?? []), 'key', $application['key']);
    $hostedPayload['policy_revision'] = $application['policyRevision'] ?? null;
    if ($hosted === null) {
        foreach ($projectRecords as $sourceProject) {
            if (($sourceProject['id'] ?? null) === $projectId) {
                continue;
            }

            $sourceDetails = $request('GET', 'v1/identity/projects/' . $sourceProject['id'], null, $token)['data'] ?? [];
            $misplacedHosted = $findByField((array) ($sourceDetails['hosted_applications'] ?? []), 'key', $application['key']);
            if (!is_array($misplacedHosted) || !isset($misplacedHosted['id'])) {
                continue;
            }

            $request('DELETE', 'v1/identity/projects/' . $sourceProject['id'] . '/hosted-applications/' . $misplacedHosted['id'], null, $token);
            break;
        }

        $legacyHosted = null;
        foreach ((array) ($details['hosted_applications'] ?? []) as $candidate) {
            if (($candidate['primary_client_id'] ?? $candidate['primaryClientId'] ?? null) === $clients['bff']['id']) {
                $legacyHosted = $candidate;
                break;
            }
        }
        if (is_array($legacyHosted) && isset($legacyHosted['id'], $legacyHosted['key'])) {
            $request('DELETE', 'v1/identity/projects/' . $projectId . '/hosted-applications/' . $legacyHosted['id'], null, $token);
        }
    }
    if ($hosted === null) {
        $created = $request('POST', 'v1/identity/projects/' . $projectId . '/applications', $hostedPayload, $token);
        $hosted = $created['data']['hosted_application'] ?? $created['data'] ?? null;
    } else {
        $request('PATCH', 'v1/identity/projects/' . $projectId . '/applications/' . $hosted['id'], $hostedPayload + ['status' => 'active'], $token);
    }
    if (!is_array($hosted) || !isset($hosted['id'])) throw new RuntimeException('Hosted application provisioning returned no application: ' . $projectKey);
    $provisioned[$projectKey] = ['project' => $project, 'clients' => $clients, 'application' => $application];
}

$starter = $provisioned['starter'];
$saas = $provisioned['saas'];
$tasks = $provisioned['tasks'];
$starterBff = $starter['clients']['bff'];
$starterApi = $starter['clients']['api'] ?? throw new RuntimeException('Starter Identity must provision an API client for the Platform API.');
$saasBff = $saas['clients']['bff'];
$tasksApi = $tasks['clients']['api'];
$tasksBff = $tasks['clients']['bff'];
$writeEnv($identityServerEnv, ['APP_URL' => $identityPublicApiUrl, 'IDENTITY_CONSOLE_URL' => $hostedAuthUrl, 'ZOLTA_BOOTSTRAP_OWNER_EMAIL' => $ownerEmail, 'ZOLTA_BOOTSTRAP_OWNER_PASSWORD' => $ownerPassword, 'IDENTITY_HOSTED_APPLICATIONS_TOKEN' => $hostedToken, 'PLATFORM_ONBOARDING_CLIENT_ID' => $starterApi['id'], 'PLATFORM_LOGIN_IDENTITY_PROJECT' => $starter['project']['slug']]);
$writeEnv($apiEnv, ['APP_URL' => $starterApiUrl, 'IDENTITY_API_URL' => $identityUrl, 'IDENTITY_PROJECT' => $tasks['project']['slug'], 'IDENTITY_CLIENT_ID' => $tasksApi['id'], 'IDENTITY_CLIENT_SECRET' => $tasksApi['client_secret'] ?? ($existingApiEnv['IDENTITY_CLIENT_SECRET'] ?? ''), 'IDENTITY_SANDBOX_API_URL' => $identityUrl, 'IDENTITY_SANDBOX_PROJECT' => $tasks['project']['slug']]);
$writeEnv($platformApiEnv, ['APP_URL' => $platformApiUrl, 'STARTER_PUBLIC_URL' => $appUrl, 'IDENTITY_API_URL' => $identityUrl, 'IDENTITY_PROJECT' => $starter['project']['slug'], 'IDENTITY_CLIENT_ID' => $starterApi['id'], 'IDENTITY_CLIENT_SECRET' => $starterApi['client_secret'] ?? ($existingPlatformApiEnv['IDENTITY_CLIENT_SECRET'] ?? ''), 'IDENTITY_SANDBOX_API_URL' => $identityUrl, 'IDENTITY_SANDBOX_PROJECT' => $starter['project']['slug']]);
$writeEnv($webEnv, [
    'ZOLTA_STARTER_SITE_URL' => $appUrl,
    'NUXT_TALRED_IDENTITY_APPLICATIONS_STARTER_IDENTITY_API_URL' => $identityUrl,
    'NUXT_TALRED_IDENTITY_APPLICATIONS_STARTER_HOSTED_AUTH_URL' => $hostedAuthUrl,
    'NUXT_TALRED_IDENTITY_APPLICATIONS_STARTER_HOSTED_APPLICATION' => $starter['application']['key'],
    'NUXT_TALRED_IDENTITY_APPLICATIONS_STARTER_CLIENT_ID' => $starterBff['id'],
    'NUXT_TALRED_IDENTITY_APPLICATIONS_STARTER_CLIENT_SECRET' => $starterBff['client_secret'] ?? ($existingWebEnv['NUXT_TALRED_IDENTITY_APPLICATIONS_STARTER_CLIENT_SECRET'] ?? ($existingWebEnv['ZOLTA_STARTER_IDENTITY_CLIENT_SECRET'] ?? '')),
    'NUXT_TALRED_IDENTITY_APPLICATIONS_STARTER_CALLBACK_URL' => $appUrl . $starter['application']['callbackPath'],
    'NUXT_TALRED_IDENTITY_APPLICATIONS_SAAS_IDENTITY_API_URL' => $identityUrl,
    'NUXT_TALRED_IDENTITY_APPLICATIONS_SAAS_HOSTED_AUTH_URL' => $hostedAuthUrl,
    'NUXT_TALRED_IDENTITY_APPLICATIONS_SAAS_HOSTED_APPLICATION' => $saas['application']['key'],
    'NUXT_TALRED_IDENTITY_APPLICATIONS_SAAS_CLIENT_ID' => $saasBff['id'],
    'NUXT_TALRED_IDENTITY_APPLICATIONS_SAAS_CLIENT_SECRET' => $saasBff['client_secret'] ?? ($existingWebEnv['NUXT_TALRED_IDENTITY_APPLICATIONS_SAAS_CLIENT_SECRET'] ?? ''),
    'NUXT_TALRED_IDENTITY_APPLICATIONS_SAAS_CALLBACK_URL' => $appUrl . $saas['application']['callbackPath'],
    'NUXT_TALRED_IDENTITY_APPLICATIONS_PROJECTS_IDENTITY_API_URL' => $identityUrl,
    'NUXT_TALRED_IDENTITY_APPLICATIONS_PROJECTS_HOSTED_AUTH_URL' => $hostedAuthUrl,
    'NUXT_TALRED_IDENTITY_APPLICATIONS_PROJECTS_HOSTED_APPLICATION' => $tasks['application']['key'],
    'NUXT_TALRED_IDENTITY_APPLICATIONS_PROJECTS_CLIENT_ID' => $tasksBff['id'],
    'NUXT_TALRED_IDENTITY_APPLICATIONS_PROJECTS_CLIENT_SECRET' => $tasksBff['client_secret'] ?? ($existingWebEnv['NUXT_TALRED_IDENTITY_APPLICATIONS_PROJECTS_CLIENT_SECRET'] ?? ($existingWebEnv['ZOLTA_PROJECTS_IDENTITY_CLIENT_SECRET'] ?? '')),
    'NUXT_TALRED_IDENTITY_APPLICATIONS_PROJECTS_CALLBACK_URL' => $appUrl . $tasks['application']['callbackPath'],
    'ZOLTA_STARTER_API_URL' => $starterApiUrl,
    'ZOLTA_PLATFORM_API_URL' => $platformApiUrl,
    'NUXT_TALRED_IDENTITY_SESSION_SECRET' => $existingSecret($existingWebEnv['NUXT_TALRED_IDENTITY_SESSION_SECRET'] ?? $existingWebEnv['NUXT_ZOLTA_IDENTITY_SESSION_SECRET'] ?? null) ?? base64_encode(random_bytes(32)),
    'NUXT_SESSION_PASSWORD' => $existingSecret($existingWebEnv['NUXT_SESSION_PASSWORD'] ?? null) ?? base64_encode(random_bytes(32)),
]);
$generatedIdentityEnv = ['STARTER_IDENTITY_HOSTED_APPLICATIONS_TOKEN' => $hostedToken, 'STARTER_IDENTITY_OWNER_EMAIL' => $ownerEmail, 'STARTER_IDENTITY_OWNER_PASSWORD' => $ownerPassword, 'STARTER_IDENTITY_CONSOLE_CLIENT_ID' => $consoleClientId, 'STARTER_IDENTITY_CONSOLE_CLIENT_SECRET' => $consoleClientSecret, 'STARTER_IDENTITY_CLIENT_ID' => $starterBff['id'], 'STARTER_IDENTITY_CLIENT_SECRET' => $starterBff['client_secret'] ?? ($existingWebEnv['NUXT_TALRED_IDENTITY_APPLICATIONS_STARTER_CLIENT_SECRET'] ?? ''), 'STARTER_SAAS_IDENTITY_CLIENT_ID' => $saasBff['id'], 'STARTER_SAAS_IDENTITY_CLIENT_SECRET' => $saasBff['client_secret'] ?? ($existingWebEnv['NUXT_TALRED_IDENTITY_APPLICATIONS_SAAS_CLIENT_SECRET'] ?? ''), 'STARTER_PROJECTS_IDENTITY_CLIENT_ID' => $tasksBff['id'], 'STARTER_PROJECTS_IDENTITY_CLIENT_SECRET' => $tasksBff['client_secret'] ?? ($existingWebEnv['NUXT_TALRED_IDENTITY_APPLICATIONS_PROJECTS_CLIENT_SECRET'] ?? ''), 'STARTER_PROJECTS_API_IDENTITY_CLIENT_ID' => $tasksApi['id'], 'STARTER_PROJECTS_API_IDENTITY_CLIENT_SECRET' => $tasksApi['client_secret'] ?? ($existingApiEnv['IDENTITY_CLIENT_SECRET'] ?? ''), 'STARTER_PLATFORM_API_IDENTITY_CLIENT_ID' => $starterApi['id'], 'STARTER_PLATFORM_API_IDENTITY_CLIENT_SECRET' => $starterApi['client_secret'] ?? ($existingPlatformApiEnv['IDENTITY_CLIENT_SECRET'] ?? ''), 'STARTER_IDENTITY_CONSOLE_SESSION_PASSWORD' => $env('STARTER_IDENTITY_CONSOLE_SESSION_PASSWORD', $env('NUXT_SESSION_PASSWORD', ''))];
if ($opsEnv !== null) {
    $writeEnv($opsEnv, $generatedIdentityEnv);
}
if ($composeEnvPath !== null) {
    $writeEnv($composeEnvPath, $generatedIdentityEnv + ['STARTER_IDENTITY_SESSION_SECRET' => $env('STARTER_IDENTITY_SESSION_SECRET', '')]);
}
$identityHostedAuthPageSets = 'default,starter,saas,projects';
$identityHostedAuthIntegrationPath = $root . '/integrations/identity-hosted-auth';
$identityConsoleValues = $hostMode ? $readEnv($identityConsoleRootEnv) : [];
$identityConsoleSessionPassword = $existingSecret($identityConsoleValues['NUXT_SESSION_PASSWORD'] ?? null) ?? bin2hex(random_bytes(32));
$consoleEnvironment = [
    'IDENTITY_API_URL' => $identityUrl,
    'IDENTITY_PROJECT' => 'identity-console',
    'IDENTITY_CLIENT_ID' => $consoleClientId,
    'IDENTITY_CLIENT_SECRET' => $consoleClientSecret,
    'IDENTITY_HOSTED_APPLICATIONS_TOKEN' => $hostedToken,
    'NUXT_IDENTITY_HOSTED_APPLICATIONS_TOKEN' => $hostedToken,
    'IDENTITY_HOSTED_AUTH_INTEGRATIONS' => $identityHostedAuthIntegrationPath,
    'IDENTITY_HOSTED_AUTH_PAGE_SETS' => $identityHostedAuthPageSets,
    'NUXT_IDENTITY_HOSTED_AUTH_PAGE_SETS' => $identityHostedAuthPageSets,
    'NUXT_SESSION_PASSWORD' => $identityConsoleSessionPassword,
];
$writeEnv($identityConsoleEnv, $consoleEnvironment);
if ($hostMode) {
    $writeEnv($identityServerRoot . '/.env', [
        'APP_URL' => $identityPublicApiUrl,
        'IDENTITY_CONSOLE_URL' => $hostedAuthUrl,
        'IDENTITY_HOSTED_APPLICATIONS_TOKEN' => $hostedToken,
        'IDENTITY_HOSTED_AUTH_PAGE_SETS' => $identityHostedAuthPageSets,
        'PLATFORM_ONBOARDING_CLIENT_ID' => $starterApi['id'],
        'PLATFORM_LOGIN_IDENTITY_PROJECT' => $starter['project']['slug'],
    ]);
    $writeEnv($identityConsoleRootEnv, $consoleEnvironment);
    fwrite(STDOUT, "Configured the public Identity source checkout for the Starter's hosted-auth integration.\n");
}
fwrite(STDOUT, "Talred Starter local Identity projects initialized.\n");
fwrite(STDOUT, "Configured separate Starter, Fresh SaaS, and Tasks projects, API/BFF clients, Identity-hosted applications, roles, permissions, and local env files.\n");
