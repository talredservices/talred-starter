<?php

use App\Providers\AppServiceProvider;
use App\Services\PlatformWorkspace\Infrastructure\Providers\PlatformWorkspaceServiceProvider;
use App\Services\PlatformOrganization\Infrastructure\Providers\PlatformOrganizationServiceProvider;

return [
    AppServiceProvider::class,
    PlatformWorkspaceServiceProvider::class,
    PlatformOrganizationServiceProvider::class,
];
