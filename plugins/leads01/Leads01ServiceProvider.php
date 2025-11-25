<?php

namespace plugins\leads01\Providers;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class Leads01ServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Additional bindings can be registered here if needed in the future.
    }

    public function boot(): void
    {
        $this->registerRoutes();

        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'leads01');

        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');

        $this->publishes([
            __DIR__ . '/../public' => public_path('vendor/leads01'),
        ], 'public');
    }

    protected function registerRoutes(): void
    {
        if (file_exists(__DIR__ . '/../routes/web.php')) {
            Route::middleware(['web', 'auth'])
                ->group(__DIR__ . '/../routes/web.php');
        }

        if (file_exists(__DIR__ . '/../routes/public.php')) {
            Route::middleware(['web'])
                ->group(__DIR__ . '/../routes/public.php');
        }
    }
}