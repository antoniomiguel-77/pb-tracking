<?php

declare(strict_types=1);

use Yetosoft\LivewireTracking\Services\TrackingManager;

if (! function_exists('tracking')) {
    function tracking(): TrackingManager
    {
        return app(TrackingManager::class);
    }
}

if (! function_exists('tracking_enabled')) {
    function tracking_enabled(): bool
    {
        return tracking()->enabled();
    }
}

if (! function_exists('tracking_config')) {
    function tracking_config(?string $key = null, mixed $default = null): mixed
    {
        $config = config('tracking');

        if ($key === null) {
            return $config;
        }

        return data_get($config, $key, $default);
    }
}
