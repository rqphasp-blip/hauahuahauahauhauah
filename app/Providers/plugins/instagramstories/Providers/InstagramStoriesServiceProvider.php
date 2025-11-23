<?php

namespace App\Providers\plugins\instagramstories\Providers;

use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class InstagramStoriesServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind('App\\Providers\\plugins\\instagramstories\\InstagramStoriesImportController', function ($app) {
            return new \App\Providers\plugins\instagramstories\InstagramStoriesImportController();
        });
    }

    public function boot(): void
    {
        // As rotas são carregadas dinamicamente pelo AppServiceProvider ao detectar plugin.json,
        // então aqui focamos apenas em namespaces de views e publicação de assets.
        $pluginPath = base_path('plugins/instagramstories');

        if (is_dir($pluginPath . '/views')) {
            View::addNamespace('instagramstories', $pluginPath . '/views');

            $this->publishes([
                $pluginPath . '/views' => resource_path('views/vendor/instagramstories'),
            ], 'instagramstories-views');
        }
    }
}