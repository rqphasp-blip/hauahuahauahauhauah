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
         if (file_exists(base_path('plugins/leads01/routes.php'))) {
            $this->loadRoutesFrom(base_path('plugins/leads01/routes.php'));
        }
        $this->loadViewsFrom(base_path('plugins/leads01/resources/views'), 'leads01');
        $this->loadMigrationsFrom(base_path('plugins/leads01/database/migrations'));

        $this->publishes([
            base_path('plugins/leads01/resources/views') => resource_path('views/leads01'),
        ], 'leads01-views');

        $this->publishes([
            base_path('plugins/leads01/database/migrations') => database_path('migrations'),
        ], 'leads01-migrations');

            $this->publishes([
                $pluginPath . '/database/migrations/2024_01_01_000000_create_lead_capture_tables.php' => database_path('migrations/' . date('Y_m_d_His') . '_create_lead_capture_tables.php'),
            ], 'leads01-migrations');
        }
    }
}