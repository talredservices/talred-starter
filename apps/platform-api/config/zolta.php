<?php

use App\Identity\RemoteIdentity;
use Zolta\Http\Identity\Laravel\IdentityPrincipal;
use Zolta\Http\Response\Laravel\Resources\GenericResource;

return [
    'cqrs' => [
        'commands' => [
            ['path' => app_path('Services'), 'namespace' => 'App\\Services\\'],
        ],
        'queries' => [
            ['path' => app_path('Services'), 'namespace' => 'App\\Services\\'],
        ],
        'infrastructure_events' => [['path' => app_path('Events'), 'namespace' => 'App\\Events\\']],
        'cache' => [
            'command' => base_path('bootstrap/cache/command_map.php'),
            'query' => base_path('bootstrap/cache/query_map.php'),
            'event' => base_path('bootstrap/cache/event_map.php'),
        ],
        'map_keys' => ['command' => 'command.map', 'query' => 'query.map', 'event' => 'event.map'],
        'options' => [
            'auto_detect_psr4' => false,
            'write_atomic' => true,
            'file_pattern' => '*.php',
            'exclude_paths' => ['**/Infrastructure/Repositories/**', '**/API/Routes/**', '**/Database/**', '**/vendor/**'],
            'composer_autoload' => base_path('vendor/autoload.php'),
            'follow_symlinks' => false,
            'verbose_logging' => false,
        ],
    ],
    'http' => [
        'routes' => [
            'cache' => [
                'enabled' => env('ZOLTA_ATTR_ROUTE_CACHE', true),
                'ensure_fresh_on_boot' => env('ZOLTA_ATTR_ROUTE_ENSURE_FRESH_ON_BOOT', true),
                'skip_commands' => ['package:discover', 'make:zolta-update-namespace'],
            ],
            'paths' => [app_path('Services')],
            'documentation' => [
                'enabled' => env('ZOLTA_ROUTE_DOCS_ENABLED', true),
                'output_dir' => env('ZOLTA_ROUTE_DOCS_OUTPUT_DIR', base_path('bootstrap/cache')),
                'output_file' => env('ZOLTA_ROUTE_DOCS_OUTPUT_FILE', 'openapi.json'),
                'manifest_file' => env('ZOLTA_ROUTE_DOCS_MANIFEST_FILE', 'openapi_manifest.php'),
                'title' => env('ZOLTA_ROUTE_DOCS_TITLE', env('APP_NAME', 'Laravel').' API'),
                'version' => env('ZOLTA_ROUTE_DOCS_VERSION', env('APP_VERSION', '1.0.0')),
                'description' => env('ZOLTA_ROUTE_DOCS_DESCRIPTION', 'Auto-generated API documentation from Zolta HTTP route attributes.'),
                'server_url' => env('ZOLTA_ROUTE_DOCS_SERVER_URL', '/'),
                'server_description' => env('ZOLTA_ROUTE_DOCS_SERVER_DESCRIPTION', 'Application server'),
            ],
            'default_response' => GenericResource::class,
        ],
    ],
    'security' => ['abilities' => [], 'user' => ['class' => IdentityPrincipal::class, 'attributes' => ['identity.permissions.*']]],
    'identity' => ['class' => RemoteIdentity::class, 'permissions' => ['identity.permissions.*'], 'schema' => []],
    'identity_consumer' => [
        'connections' => [
            'live' => ['base_url' => env('IDENTITY_API_URL'), 'project' => env('IDENTITY_PROJECT'), 'client_id' => env('IDENTITY_CLIENT_ID'), 'client_secret' => env('IDENTITY_CLIENT_SECRET')],
            'sandbox' => ['base_url' => env('IDENTITY_SANDBOX_API_URL', env('IDENTITY_API_URL')), 'project' => env('IDENTITY_SANDBOX_PROJECT'), 'client_id' => env('IDENTITY_SANDBOX_CLIENT_ID'), 'client_secret' => env('IDENTITY_SANDBOX_CLIENT_SECRET')],
        ],
        'timeout_seconds' => (int) env('IDENTITY_INTROSPECTION_TIMEOUT_SECONDS', 5),
        'cache_seconds' => (int) env('IDENTITY_INTROSPECTION_CACHE_SECONDS', 30),
        'webhook_secrets' => array_values(array_filter(array_map(static fn (string $secret): string => trim($secret), explode(',', (string) env('IDENTITY_WEBHOOK_SECRETS', ''))))),
        'webhook_tolerance_seconds' => (int) env('IDENTITY_WEBHOOK_TOLERANCE_SECONDS', 300),
    ],
];
