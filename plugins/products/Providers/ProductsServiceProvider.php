<?php

namespace App\Providers\plugins\products\Providers;

use Illuminate\Support\ServiceProvider;

class ProductsServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind('products.controller', function () {
            return $this->app->make(\App\Providers\plugins\products\ProductsController::class);
        });
    }

    public function boot(): void
    {
        $this->loadRoutesFrom(base_path('plugins/products/routes.php'));
        $this->loadViewsFrom(base_path('plugins/products/views'), 'products');

        $this->publishes([
            base_path('plugins/products/views') => resource_path('views/products'),
        ], 'products-views');

        $this->publishes([
            base_path('plugins/products/create_user_products_tables.php') => database_path('migrations/' . date('Y_m_d_His') . '_create_user_products_tables.php'),
            base_path('plugins/products/add_image_path_to_user_products_table.php') => database_path('migrations/' . date('Y_m_d_His', time() + 1) . '_add_image_path_to_user_products_table.php'),
            base_path('plugins/products/add_catalog_enabled_to_user_product_settings_table.php') => database_path('migrations/' . date('Y_m_d_His', time() + 2) . '_add_catalog_enabled_to_user_product_settings_table.php'),
        ], 'products-migrations');
    }
}