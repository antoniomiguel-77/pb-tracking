<?php

declare(strict_types=1);

namespace Tests\Feature;

use Tests\TestCase;
use Yetosoft\LivewireTracking\Livewire\Concerns\InteractsWithTracking;
use Yetosoft\LivewireTracking\Services\EventDispatcher;

final class TrackingDispatchTest extends TestCase
{
    public function test_livewire_trait_dispatches_browser_events(): void
    {
        config()->set('tracking.enabled', true);
        config()->set('tracking.drivers.facebook', [
            'enabled' => true,
            'pixel_id' => 'fb-123',
        ]);
        config()->set('tracking.drivers.google', [
            'enabled' => false,
            'measurement_id' => 'ga-123',
        ]);
        config()->set('tracking.drivers.tiktok', [
            'enabled' => false,
            'pixel_id' => 'tt-123',
        ]);

        $component = new class () {
            use InteractsWithTracking;

            public array $dispatched = [];

            public function dispatch(string $event, ...$payload): object
            {
                $this->dispatched[] = [
                    'event' => $event,
                    'payload' => $payload,
                ];

                return (object) end($this->dispatched);
            }
        };

        $component->track('Purchase', [
            'value' => 5000,
            'currency' => 'AOA',
        ]);

        $this->assertCount(1, $component->dispatched);
        $this->assertSame(EventDispatcher::BROWSER_EVENT, $component->dispatched[0]['event']);
        $this->assertSame('Purchase', $component->dispatched[0]['payload']['name']);
        $this->assertSame(5000, $component->dispatched[0]['payload']['data']['value']);
    }
}
