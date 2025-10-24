<?php

namespace plugins\UserProfileBanner\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\View;

class UserProfileBannerServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Registra as views
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'userprofilebanner');
        
        // Registra as rotas
        $this->loadRoutesFrom(__DIR__.'/../routes/web.php');
        
        // Registra as migrations
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
    }
}
