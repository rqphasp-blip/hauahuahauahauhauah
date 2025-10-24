<?php

namespace App\Providers\plugins\LeadCapture\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\View;

class LeadCaptureServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        // Registrar o binding do controller
        $this->app->bind('App\Providers\plugins\LeadCapture\LeadCaptureController', function($app) {
            return new \App\Providers\plugins\LeadCapture\LeadCaptureController();
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
        View::addNamespace('leadcapture', __DIR__.'/../views');
        
        // Publicar assets, migrations, etc.
        $this->publishes([
            __DIR__.'/../views' => resource_path('views/leadcapture'),
            __DIR__.'/../create_leads_table.php' => database_path('migrations/'.date('Y_m_d_His').'_create_leads_table.php'),
        ], 'leadcapture');
    }
}
