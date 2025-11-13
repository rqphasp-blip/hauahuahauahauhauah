<?php

namespace plugins\leads01\Providers;

use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use plugins\leads01\Http\Controllers\Leads01Controller;

use function class_exists;

class Leads01ServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(Leads01Controller::class, fn () => new Leads01Controller());

        $appScopedController = 'App\\Providers\\plugins\\leads01\\Leads01Controller';
        if (class_exists($appScopedController)) {
            $this->app->singleton($appScopedController, function ($app) use ($appScopedController) {
                return $app->make(Leads01Controller::class);
            });
        }
    }

    public function boot(): void
    {
        $pluginPath = __DIR__ . '/../';

        $routesPath = $pluginPath . 'routes/web.php';
        if (file_exists($routesPath)) {
            $this->loadRoutesFrom($routesPath);
        }

        $viewsPath = $pluginPath . 'views';
        if (is_dir($viewsPath)) {
            $paths = [$viewsPath];
            $overridePath = resource_path('views/leads01');
            if (is_dir($overridePath)) {
                array_unshift($paths, $overridePath);
            }

            View::addNamespace('leads01', $paths);
            $this->publishes([
                $viewsPath => resource_path('views/leads01'),
            ], 'leads01-views');
        }

        $migrationsPath = $pluginPath . 'database/migrations';
        if (is_dir($migrationsPath)) {
            $this->loadMigrationsFrom($migrationsPath);
            $this->publishes([
                $migrationsPath => database_path('migrations'),
            ], 'leads01-migrations');
        }
    }
}