# Changelog

All notable changes to `pb/livewire-tracking` will be documented in this file.

The format is based on [Semantic Versioning](https://semver.org/).

## [1.0.0] - 2026-05-28

### Added

- Initial enterprise-ready release
- Laravel 10+ and Laravel 11+ compatibility
- Livewire 3 browser-event integration
- `wire:navigate` and `livewire:navigated` support
- Facebook Pixel driver
- Google Analytics 4 driver
- TikTok Pixel driver
- `Tracking` facade
- `InteractsWithTracking` Livewire trait
- `tracking:install` artisan command
- publishable config, views, and assets
- `window.YetoTracking` JavaScript runtime
- multi-provider driver manager
- automated test coverage for core flows

## [1.1.0] - 2026-05-28

### Added

- database persistence for captured tracking events
- publishable migration for the `tracking_events` table
- `TrackingEventRecorder` service for audit logging
- storage configuration section

### Changed

- install command now publishes the migration and reminds the user to run `migrate`
