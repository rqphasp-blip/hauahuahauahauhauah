<?php

namespace App\Providers\plugins\stories\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\View;

class StoriesServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        // Registrar o binding do controller
        $this->app->bind('App\Providers\plugins\stories\StoriesController', function($app) {
            return new \App\Providers\plugins\stories\StoriesController();
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
        $this->loadRoutesFrom(__DIR__.'/../routes.php');
        
        // Registrar views
        View::addNamespace('stories', __DIR__.'/../views');
        
        // Publicar assets, migrations, etc.
        $this->publishes([
            __DIR__.'/../views' => resource_path('views/stories'),
            __DIR__.'/../create_user_stories_table.php' => database_path('migrations/'.date('Y_m_d_His').'_create_user_stories_table.php'),
        ], 'stories');
    }
}
