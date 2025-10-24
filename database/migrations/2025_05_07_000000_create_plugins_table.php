<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('plugins', function (Blueprint $table) {
            $table->id();
            $table->string('identifier')->unique();
            $table->string('name');
            $table->string('version');
            $table->text('description')->nullable();
            $table->string('author')->nullable();
            $table->string('provider_class');
            $table->boolean('is_active')->default(false);
            $table->string('path');
            $table->json('permissions_requested')->nullable();
            $table->json('permissions_granted')->nullable(); // Para uso futuro
            $table->timestamp('installed_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('plugins');
    }
};

