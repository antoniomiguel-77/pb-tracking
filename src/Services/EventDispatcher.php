<?php

declare(strict_types=1);

namespace Yetosoft\LivewireTracking\Services;

final class EventDispatcher
{
    public const BROWSER_EVENT = 'yeto-track';

    public function browserEventName(): string
    {
        return self::BROWSER_EVENT;
    }

    public function normalize(string $event, array $data = [], array $meta = []): array
    {
        return [
            'event' => $event,
            'data' => $data,
            'meta' => array_merge([
                'origin' => 'server',
                'timestamp' => now()->toIso8601String(),
            ], $meta),
        ];
    }

    public function forDriver(string $driver, string $event, array $data = [], array $meta = []): array
    {
        return array_merge($this->normalize($event, $data, $meta), [
            'driver' => $driver,
        ]);
    }
}
