<?php

// Registrar o binding do controller ProfileBannerController
        $this->app->bind('plugins\banner\ProfileBannerController', function($app) {
            return new plugins\banner\ProfileBannerController();
        });
        
        if (class_exists(\plugins\UserProfileBanner\Providers\UserProfileBannerServiceProvider::class)) {
            $this->app->register(\plugins\UserProfileBanner\Providers\UserProfileBannerServiceProvider::class);
            Log::info("UserProfileBannerServiceProvider (plugins minúsculo) registrado no método register() do AppServiceProvider.");
        } else {
            Log::warning("UserProfileBannerServiceProvider (plugins minúsculo) NÃO encontrado no método register() do AppServiceProvider.");
        }
    }

	
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        // ======== TUDO QUE ESTAVA NO PRIMEIRO boot() =========
        $pluginPath = base_path('plugins');
        if (is_dir($pluginPath)) {
            foreach (scandir($pluginPath) as $pluginDir) {
                if ($pluginDir === '.' || $pluginDir === '..') {
                    continue;
                }

                $pluginMain = $pluginPath . '/' . $pluginDir . '/plugin.json';
                if (! file_exists($pluginMain)) {
                    continue;
                }

                $modernRoutes = $pluginPath . '/' . $pluginDir . '/routes/web.php';
                if (file_exists($modernRoutes)) {
                    $this->loadRoutesFrom($modernRoutes);
                    continue;
                }

                $legacyRoutes = $pluginPath . '/' . $pluginDir . '/routes.php';
                if (file_exists($legacyRoutes)) {
                    $this->loadRoutesFrom($legacyRoutes);
                }
            }
        }
        // ======== FIM DO BLOCO ADICIONADO =========
        
        // Corrigido o caminho para o namespace de views
        View::addNamespace('plugins.banner', base_path('plugins/banner'));
        
        Log::info("AppServiceProvider boot method reached.");

        if (class_exists(\plugins\UserProfileBanner\Providers\UserProfileBannerServiceProvider::class)) {
            if (! $this->app->resolved(\plugins\UserProfileBanner\Providers\UserProfileBannerServiceProvider::class)) {
                 $this->app->register(\plugins\UserProfileBanner\Providers\UserProfileBannerServiceProvider::class);
                 Log::info("UserProfileBannerServiceProvider (plugins minúsculo) registrado explicitamente no método boot() do AppServiceProvider.");
            } else {
                 Log::info("UserProfileBannerServiceProvider (plugins minúsculo) já estava registrado/resolvido antes do registro explícito no boot().");
            }
        } else {
            Log::error("UserProfileBannerServiceProvider (plugins minúsculo) CLASS DOES NOT EXIST no momento do boot do AppServiceProvider.");
        }

        Paginator::useBootstrap();
        Validator::extend("isunique", function ($attribute, $value, $parameters, $validator) {
            $value = strtolower($value);
            $query = DB::table($parameters[0])->whereRaw("LOWER({$attribute}) = ?", [$value]);