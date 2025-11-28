<?php

namespace plugins\highlights\Providers;

use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class HighlightsServiceProvider extends ServiceProvider
{
    public function register()
    {
    require_once __DIR__ . '/../HighlightsController.php';

       if (!class_exists(\plugins\highlights\HighlightsController::class)) {
            require_once base_path('plugins/highlights/HighlightsController.php');
        }

        $this->app->bind(\plugins\highlights\HighlightsController::class, function ($app) {
            return new \plugins\highlights\HighlightsController();
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