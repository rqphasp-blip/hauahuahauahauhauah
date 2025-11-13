<?php

namespace App\Providers\plugins\gallery\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;

class GalleryServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register()
    {
        $this->app->bind('App\\Providers\\plugins\\gallery\\GalleryController', function ($app) {
            return new \App\Providers\plugins\gallery\GalleryController();
        });
    }

    /**
     * Bootstrap services.
     */
    public function boot()
    {
        $this->loadRoutesFrom(__DIR__ . '/../routes.php');
        View::addNamespace('gallery', __DIR__ . '/../views');

        $this->publishes([
            __DIR__ . '/../views' => resource_path('views/vendor/gallery'),
            __DIR__ . '/../create_user_gallery_photos_table.php' => database_path('migrations/' . date('Y_m_d_His') . '_create_user_gallery_photos_table.php'),
        ], 'gallery');
    }
}