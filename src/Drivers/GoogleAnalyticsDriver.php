<?php

declare(strict_types=1);

namespace Yetosoft\LivewireTracking\Drivers;

use Yetosoft\LivewireTracking\Contracts\TrackerContract;

final class GoogleAnalyticsDriver implements TrackerContract
{
    private array $payload = [];

    public function __construct(private readonly array $config = [])
    {
    }

    public function identifier(): string
    {
        return 'google';
    }

    public function viewName(): string
    {
        return 'tracking::drivers.google';
    }

    public function measurementId(): ?string
    {
        return $this->config['measurement_id'] ?? null;
    }

    public function isEnabled(): bool
    {
        return (bool) ($this->config['enabled'] ?? false) && filled($this->measurementId());
    }

    public function config(): array
    {
        return $this->config;
    }

    public function payload(): array
    {
        return $this->payload;
    }

    public function pageView(): void
    {
        $this->track('PageView');
    }

    public function track(string $event, array $data = []): void
    {
        $this->payload = [
            'driver' => $this->identifier(),
            'event' => $event,
            'data' => $data,
        ];
    }
}
