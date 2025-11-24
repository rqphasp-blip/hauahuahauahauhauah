<?php

namespace App\Providers\plugins\highlights\Providers;

use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class HighlightsServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->bind(\App\Providers\plugins\highlights\HighlightsController::class, function ($app) {
            return new \App\Providers\plugins\highlights\HighlightsController();
        });
    }

    public function boot()
    {
        $pluginPath = base_path('plugins/highlights');

        $this->loadRoutesFrom($pluginPath . '/routes.php');
        View::addNamespace('highlights', $pluginPath . '/views');

        $this->publishes([
            $pluginPath . '/views' => resource_path('views/vendor/highlights'),
            $pluginPath . '/create_user_highlights_table.php' => database_path('migrations/' . date('Y_m_d_His') . '_create_user_highlights_table.php'),
        ], 'highlights');
    }
}