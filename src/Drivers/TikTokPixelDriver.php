<?php

declare(strict_types=1);

namespace Yetosoft\LivewireTracking\Drivers;

use Yetosoft\LivewireTracking\Contracts\TrackerContract;

final class TikTokPixelDriver implements TrackerContract
{
    private array $payload = [];

    public function __construct(private readonly array $config = [])
    {
    }

    public function identifier(): string
    {
        return 'tiktok';
    }

    public function viewName(): string
    {
        return 'tracking::drivers.tiktok';
    }

    public function pixelId(): ?string
    {
        return $this->config['pixel_id'] ?? null;
    }

    public function isEnabled(): bool
    {
        return (bool) ($this->config['enabled'] ?? false) && filled($this->pixelId());
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
