<?php

namespace plugins\LeadCapture\Providers;

use Illuminate\Support\ServiceProvider;

if (class_exists(__NAMESPACE__ . '\\LeadCaptureServiceProvider', false)) {
    return;
}

class LeadCaptureServiceProvider extends ServiceProvider
{
    /**
     * Register any application services provided by the plugin.
     */
    public function register(): void
    {
        // No bindings are required for this plugin at the moment.
    }

    /**
     * Bootstrap the plugin services.
     */
    public function boot(): void
    {
        $pluginPath = __DIR__ . '/../';

        $this->loadRoutesFrom($pluginPath . 'routes/web.php');
        $this->loadViewsFrom($pluginPath . 'resources/views', 'LeadCapture');
        $this->loadMigrationsFrom($pluginPath . 'database/migrations');
