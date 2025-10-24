<?php

namespace plugins\UserDashboardImage\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\View;

class UserDashboardImageServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        $pluginName = 'UserDashboardImage';
        $pluginPath = __DIR__ . '/../../'; // Path to the plugin's root directory

        // Load routes
        $this->loadRoutesFrom($pluginPath . 'routes/web.php');

        // Load views
        $this->loadViewsFrom($pluginPath . 'resources/views', $pluginName);

        // You can also publish assets, migrations, etc. here if needed in the future
        // For example, to publish views (optional, if you want users to be able to override them):
        // $this->publishes([
        //     $pluginPath . 'resources/views' => resource_path('views/vendor/' . strtolower($pluginName)),
        // ], 'views');
    }
}

