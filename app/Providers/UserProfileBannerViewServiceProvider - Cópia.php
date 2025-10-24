<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\View;

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
            return "<?php echo view('userprofilebanner_inline')->render(); ?>";
        });
        
        // Registra uma view inline com o conteúdo do formulário
        View::composer('userprofilebanner_inline', function ($view) {
            $viewPath = base_path('/plugins/UserProfileBanner/resources/views/upload_form_direct.blade.php');
            if (file_exists($viewPath)) {
                $content = file_get_contents($viewPath);
                $view->with('content', $content);
            } else {
                $view->with('content', '<!-- Arquivo de formulário de banner não encontrado -->');
            }
        });
    }
}
