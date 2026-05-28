<?php

declare(strict_types=1);

namespace Tests\Unit;

use Tests\TestCase;

final class ConfigTest extends TestCase
{
    public function test_tracking_configuration_is_loaded(): void
    {
        $this->assertTrue(config('tracking.enabled'));
        $this->assertArrayHasKey('facebook', config('tracking.drivers'));
        $this->assertArrayHasKey('google', config('tracking.drivers'));
        $this->assertArrayHasKey('tiktok', config('tracking.drivers'));
    }
}
