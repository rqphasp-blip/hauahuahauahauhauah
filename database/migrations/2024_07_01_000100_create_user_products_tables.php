<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_product_categories', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->string('name');
            $table->string('description')->nullable();
            $table->timestamps();
            $table->index('user_id');
        });

        Schema::create('user_products', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('category_id');
            $table->string('name');
            $table->text('description');
            $table->decimal('price', 10, 2);
            $table->decimal('weight', 10, 3)->default(0);
            $table->string('image_path')->nullable();
            $table->timestamps();
            $table->index(['user_id', 'category_id']);
        });

        Schema::create('user_product_addons', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('product_id');
            $table->string('name');
            $table->decimal('price', 10, 2)->default(0);
            $table->timestamps();
            $table->index('product_id');
        });

        Schema::create('user_product_settings', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->unique();
            $table->string('whatsapp_number')->nullable();
            $table->boolean('catalog_enabled')->default(false);
            $table->timestamps();
        });

        Schema::create('user_product_orders', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->string('phone', 20);
            $table->string('customer_name');
            $table->string('address');
            $table->text('note')->nullable();
            $table->longText('cart_snapshot');
            $table->timestamps();
            $table->index(['user_id', 'phone']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_product_orders');
        Schema::dropIfExists('user_product_settings');
        Schema::dropIfExists('user_product_addons');
        Schema::dropIfExists('user_products');
        Schema::dropIfExists('user_product_categories');
    }
};