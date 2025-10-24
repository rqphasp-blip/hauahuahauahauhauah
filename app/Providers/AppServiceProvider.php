<?php

namespace App\Providers;

use Illuminate\Support\Facades\Validator;
use Illuminate\Support\ServiceProvider;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Log;
use App\Services\PluginManagerService;
use Illuminate\Support\Facades\Schema;
use plugins\banner\ProfileBannerController;

// E depois use:


class AppServiceProvider extends ServiceProvider
{
    public function register()
    {
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
                if ($pluginDir === '.' || $pluginDir === '..') continue;
                $pluginMain = $pluginPath . '/' . $pluginDir . '/plugin.json';
                if (file_exists($pluginMain)) {
                    $routes = $pluginPath . '/' . $pluginDir . '/routes.php';
                    if (file_exists($routes)) {
                        $this->loadRoutesFrom($routes);
                    }
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

            if (isset($parameters[1])) {
                $query->where($parameters[1], "!=", $parameters[2]);
            }

            return $query->count() === 0;
        });
        Validator::extend("exturl", function ($attribute, $value, $parameters, $validator) {
            $allowed_schemes = ["http", "https", "mailto", "tel"];
            return in_array(parse_url($value, PHP_URL_SCHEME), $allowed_schemes, true);
        });
        View::addNamespace("blocks", base_path("blocks"));

        // Lógica de carregamento dinâmico de outros plugins
        try {
            // Instanciando o PluginManagerService que estava faltando
            $pluginManager = app(PluginManagerService::class);
            
            if (Schema::hasTable("plugins")) {
                $activePluginProviders = $pluginManager->getActivePluginProviders();
                Log::info("AppServiceProvider: Provedores de plugins ativos recebidos do PluginManagerService: " . print_r($activePluginProviders, true));

                foreach ($activePluginProviders as $providerClass) {
                    if ($providerClass === \plugins\UserProfileBanner\Providers\UserProfileBannerServiceProvider::class) {
                        Log::info("UserProfileBannerServiceProvider (plugins minúsculo) já tratado/registrado, pulando na lista de plugins ativos.");
                        continue;
                    }

                    Log::info("AppServiceProvider: Verificando provider dinâmico: {$providerClass}");

                    if (class_exists($providerClass)) {
                        if (! $this->app->resolved($providerClass)){
                            $this->app->register($providerClass);
                            Log::info("Plugin Service Provider dinâmico registrado: {$providerClass}");
                        }
                    } else {
                        Log::warning("Classe do Service Provider do plugin dinâmico NÃO encontrada: {$providerClass}");
                    }
                }
            } else {
                Log::warning("A tabela \"plugins\" não existe. Nenhum plugin dinâmico será carregado. Execute as migrações.");
            }
        } catch (\Exception $e) {
            Log::error("Erro ao carregar providers de plugins dinâmicos no AppServiceProvider@boot: " . $e->getMessage());
        }
		
    }
}
