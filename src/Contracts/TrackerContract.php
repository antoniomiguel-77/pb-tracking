<?php

declare(strict_types=1);

namespace Yetosoft\LivewireTracking\Contracts;

interface TrackerContract
{
    public function pageView(): void;

    public function track(string $event, array $data = []): void;
}
