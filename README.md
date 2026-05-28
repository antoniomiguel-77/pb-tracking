# YetoSoft Livewire Tracking

`yetosoft/livewire-tracking` is an enterprise-ready Laravel package for centralized tracking across multiple providers, with first-class support for Livewire 3, `wire:navigate`, browser events, and Alpine.js.

It is built to be reusable, configuration-driven, and easy to extend with new tracking drivers.

## Highlights

- Laravel 10+ and Laravel 11+ compatible
- Livewire 3 friendly
- Supports Facebook Pixel, Google Analytics 4, and TikTok Pixel
- Multi-provider driver architecture
- Facade and helper support
- Publishable config, views, and assets
- Browser-event based tracking pipeline
- SPA navigation support through `livewire:navigated`
- Alpine.js integration

## Installation

Install the package through Composer:

```bash
composer require yetosoft/livewire-tracking
```

Then run the installer:

```bash
php artisan tracking:install
```

The install command publishes:

- `config/tracking.php`
- package views
- the standalone JavaScript asset
- the database migration for the `tracking_events` table

After publishing, run:

```bash
php artisan migrate
```

## Configuration

The package ships with a publishable config file at `config/tracking.php`.

```php
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

];
```

Recommended `.env` entries:

```env
FB_PIXEL_ID=1234567890
GA_MEASUREMENT_ID=G-XXXXXXXXXX
TIKTOK_PIXEL_ID=CXXXXXXXXXX
```

### Storage

The package can persist every captured tracking action into the database.

```php
'storage' => [
    'enabled' => true,
    'connection' => env('TRACKING_DB_CONNECTION'),
    'table' => 'tracking_events',
],
```

When storage is enabled, each `Tracking::track(...)` call is written to the `tracking_events` table as an audit trail.

#### Stored fields

The `tracking_events` table stores:

- `event`
- `driver`
- `drivers`
- `data`
- `meta`
- `url`
- `ip_address`
- `user_agent`
- `user_id`
- `session_id`

This gives you an auditable history of tracked actions for debugging, analytics review, and later processing.

## Publishing Assets

If you want to publish manually:

```bash
php artisan vendor:publish --tag=tracking-config
php artisan vendor:publish --tag=tracking-views
php artisan vendor:publish --tag=tracking-assets
php artisan vendor:publish --tag=tracking-migrations
```

If you only want the database layer, publish the migration and run `migrate`:

```bash
php artisan vendor:publish --tag=tracking-migrations
php artisan migrate
```

## Blade Integration

Include the package scripts in your base layout:

```blade
@include('tracking::scripts')
```

The view renders:

- provider bootstrap snippets
- the `window.YetoTracking` global
- the tracking runtime

## Livewire 3 Integration

Use the `InteractsWithTracking` trait in any Livewire 3 component:

```php
use Yetosoft\LivewireTracking\Livewire\Concerns\InteractsWithTracking;

class CheckoutForm extends \Livewire\Component
{
    use InteractsWithTracking;

    public function submit(): void
    {
        $this->track('Purchase', [
            'value' => 5000,
            'currency' => 'AOA',
        ]);
    }
}
```

The trait dispatches a modern browser event named `yeto-track`, which is consumed by the JavaScript runtime.

## Facade Usage

```php
use Yetosoft\LivewireTracking\Facades\Tracking;

Tracking::pageView();

Tracking::track('Lead', [
    'source' => 'contact-form',
]);
```

## Helper Usage

```php
tracking()->track('AddToCart', [
    'product_id' => 1001,
    'quantity' => 2,
]);
```

## JavaScript API

The package exposes a global object:

```js
window.YetoTracking
```

Available methods:

```js
window.YetoTracking.pageView();
window.YetoTracking.track('Purchase', {
    value: 5000,
    currency: 'AOA',
});
```

### Browser Events

The runtime listens to:

- `yeto-track`
- `livewire:navigated`
- `alpine:init`

This makes the package work naturally with:

- Livewire 3 SPA navigation
- browser-dispatched tracking events
- Alpine.js stores and magic helpers

## Facebook Pixel

Supported actions:

- `PageView`
- `Purchase`
- `Lead`
- `CompleteRegistration`
- `AddToCart`
- custom events

Example:

```php
Tracking::track('Purchase', [
    'value' => 5000,
    'currency' => 'AOA',
]);
```

## Google Analytics 4

The Google driver maps common commerce actions to GA4-friendly event names:

- `PageView` -> `page_view`
- `Purchase` -> `purchase`
- `Lead` -> `generate_lead`
- `CompleteRegistration` -> `sign_up`
- `AddToCart` -> `add_to_cart`

## TikTok Pixel

The TikTok driver bootstraps the pixel and forwards tracking calls from the browser runtime.

## Alpine.js Integration

The runtime registers:

- `Alpine.store('yetoTracking', ...)`
- `Alpine.magic('track', ...)`

Example:

```html
<button x-on:click="$track('Lead', { source: 'newsletter' })">
    Subscribe
</button>
```

## SPA Navigation Support

Livewire 3 fires `livewire:navigated` after SPA transitions. The package listens to that event and automatically sends a page view again, ensuring page navigation is tracked correctly without jQuery or Livewire 2 APIs.

## Browser Event Payload

The browser event payload uses this shape:

```json
{
  "event": "Purchase",
  "data": {
    "value": 5000,
    "currency": "AOA"
  },
  "meta": {
    "origin": "server",
    "timestamp": "2026-05-28T00:00:00Z"
  }
}
```

## Real Examples

### Purchase

```php
$this->track('Purchase', [
    'value' => 5000,
    'currency' => 'AOA',
    'transaction_id' => 'ORD-2026-001',
]);
```

That event is sent to the active providers and stored in the `tracking_events` table for auditing, debugging, or later processing.

### Database-backed tracking flow

1. A Livewire component calls `track(...)` or the facade/helper does it.
2. The manager forwards the event to every enabled driver.
3. The `TrackingEventRecorder` stores a row in `tracking_events`.
4. The browser runtime can still react to the `yeto-track` event if needed.

### Lead

```php
Tracking::track('Lead', [
    'source' => 'contact-form',
    'campaign' => 'landing-page',
]);
```

### Complete Registration

```php
Tracking::track('CompleteRegistration', [
    'method' => 'email',
    'plan' => 'enterprise',
]);
```

## Architecture

The package follows a clean, extensible structure:

- `Contracts/TrackerContract.php`
- `Drivers/*` for provider-specific drivers
- `Services/TrackingManager.php` for central orchestration
- `Services/EventDispatcher.php` for event normalization
- `Services/TrackingEventRecorder.php` for database persistence
- `Livewire/Concerns/InteractsWithTracking.php` for component integration
- `TrackingServiceProvider.php` for registration and publishing
- `resources/js/tracking.js` for client-side dispatch

### Manager Responsibilities

- resolve active drivers
- validate enabled providers
- dispatch page views and custom events
- persist captured events into the database
- render package scripts
- support future custom driver classes

### Database Schema

The package creates a `tracking_events` table with:

- `event`
- `driver`
- `drivers`
- `data`
- `meta`
- `url`
- `ip_address`
- `user_agent`
- `user_id`
- `session_id`

This gives you a durable audit trail of every tracked interaction.

### Custom connection

If you prefer a dedicated database connection, set:

```env
TRACKING_DB_CONNECTION=sqlite
```

Or any other Laravel connection name supported by your project.

### Driver Extension

To create a new driver, add a config entry such as:

```php
'drivers' => [
    'linkedin' => [
        'enabled' => true,
        'driver_class' => App\\Tracking\\Drivers\\LinkedInDriver::class,
        'pixel_id' => env('LINKEDIN_PIXEL_ID'),
    ],
],
```

Your class should implement `Yetosoft\LivewireTracking\Contracts\TrackerContract`.

## Troubleshooting

### Nothing tracks

- confirm `tracking.enabled` is `true`
- confirm the driver is enabled
- confirm the pixel or measurement ID is set

### Duplicate page views

- include the package scripts only once in your layout
- do not manually call `pageView()` if you already rely on SPA navigation auto-tracking

### Livewire events are not firing

- ensure you are using Livewire 3
- make sure your component uses `dispatch()` and not legacy Livewire 2 APIs

### Alpine helpers do not exist

- make sure `tracking::scripts` is included after Alpine is loaded

## Tests

The package includes automated tests for:

- manager behavior
- driver payload capture
- facade resolution
- service provider registration
- config loading
- browser dispatch flow

Run them with:

```bash
phpunit
```

## Contribution

Contributions are welcome. Keep changes:

- typed
- focused
- test-covered
- compatible with Laravel 10+ and Livewire 3

## Roadmap

- additional drivers
- queued/offline tracking adapters
- more provider-specific normalization helpers
- richer analytics event maps
- optional asset bundling support
