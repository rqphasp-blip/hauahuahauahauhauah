<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('lead_capture_campaigns', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('description', 500)->nullable();
            $table->text('thank_you_message')->nullable();
            $table->timestamps();

            $table->index('user_id');
        });

        Schema::create('lead_capture_fields', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('campaign_id');
            $table->string('label');
            $table->string('type', 30)->default('text');
            $table->boolean('required')->default(false);
            $table->unsignedInteger('order')->default(0);
            $table->timestamps();

            $table->foreign('campaign_id')
                ->references('id')
                ->on('lead_capture_campaigns')
                ->onDelete('cascade');
        });

        Schema::create('lead_capture_entries', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('campaign_id');
            $table->json('data');
            $table->timestamps();

            $table->foreign('campaign_id')
                ->references('id')
                ->on('lead_capture_campaigns')
                ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lead_capture_entries');
        Schema::dropIfExists('lead_capture_fields');
        Schema::dropIfExists('lead_capture_campaigns');
    }
};