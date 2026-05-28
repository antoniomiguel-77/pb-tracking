<?php

declare(strict_types=1);

namespace Tests\Feature;

use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

final class ServiceProviderTest extends TestCase
{
    public function test_provider_registers_binding_and_views(): void
    {
        $this->assertTrue(app()->bound('tracking'));
        $this->assertTrue(view()->exists('tracking::scripts'));
        $this->assertTrue(view()->exists('tracking::drivers.facebook'));
        $this->assertTrue(view()->exists('tracking::drivers.google'));
        $this->assertTrue(view()->exists('tracking::drivers.tiktok'));
    }

    public function test_install_command_is_registered(): void
    {
        $this->assertArrayHasKey('tracking:install', Artisan::all());
    }
}
