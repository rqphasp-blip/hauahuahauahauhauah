<?php

namespace App\Providers\plugins\linktreeimport\Providers;

use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class LinktreeImportServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->bind('App\\Providers\\plugins\\linktreeimport\\LinktreeImportController', function ($app) {
            return new \App\Providers\plugins\linktreeimport\LinktreeImportController();
        });
    }

    public function boot()
    {
        $this->loadRoutesFrom(__DIR__ . '/../routes.php');
        View::addNamespace('linktreeimport', __DIR__ . '/../views');

        $this->publishes([
            __DIR__ . '/../views' => resource_path('views/vendor/linktreeimport'),
        ], 'linktreeimport');
    }
}