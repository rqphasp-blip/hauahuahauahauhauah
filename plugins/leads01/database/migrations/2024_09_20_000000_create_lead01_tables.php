<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('leads01_campaigns', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->string('name', 150);
            $table->string('slug', 180)->unique();
            $table->string('description', 500)->nullable();
            $table->text('thank_you_message')->nullable();
            $table->string('status', 20)->default('active');
            $table->timestamps();

            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->onDelete('cascade');

            $table->index(['user_id', 'status']);
        });

        Schema::create('leads01_fields', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('campaign_id');
            $table->string('label', 150);
            $table->string('field_name', 150);
            $table->string('field_type', 30)->default('text');
            $table->boolean('required')->default(false);
            $table->string('placeholder', 255)->nullable();
            $table->json('options')->nullable();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();

            $table->foreign('campaign_id')
                ->references('id')
                ->on('leads01_campaigns')
                ->onDelete('cascade');

            $table->unique(['campaign_id', 'field_name']);
            $table->index(['campaign_id', 'sort_order']);
        });

        Schema::create('leads01_entries', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('campaign_id');
            $table->unsignedBigInteger('user_id');
            $table->json('data');
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamps();

            $table->foreign('campaign_id')
                ->references('id')
                ->on('leads01_campaigns')
                ->onDelete('cascade');

            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->onDelete('cascade');

            $table->index(['campaign_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('leads01_entries');
        Schema::dropIfExists('leads01_fields');
        Schema::dropIfExists('leads01_campaigns');
    }
};