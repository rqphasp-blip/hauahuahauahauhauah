<?php

namespace plugins\GoogleMapsProfile\Providers;

use Illuminate\Support\ServiceProvider;

class GoogleMapsProfileServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        // No bindings required for now
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        $this->loadRoutesFrom(__DIR__ . '/../routes/web.php');
        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'googlemapsprofile');
        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');
    }
}