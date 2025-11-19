<?php

namespace App\Providers\plugins\leads01\Providers;

use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class Leads01ServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(\App\Providers\plugins\leads01\Leads01Controller::class, function ($app) {
            return new \App\Providers\plugins\leads01\Leads01Controller();
        });
    }

    public function boot(): void
    {
        $pluginPath = base_path('plugins/leads01');

        $this->loadRoutesFrom($pluginPath . '/routes/web.php');

        View::addNamespace('leads01', $pluginPath . '/views');

        $this->publishes([
            $pluginPath . '/views' => resource_path('views/leads01'),
        ], 'leads01-views');

        $this->loadMigrationsFrom($pluginPath . '/database/migrations');

        $this->publishes([
            $pluginPath . '/database/migrations' => database_path('migrations'),
        ], 'leads01-migrations');
    }
}