<?php

declare(strict_types=1);

namespace App\Services\PlatformOrganization\Infrastructure\Providers;

use App\Services\PlatformOrganization\Application\Contracts\IdentityApiGateway;
use App\Services\PlatformOrganization\Application\Contracts\PlatformOrganizationManagement;
use App\Services\PlatformOrganization\Infrastructure\Services\HttpIdentityApiGateway;
use App\Services\PlatformOrganization\Infrastructure\Services\PlatformOrganizationManager;
use Illuminate\Support\ServiceProvider;

final class PlatformOrganizationServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(IdentityApiGateway::class, HttpIdentityApiGateway::class);
        $this->app->bind(PlatformOrganizationManagement::class, PlatformOrganizationManager::class);
    }
}
