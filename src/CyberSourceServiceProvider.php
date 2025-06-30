<?php

namespace Asciisd\CyberSource;

use Illuminate\Support\ServiceProvider;
use Asciisd\CyberSource\Console\Commands\InstallCommand;

class CyberSourceServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     */
    public function boot()
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../config/cybersource.php' => config_path('cybersource.php'),
            ], 'cybersource-config');

            $this->publishes([
                __DIR__.'/../database/migrations/' => database_path('migrations'),
            ], 'cybersource-migrations');

            $this->loadViewsFrom(__DIR__.'/../resources/views', 'cybersource');

            $this->publishes([
                __DIR__.'/../public' => public_path('vendor/cybersource'),
            ], 'cybersource-public');

            // Register commands
            $this->commands([
                InstallCommand::class,
            ]);
        }
    }

    /**
     * Register the application services.
     */
    public function register()
    {
        // Merge config
        $this->mergeConfigFrom(__DIR__.'/../config/cybersource.php', 'cybersource');

        // Register the main class to use with the facade
        $this->app->singleton('cybersource', function ($app) {
            return new CyberSource(config('cybersource'));
        });
    }
}
