<?php

declare(strict_types=1);

namespace Yetosoft\LivewireTracking;

use Illuminate\Support\ServiceProvider;
use Yetosoft\LivewireTracking\Commands\InstallTrackingCommand;
use Yetosoft\LivewireTracking\Services\EventDispatcher;
use Yetosoft\LivewireTracking\Services\TrackingManager;

final class TrackingServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/tracking.php', 'tracking');

        $this->app->singleton(EventDispatcher::class);
        $this->app->singleton(TrackingManager::class, fn ($app): TrackingManager => new TrackingManager(
            $app['config'],
            $app->make(EventDispatcher::class),
        ));
        $this->app->alias(TrackingManager::class, 'tracking');
    }

    public function boot(): void
    {
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'tracking');

        $this->publishes([
            __DIR__.'/../config/tracking.php' => config_path('tracking.php'),
        ], 'tracking-config');

        $this->publishes([
            __DIR__.'/../resources/views' => resource_path('views/vendor/tracking'),
        ], 'tracking-views');

        $this->publishes([
            __DIR__.'/../resources/js/tracking.js' => public_path('vendor/yetosoft/livewire-tracking/tracking.js'),
        ], 'tracking-assets');

        if ($this->app->runningInConsole()) {
            $this->commands([
                InstallTrackingCommand::class,
            ]);
        }
    }
}
