<?php

declare(strict_types=1);

namespace Yetosoft\LivewireTracking\Commands;

use Illuminate\Console\Command;

final class InstallTrackingCommand extends Command
{
    protected $signature = 'tracking:install {--force : Overwrite existing published files}';

    protected $description = 'Install the YetoSoft Livewire Tracking package assets and configuration.';

    public function handle(): int
    {
        $force = (bool) $this->option('force');

        $this->call('vendor:publish', [
            '--tag' => 'tracking-config',
            '--force' => $force,
        ]);

        $this->call('vendor:publish', [
            '--tag' => 'tracking-views',
            '--force' => $force,
        ]);

        $this->call('vendor:publish', [
            '--tag' => 'tracking-assets',
            '--force' => $force,
        ]);

        $this->newLine();
        $this->info('YetoSoft Livewire Tracking installed successfully.');
        $this->line('1. Configure your pixel IDs in config/tracking.php or via environment variables.');
        $this->line('2. Add @include(\'tracking::scripts\') to your main Blade layout.');
        $this->line('3. Use Tracking::track(...) or the InteractsWithTracking trait in Livewire components.');
        $this->newLine();

        return self::SUCCESS;
    }
}
