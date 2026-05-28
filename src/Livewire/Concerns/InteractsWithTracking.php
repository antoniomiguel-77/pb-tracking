<?php

declare(strict_types=1);

namespace Yetosoft\LivewireTracking\Livewire\Concerns;

use LogicException;
use Yetosoft\LivewireTracking\Services\EventDispatcher;

trait InteractsWithTracking
{
    public function track(string $event, array $data = []): mixed
    {
        tracking()->track($event, $data);

        return $this->dispatchTrackingEvent($event, $data);
    }

    public function pageView(): mixed
    {
        return $this->track('PageView');
    }

    protected function dispatchTrackingEvent(string $event, array $data = []): mixed
    {
        if (! method_exists($this, 'dispatch')) {
            throw new LogicException('The InteractsWithTracking trait requires a dispatch method.');
        }

        return $this->dispatch(
            app(EventDispatcher::class)->browserEventName(),
            name: $event,
            data: $data,
            source: static::class,
        );
    }
}
