<?php

declare(strict_types=1);

namespace Tests\Unit;

use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;
use Yetosoft\LivewireTracking\Drivers\FacebookPixelDriver;
use Yetosoft\LivewireTracking\Drivers\GoogleAnalyticsDriver;
use Yetosoft\LivewireTracking\Drivers\TikTokPixelDriver;

final class DriversTest extends TestCase
{
    #[DataProvider('driverProvider')]
    public function test_drivers_capture_payloads(string $driverClass, array $config, string $identifier): void
    {
        $driver = new $driverClass($config);

        $this->assertSame($identifier, $driver->identifier());
        $this->assertTrue($driver->isEnabled());

        $driver->pageView();

        $this->assertSame('PageView', $driver->payload()['event']);

        $driver->track('Purchase', [
            'value' => 5000,
            'currency' => 'AOA',
        ]);

        $this->assertSame('Purchase', $driver->payload()['event']);
        $this->assertSame(5000, $driver->payload()['data']['value']);
    }

    public static function driverProvider(): array
    {
        return [
            'facebook' => [
                FacebookPixelDriver::class,
                ['enabled' => true, 'pixel_id' => 'fb-123'],
                'facebook',
            ],
            'google' => [
                GoogleAnalyticsDriver::class,
                ['enabled' => true, 'measurement_id' => 'ga-123'],
                'google',
            ],
            'tiktok' => [
                TikTokPixelDriver::class,
                ['enabled' => true, 'pixel_id' => 'tt-123'],
                'tiktok',
            ],
        ];
    }
}
