<?php

namespace App\Providers\plugins\leads\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\View;

class LeadsServiceProvider extends ServiceProvider
{
    /**
     * Register any plugin services.
     */
    public function register()
    {
        // Registrar o binding do controller principal do plugin
        $this->app->bind('App\Providers\plugins\leads\LeadsController', function($app) {
            return new \App\Providers\plugins\leads\LeadsController();
        });
    }

    /**
     * Bootstrap plugin services.
     */
    public function boot()
    {
        // Carregar rotas
        $this->loadRoutesFrom(__DIR__ . '/../routes.php');

        // Registrar namespace das views
        View::addNamespace('leads', __DIR__ . '/../views');

        // Publicar arquivos (para caso o usuário queira copiar views/migrações)
        $this->publishes([
            __DIR__ . '/../views' => resource_path('views/leads'),
            __DIR__ . '/../create_user_leads_tables.php' => database_path(
                'migrations/' . date('Y_m_d_His') . '_create_user_leads_tables.php'
            ),
        ], 'leads');
		
		 // 🔎 DEBUG: Verificar se o provider está sendo carregado
    \Log::info('✅ LeadsServiceProvider carregado com sucesso.');
    // ou, para teste temporário direto no navegador:
    // echo "<!-- LeadsServiceProvider booted -->";
		
		
    }
}
