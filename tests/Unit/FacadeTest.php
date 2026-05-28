<?php

declare(strict_types=1);

namespace Tests\Unit;

use Tests\TestCase;
use Yetosoft\LivewireTracking\Facades\Tracking;

final class FacadeTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

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
    }

    public function test_facade_tracks_pageviews(): void
    {
        $results = Tracking::pageView();

        $this->assertCount(1, $results);
        $this->assertSame('facebook', $results->first()['driver']);
    }

    public function test_facade_tracks_custom_events(): void
    {
        $results = Tracking::track('Lead', [
            'source' => 'contact-form',
        ]);

        $this->assertCount(1, $results);
        $this->assertSame('Lead', $results->first()['event']);
        $this->assertSame('contact-form', $results->first()['data']['source']);
    }
}
