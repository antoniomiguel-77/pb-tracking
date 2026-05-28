<?php

declare(strict_types=1);

namespace Yetosoft\LivewireTracking\Facades;

use Illuminate\Support\Facades\Facade;

final class Tracking extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'tracking';
    }
}
