<?php

declare(strict_types=1);

namespace App\Services\PlatformWorkspace\Infrastructure\Providers;

use App\Services\PlatformWorkspace\Application\Contracts\WorkspaceReader;
use App\Services\PlatformWorkspace\Domain\Repositories\WorkspaceRepository;
use App\Services\PlatformWorkspace\Domain\Repositories\WorkspaceResourceRepository;
use App\Services\PlatformWorkspace\Infrastructure\Repositories\EloquentWorkspaceRepository;
use App\Services\PlatformWorkspace\Infrastructure\Repositories\EloquentWorkspaceResourceRepository;
use Illuminate\Support\ServiceProvider;

final class PlatformWorkspaceServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(WorkspaceRepository::class, EloquentWorkspaceRepository::class);
        $this->app->bind(WorkspaceResourceRepository::class, EloquentWorkspaceResourceRepository::class);
        $this->app->bind(WorkspaceReader::class, EloquentWorkspaceRepository::class);
    }

    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__.'/../Persistence/Migrations');
    }
}
