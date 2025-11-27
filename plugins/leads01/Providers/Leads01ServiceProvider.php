<?php

namespace App\Providers\plugins\leads01\Providers;

use Illuminate\Support\ServiceProvider;

class Leads01ServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Garante que o controller na pasta app esteja disponível
        if (!class_exists(\App\Providers\plugins\leads01\Leads01Controller::class)) {
            require_once base_path('app/Providers/plugins/leads01/Leads01Controller.php');
        }

        // Cria alias ANTES de qualquer outra coisa
        if (!class_exists('plugins\\leads01\\Http\\Controllers\\Leads01Controller')) {
            class_alias(
                \App\Providers\plugins\leads01\Leads01Controller::class,
                'plugins\\leads01\\Http\\Controllers\\Leads01Controller'
            );
        }

        $this->app->bind('leads01.controller', function () {
            return $this->app->make(\App\Providers\plugins\leads01\Leads01Controller::class);
        });
    }

    public function boot(): void
    {
        // Carrega rotas DEPOIS do register
        if (file_exists(base_path('plugins/leads01/routes.php'))) {
            $this->loadRoutesFrom(base_path('plugins/leads01/routes.php'));
        }
        $this->loadViewsFrom(base_path('plugins/leads01/resources/views'), 'leads01');
        $this->loadMigrationsFrom(base_path('plugins/leads01/database/migrations'));

        $this->publishes([
            base_path('plugins/leads01/resources/views') => resource_path('views/leads01'),
        ], 'leads01-views');

        $this->publishes([
            base_path('plugins/leads01/database/migrations') => database_path('migrations'),
        ], 'leads01-migrations');
    }
}