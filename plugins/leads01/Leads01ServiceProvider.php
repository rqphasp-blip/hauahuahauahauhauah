<?php

namespace plugins\leads01\Providers;

use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use plugins\leads01\Leads01Controller;

class Leads01ServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(Leads01Controller::class, fn () => new Leads01Controller());
    }

    public function boot(): void
    {
        $basePath = __DIR__ . '/..';

        $routes = $basePath . '/routes.php';
        if (file_exists($routes)) {
            $this->loadRoutesFrom($routes);
        }

        $viewsPath = $basePath . '/views';
        if (is_dir($viewsPath)) {
            View::addNamespace('leads01', $viewsPath);
            $this->publishes([
                $viewsPath => resource_path('views/vendor/leads01'),
            ], 'leads01-views');
        }

        $migration = $basePath . '/database/create_leads01_tables.php';
        if (file_exists($migration)) {
            $this->publishes([
                $migration => database_path('migrations/' . date('Y_m_d_His') . '_create_leads01_tables.php'),
            ], 'leads01-migrations');
        }
    }
}