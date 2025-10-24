<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Blade;

class UserProfileBannerViewServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Registra a diretiva Blade personalizada para incluir o formulário de banner
        Blade::directive('userprofilebanner', function () {
            $viewPath = base_path('plugins/userprofilebanner/resources/views/upload_form_direct.blade.php');
            if (file_exists($viewPath)) {
                return "<?php echo file_get_contents('{$viewPath}'); ?>";
            }
            return "<!-- Arquivo de formulário de banner não encontrado -->";
        });
    }
}
