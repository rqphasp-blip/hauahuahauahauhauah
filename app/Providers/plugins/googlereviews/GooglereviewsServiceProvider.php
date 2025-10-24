<?php

namespace App\Providers\plugins\googlereviews\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\View;

class GooglereviewsServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        // Registrar o binding do controller
        $this->app->bind('App\Providers\plugins\googlereviews\GooglereviewsController', function($app) {
            return new \App\Providers\plugins\googlereviews\GooglereviewsController();
        });
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        // Carregar rotas
        $this->loadRoutesFrom(base_path('app/Providers/plugins/googlereviews/routes.php'));
        
        // Registrar views
        View::addNamespace('googlereviews', __DIR__.'/../views');
        
        // Publicar assets, views, etc.
        $this->publishes([
            __DIR__.'/../views' => resource_path('views/googlereviews'),
        ], 'googlereviews');
    }
}
