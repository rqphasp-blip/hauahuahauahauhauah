<?php

namespace App\Providers\plugins\leads01\Providers;

use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class Leads01ServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind('App\\Providers\\plugins\\leads01\\Leads01Controller', function ($app) {
            return new \App\Providers\plugins\leads01\Leads01Controller();
        });
    }

    public function boot(): void
    {
        $pluginPath = base_path('plugins/leads01');

        if (file_exists($pluginPath . '/routes.php')) {
            $this->loadRoutesFrom($pluginPath . '/routes.php');
        }

        if (is_dir($pluginPath . '/views')) {
            View::addNamespace('leads01', $pluginPath . '/views');
        }

        if (file_exists($pluginPath . '/create_leads01_tables.php')) {
            $this->publishes([
                $pluginPath . '/create_leads01_tables.php' => database_path('migrations/' . date('Y_m_d_His') . '_create_leads01_tables.php'),
            ], 'leads01-migrations');
        }

        if (is_dir($pluginPath . '/views')) {
            $this->publishes([
                $pluginPath . '/views' => resource_path('views/vendor/leads01'),
            ], 'leads01-views');
        }
    }
}