<?php

namespace plugins\LeadCapture\Providers;

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
        $this->app->bind('app\Providers\plugins\LeadCapture\LeadCaptureController', function($app) {
            return new \app\Providers\plugins\LeadCapture\LeadCaptureController();
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
        $this->loadRoutesFrom(base_path('plugins/LeadCapture/routes.php'));
        
        // Registrar views - Corrigido para usar o caminho absoluto para as views
        View::addNamespace('leadcapture', base_path('plugins/LeadCapture/views'));
        
  
    }
}
