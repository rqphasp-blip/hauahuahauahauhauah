<?php

namespace plugins\contatos\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;

class ContatosServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->bind('plugins\\contatos\\ContatosController', function ($app) {
            return new \plugins\contatos\ContatosController();
        });
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        $this->loadRoutesFrom(__DIR__ . '/../routes.php');

        View::addNamespace('contatos', __DIR__ . '/../views');

        $this->publishes([
            __DIR__ . '/../views' => resource_path('views/contatos'),
            __DIR__ . '/../create_lead_campaigns_table.php' => database_path('migrations/' . date('Y_m_d_His') . '_create_lead_campaigns_table.php'),
            __DIR__ . '/../create_campaign_leads_table.php' => database_path('migrations/' . date('Y_m_d_His', strtotime('+1 second')) . '_create_campaign_leads_table.php'),
        ], 'contatos');
    }
}
