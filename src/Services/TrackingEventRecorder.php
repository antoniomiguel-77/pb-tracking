<?php

declare(strict_types=1);

namespace Yetosoft\LivewireTracking\Services;

use Illuminate\Contracts\Config\Repository as ConfigRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Yetosoft\LivewireTracking\Models\TrackingEvent;

final class TrackingEventRecorder
{
    public function __construct(
        private readonly ConfigRepository $config,
    ) {
    }

    public function enabled(): bool
    {
        return (bool) $this->config->get('tracking.storage.enabled', true);
    }

    public function record(string $event, array $data = [], array $drivers = [], array $meta = []): ?TrackingEvent
    {
        if (! $this->enabled()) {
            return null;
        }

        $request = app()->bound('request') ? app(Request::class) : null;
        $connection = $this->config->get('tracking.storage.connection');

        $record = new TrackingEvent([
            'event' => $event,
            'driver' => $meta['driver'] ?? null,
            'drivers' => $drivers,
            'data' => $data,
            'meta' => $meta,
            'url' => $request?->fullUrl(),
            'ip_address' => $request?->ip(),
            'user_agent' => $request?->userAgent(),
            'user_id' => Auth::id(),
            'session_id' => $request && $request->hasSession() ? $request->session()->getId() : null,
        ]);

        if ($connection) {
            $record->setConnection($connection);
        }

        $record->save();

        return $record;
    }
}
