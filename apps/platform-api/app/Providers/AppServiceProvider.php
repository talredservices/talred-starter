<?php

namespace App\Providers;

use App\Events\MappedLaravelEventDispatcher;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\ServiceProvider;
use Psr\Log\LoggerInterface;
use Zolta\Cqrs\Events\Contracts\EventDispatcherInterface;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(
            EventDispatcherInterface::class,
            static function (Application $application): EventDispatcherInterface {
                $logger = $application->bound(LoggerInterface::class)
                    ? $application->make(LoggerInterface::class)
                    : null;

                return new MappedLaravelEventDispatcher(
                    laravelDispatcher: $application->make('events'),
                    eventMap: $application->make('event.map'),
                    logger: $logger,
                );
            }
        );
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void {}
}
