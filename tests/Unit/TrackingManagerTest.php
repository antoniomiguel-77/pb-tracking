<?php

declare(strict_types=1);

namespace Tests\Unit;

use Tests\TestCase;
use Yetosoft\LivewireTracking\Services\TrackingManager;

final class TrackingManagerTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config()->set('tracking', [
            'enabled' => true,
            'drivers' => [
                'facebook' => [
                    'enabled' => true,
                    'pixel_id' => 'fb-123',
                ],
                'google' => [
                    'enabled' => true,
                    'measurement_id' => 'ga-123',
                ],
                'tiktok' => [
                    'enabled' => false,
                    'pixel_id' => 'tt-123',
                ],
            ],
        ]);
    }

    public function test_manager_resolves_active_drivers(): void
    {
        $manager = app(TrackingManager::class);

        $this->assertCount(2, $manager->activeDrivers());
        $this->assertTrue($manager->enabled());
    }

    public function test_manager_dispatches_pageview_to_enabled_drivers(): void
    {
        $manager = app(TrackingManager::class);

        $results = $manager->pageView();

        $this->assertCount(2, $results);
        $this->assertSame(['facebook', 'google'], $results->pluck('driver')->all());
        $this->assertSame('PageView', $results->first()['event']);
    }

    public function test_manager_dispatches_custom_events_to_enabled_drivers(): void
    {
        $manager = app(TrackingManager::class);

        $results = $manager->track('Purchase', [
            'value' => 5000,
            'currency' => 'AOA',
        ]);

        $this->assertCount(2, $results);
        $this->assertSame('Purchase', $results->first()['event']);
        $this->assertSame(5000, $results->first()['data']['value']);
    }

    public function test_manager_renders_scripts_for_enabled_drivers(): void
    {
        $manager = app(TrackingManager::class);

        $scripts = $manager->renderScripts();

        $this->assertStringContainsString('window.YetoTracking', $scripts);
        $this->assertStringContainsString('fbq(\'init\'', $scripts);
        $this->assertStringContainsString('gtag(\'config\'', $scripts);
        $this->assertStringContainsString('livewire:navigated', $scripts);
    }
}
