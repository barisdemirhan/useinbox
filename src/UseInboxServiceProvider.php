<?php

namespace Barisdemirhan\Useinbox;

use Barisdemirhan\Useinbox\Transport\UseInboxAddedTransportManager;
use Illuminate\Support\ServiceProvider;

class UseInboxServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     */
    public function boot()
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/../config/config.php' => config_path('useinbox.php'),
            ], 'config');
        }
    }

    /**
     * Register the application services.
     */
    public function register()
    {
        // Automatically apply the package configuration
        $this->mergeConfigFrom(__DIR__ . '/../config/config.php', 'useinbox');

        // Register the main class to use with the facade
        $this->app->singleton('useinbox', function () {
            return new UseInbox;
        });
    }
}
