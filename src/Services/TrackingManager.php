<?php

declare(strict_types=1);

namespace Yetosoft\LivewireTracking\Services;

use Illuminate\Contracts\Config\Repository as ConfigRepository;
use Illuminate\Support\Collection;
use Yetosoft\LivewireTracking\Contracts\TrackerContract;
use Yetosoft\LivewireTracking\Drivers\FacebookPixelDriver;
use Yetosoft\LivewireTracking\Drivers\GoogleAnalyticsDriver;
use Yetosoft\LivewireTracking\Drivers\TikTokPixelDriver;

final class TrackingManager
{
    public function __construct(
        private readonly ConfigRepository $config,
        private readonly EventDispatcher $events,
    ) {
    }

    public function enabled(): bool
    {
        return (bool) $this->config->get('tracking.enabled', false);
    }

    public function pageView(): Collection
    {
        return $this->dispatchToDrivers('PageView');
    }

    public function track(string $event, array $data = []): Collection
    {
        return $this->dispatchToDrivers($event, $data);
    }

    public function activeDrivers(): Collection
    {
        if (! $this->enabled()) {
            return collect();
        }

        return collect($this->config->get('tracking.drivers', []))
            ->filter(fn (array $driverConfig, string $name): bool => $this->isDriverEnabled($name, $driverConfig))
            ->map(fn (array $driverConfig, string $name): array => [
                'name' => $name,
                'config' => $driverConfig,
                'driver' => $this->resolveDriver($name, $driverConfig),
            ])
            ->filter(fn (array $driver): bool => $driver['driver'] instanceof TrackerContract)
            ->values();
    }

    public function renderScripts(): string
    {
        if (! $this->enabled()) {
            return '';
        }

        $scripts = [];
        $scripts[] = $this->renderConfigurationScript();

        foreach ($this->activeDrivers() as $driverData) {
            $driver = $driverData['driver'];
            $view = $driver->viewName();

            if (! view()->exists($view)) {
                continue;
            }

            $scripts[] = view($view, [
                'driver' => $driver,
                'config' => $driverData['config'],
            ])->render();
        }

        $scripts[] = $this->renderJavaScript();

        return implode(PHP_EOL, array_filter($scripts));
    }

    private function dispatchToDrivers(string $event, array $data = []): Collection
    {
        if (! $this->enabled()) {
            return collect();
        }

        return $this->activeDrivers()->map(function (array $driverData) use ($event, $data): array {
            /** @var TrackerContract $driver */
            $driver = $driverData['driver'];

            if ($event === 'PageView') {
                $driver->pageView();
            } else {
                $driver->track($event, $data);
            }

            $payload = method_exists($driver, 'payload') ? $driver->payload() : [
                'event' => $event,
                'data' => $data,
            ];

            return $this->events->forDriver(
                $driverData['name'],
                $event,
                $payload['data'] ?? $data,
                [
                    'origin' => 'php',
                    'payload' => $payload,
                ]
            );
        });
    }

    private function isDriverEnabled(string $name, array $driverConfig): bool
    {
        return match ($name) {
            'facebook' => (bool) ($driverConfig['enabled'] ?? false) && filled($driverConfig['pixel_id'] ?? null),
            'google' => (bool) ($driverConfig['enabled'] ?? false) && filled($driverConfig['measurement_id'] ?? null),
            'tiktok' => (bool) ($driverConfig['enabled'] ?? false) && filled($driverConfig['pixel_id'] ?? null),
            default => (bool) ($driverConfig['enabled'] ?? false) && $this->resolveDriverClass($name, $driverConfig) !== null,
        };
    }

    private function resolveDriver(string $name, array $driverConfig): ?TrackerContract
    {
        $class = $this->resolveDriverClass($name, $driverConfig);

        if ($class === null || ! class_exists($class)) {
            return null;
        }

        if (! is_a($class, TrackerContract::class, true)) {
            return null;
        }

        return app()->makeWith($class, [
            'config' => $driverConfig,
        ]);
    }

    private function resolveDriverClass(string $name, array $driverConfig): ?string
    {
        return $driverConfig['driver_class']
            ?? $driverConfig['class']
            ?? match ($name) {
                'facebook' => FacebookPixelDriver::class,
                'google' => GoogleAnalyticsDriver::class,
                'tiktok' => TikTokPixelDriver::class,
                default => null,
            };
    }

    private function renderConfigurationScript(): string
    {
        $config = [
            'enabled' => $this->enabled(),
            'drivers' => collect($this->config->get('tracking.drivers', []))
                ->mapWithKeys(fn (array $driverConfig, string $name): array => [
                    $name => [
                        'enabled' => (bool) ($driverConfig['enabled'] ?? false),
                        'data' => array_diff_key($driverConfig, array_flip(['enabled', 'driver_class', 'class'])),
                    ],
                ])
                ->all(),
        ];

        return '<script>'
            .'window.YetoTracking = window.YetoTracking || {};'
            .'window.YetoTracking.config = '.json_encode($config, JSON_THROW_ON_ERROR).';'
            .'window.YetoTracking.providers = window.YetoTracking.providers || {};'
            .'</script>';
    }

    private function renderJavaScript(): string
    {
        $path = dirname(__DIR__, 2).'/resources/js/tracking.js';

        if (! is_file($path)) {
            return '';
        }

        return '<script>'.file_get_contents($path).'</script>';
    }
}
