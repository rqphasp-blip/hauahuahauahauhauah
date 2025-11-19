<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('user_product_settings', function (Blueprint $table) {
            if (! Schema::hasColumn('user_product_settings', 'catalog_enabled')) {
                $table->boolean('catalog_enabled')->default(false)->after('whatsapp_number');
            }
        });
    }

    public function down(): void
    {
        Schema::table('user_product_settings', function (Blueprint $table) {
            if (Schema::hasColumn('user_product_settings', 'catalog_enabled')) {
                $table->dropColumn('catalog_enabled');
            }
        });
    }
};
