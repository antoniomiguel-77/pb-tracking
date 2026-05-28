<?php

return [

    'enabled' => true,

    'drivers' => [

        'facebook' => [
            'enabled' => true,
            'pixel_id' => env('FB_PIXEL_ID'),
        ],

        'google' => [
            'enabled' => false,
            'measurement_id' => env('GA_MEASUREMENT_ID'),
        ],

        'tiktok' => [
            'enabled' => false,
            'pixel_id' => env('TIKTOK_PIXEL_ID'),
        ],

    ],

    'storage' => [
        'enabled' => true,
        'connection' => env('TRACKING_DB_CONNECTION'),
        'table' => 'tracking_events',
    ],

];
