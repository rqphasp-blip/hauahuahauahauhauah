<?php

namespace App\Providers\plugins\leads01\Providers;

use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class Leads01ServiceProvider extends ServiceProvider
{
    public function register(): void
    {
		
		 // Carrega manualmente os models do plugin e registra aliases globais
        $modelsPath = base_path('plugins/leads01/Models');
        $models = ['LeadField', 'LeadEntry', 'LeadCampaign'];

        foreach ($models as $model) {
            $modelClass = "plugins\\leads01\\Models\\{$model}";

            if (!class_exists($modelClass)) {
                $file = "{$modelsPath}/{$model}.php";
                if (file_exists($file)) {
                    require_once $file;
                }
            }

            $globalAlias = "\\{$model}";
            if (!class_exists($globalAlias, false) && class_exists($modelClass)) {
                class_alias($modelClass, $globalAlias);
            }
        }

        // Garante compatibilidade com o namespace antigo do controller
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