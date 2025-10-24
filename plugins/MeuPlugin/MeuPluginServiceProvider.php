<?php

namespace Plugins\MeuPlugin;

use Illuminate\Support\ServiceProvider;

class MeuPluginServiceProvider extends ServiceProvider
{
    public function boot()
    {
        include __DIR__.'/routes.php';
        // Publique assets, views, etc, aqui.
    }
}