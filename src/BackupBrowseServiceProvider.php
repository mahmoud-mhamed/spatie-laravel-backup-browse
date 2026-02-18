<?php

namespace MahmoudMhamed\BackupBrowse;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\ServiceProvider;
use MahmoudMhamed\BackupBrowse\Console\Commands\RunScheduledBackups;

class BackupBrowseServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/backup-browse.php', 'backup-browse');
    }

    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');
        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'backup-browse');
        $this->loadRoutesFrom(__DIR__ . '/../routes/web.php');

        if ($this->app->runningInConsole()) {
            $this->commands([
                RunScheduledBackups::class,
            ]);

            $this->publishes([
                __DIR__ . '/../config/backup-browse.php' => config_path('backup-browse.php'),
            ], 'backup-browse-config');

            $this->publishes([
                __DIR__ . '/../database/migrations' => database_path('migrations'),
            ], 'backup-browse-migrations');

            $this->publishes([
                __DIR__ . '/../resources/views' => resource_path('views/vendor/backup-browse'),
            ], 'backup-browse-views');
        }

        $this->app->booted(function () {
            $schedule = $this->app->make(Schedule::class);
            $schedule->command('backup-browse:run-scheduled')->everyMinute();
        });
    }
}
