<?php

declare(strict_types=1);

namespace Yetosoft\LivewireTracking\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class TrackingEvent extends Model
{
    protected $table;

    protected $fillable = [
        'event',
        'driver',
        'drivers',
        'data',
        'meta',
        'url',
        'ip_address',
        'user_agent',
        'user_id',
        'session_id',
    ];

    protected $casts = [
        'drivers' => 'array',
        'data' => 'array',
        'meta' => 'array',
        'user_id' => 'integer',
    ];

    public function __construct(array $attributes = [])
    {
        $this->table = config('tracking.storage.table', 'tracking_events');

        parent::__construct($attributes);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(config('auth.providers.users.model', \App\Models\User::class));
    }
}
