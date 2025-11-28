<?php

namespace App\Providers\plugins\leads01\Providers;

use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class Leads01ServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Carrega os Models manualmente (ordem importa por causa dos relacionamentos)
        $modelsPath = base_path('plugins/leads01/Models');
        $models = ['LeadField', 'LeadEntry', 'LeadCampaign'];
        
        foreach ($models as $model) {
            $modelClass = "plugins\\leads01\\Models\\{$model}";
            if (!class_exists($modelClass)) {
                require_once "{$modelsPath}/{$model}.php";
            }
        }

		
		  // Garante alias globais (\\LeadCampaign, etc.) para uso em outros controllers
            $globalAlias = "\\{$model}";
            if (!class_exists($globalAlias, false)) {
                class_alias($modelClass, $globalAlias);
            }
		
        // Garante que o controller esteja disponível
        if (!class_exists(\App\Providers\plugins\leads01\Leads01Controller::class)) {
            require_once base_path('app/Providers/plugins/leads01/Leads01Controller.php');
        }

        // Cria alias para compatibilidade com namespace antigo
        if (!class_exists('plugins\\leads01\\Http\\Controllers\\Leads01Controller')) {
            class_alias(
                \App\Providers\plugins\leads01\Leads01Controller::class,
                'plugins\\leads01\\Http\\Controllers\\Leads01Controller'
            );
        }

        $this->app->bind(\App\Providers\plugins\leads01\Leads01Controller::class, function ($app) {
            return new \App\Providers\plugins\leads01\Leads01Controller();
        });
    }

    public function boot(): void
    {
        $pluginPath = base_path('plugins/leads01');

        $this->loadRoutesFrom($pluginPath . '/routes/web.php');

        View::addNamespace('leads01', $pluginPath . '/views');

        $this->publishes([
            $pluginPath . '/views' => resource_path('views/leads01'),
        ], 'leads01-views');

        $this->loadMigrationsFrom($pluginPath . '/database/migrations');

        $this->publishes([
            $pluginPath . '/database/migrations' => database_path('migrations'),
        ], 'leads01-migrations');
    }
}